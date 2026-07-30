<?php

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The ticket queue and its create / edit / close / delete flow.
 *
 * Run by the "Test" stage of the Jenkins pipeline before every deployment.
 */
class TicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'subject' => 'Printer on the second floor is offline',
            'description' => 'It stopped responding after the network maintenance window.',
            'requester' => 'user@example.com',
            'priority' => 'normal',
            'status' => 'open',
            'assignee' => null,
        ], $overrides);
    }

    public function test_the_root_url_redirects_to_the_queue(): void
    {
        $this->get('/')->assertRedirect(route('tickets.index'));
    }

    public function test_the_queue_lists_the_tickets(): void
    {
        Ticket::factory()->create(['reference' => 'TCK-0042', 'subject' => 'Visible ticket']);

        $response = $this->get(route('tickets.index'));

        $response->assertOk();
        $response->assertSee('TCK-0042');
        $response->assertSee('Visible ticket');
    }

    public function test_the_queue_can_be_searched(): void
    {
        Ticket::factory()->create(['reference' => 'TCK-1111', 'subject' => 'Needle in here']);
        Ticket::factory()->create(['reference' => 'TCK-2222', 'subject' => 'Something else']);

        $response = $this->get(route('tickets.index', ['q' => 'Needle']));

        $response->assertOk();
        $response->assertSee('TCK-1111');
        $response->assertDontSee('TCK-2222');
    }

    public function test_the_queue_can_be_filtered_by_status_and_priority(): void
    {
        Ticket::factory()->open()->urgent()->create(['reference' => 'TCK-3001']);
        Ticket::factory()->closed()->create(['reference' => 'TCK-3002', 'priority' => 'low']);

        $byStatus = $this->get(route('tickets.index', ['status' => 'open']));
        $byStatus->assertOk();
        $byStatus->assertSee('TCK-3001');
        $byStatus->assertDontSee('TCK-3002');

        $byPriority = $this->get(route('tickets.index', ['priority' => 'urgent']));
        $byPriority->assertOk();
        $byPriority->assertSee('TCK-3001');
        $byPriority->assertDontSee('TCK-3002');
    }

    public function test_an_unknown_filter_value_is_ignored(): void
    {
        Ticket::factory()->create(['reference' => 'TCK-4001']);

        $response = $this->get(route('tickets.index', ['status' => 'exploded']));

        $response->assertOk();
        $response->assertSee('TCK-4001');
    }

    public function test_a_ticket_can_be_created_and_gets_a_reference(): void
    {
        $response = $this->post(route('tickets.store'), $this->validPayload());

        $response->assertRedirect(route('tickets.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('tickets', [
            'reference' => 'TCK-0001',
            'subject' => 'Printer on the second floor is offline',
        ]);
    }

    public function test_references_increment(): void
    {
        $this->post(route('tickets.store'), $this->validPayload());
        $this->post(route('tickets.store'), $this->validPayload(['subject' => 'Second one']));

        $this->assertDatabaseHas('tickets', ['reference' => 'TCK-0002', 'subject' => 'Second one']);
    }

    public function test_the_required_fields_are_validated(): void
    {
        $response = $this->post(route('tickets.store'), []);

        $response->assertSessionHasErrors(['subject', 'description', 'requester', 'priority', 'status']);
        $this->assertSame(0, Ticket::count());
    }

    public function test_an_invalid_requester_email_is_rejected(): void
    {
        $response = $this->post(route('tickets.store'), $this->validPayload(['requester' => 'not-an-email']));

        $response->assertSessionHasErrors('requester');
    }

    public function test_an_unknown_priority_is_rejected(): void
    {
        $response = $this->post(route('tickets.store'), $this->validPayload(['priority' => 'catastrophic']));

        $response->assertSessionHasErrors('priority');
    }

    public function test_a_ticket_page_can_be_opened(): void
    {
        $ticket = Ticket::factory()->create(['reference' => 'TCK-0500', 'subject' => 'Detail page']);

        $response = $this->get(route('tickets.show', $ticket));

        $response->assertOk();
        $response->assertSee('TCK-0500');
        $response->assertSee('Detail page');
    }

    public function test_a_ticket_can_be_updated(): void
    {
        $ticket = Ticket::factory()->open()->create();

        $response = $this->put(route('tickets.update', $ticket), $this->validPayload([
            'subject' => 'Renamed subject',
            'status' => 'in_progress',
            'assignee' => 'Ana Pop',
        ]));

        $response->assertRedirect(route('tickets.index'));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'subject' => 'Renamed subject',
            'status' => 'in_progress',
            'assignee' => 'Ana Pop',
        ]);
    }

    public function test_resolving_a_ticket_stamps_the_resolution_time(): void
    {
        $ticket = Ticket::factory()->open()->create();

        $this->put(route('tickets.update', $ticket), $this->validPayload(['status' => 'resolved']));

        $this->assertNotNull($ticket->fresh()->resolved_at);
    }

    public function test_reopening_a_ticket_clears_the_resolution_time(): void
    {
        $ticket = Ticket::factory()->closed()->create();

        $this->assertNotNull($ticket->resolved_at);

        $this->put(route('tickets.update', $ticket), $this->validPayload(['status' => 'open']));

        $this->assertNull($ticket->fresh()->resolved_at);
    }

    public function test_a_ticket_can_be_closed_in_one_click(): void
    {
        $ticket = Ticket::factory()->open()->create();

        $response = $this->patch(route('tickets.close', $ticket));

        $response->assertRedirect(route('tickets.index'));

        $ticket->refresh();

        $this->assertSame('closed', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_a_ticket_can_be_deleted(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->delete(route('tickets.destroy', $ticket));

        $response->assertRedirect(route('tickets.index'));
        $this->assertSame(0, Ticket::count());
    }

    public function test_an_unknown_ticket_returns_404(): void
    {
        $this->get('/tickets/999999')->assertNotFound();
    }
}
