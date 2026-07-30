<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * A small fixed queue, so the application is never an empty screen at a
     * presentation. updateOrCreate keeps the seeder safe to re-run.
     */
    public function run(): void
    {
        $tickets = [
            [
                'reference' => 'TCK-0001',
                'subject' => 'app-crm returns HTTP 500 after the PHP upgrade',
                'description' => 'Every page of app-crm on VM3 answers with 500. The monitor flagged it right after the Ansible playbook ran.',
                'requester' => 'elena@nordwind.example',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'assignee' => 'Ana Pop',
            ],
            [
                'reference' => 'TCK-0002',
                'subject' => 'Stock report shows a wrong total value',
                'description' => 'The stock value in app-inventory does not match the sum of the rows when a filter is active.',
                'requester' => 'radu@cortex.example',
                'priority' => 'high',
                'status' => 'open',
                'assignee' => null,
            ],
            [
                'reference' => 'TCK-0003',
                'subject' => 'Please add a CSV export for the client list',
                'description' => 'It would help to export the CRM client list to CSV for the monthly report.',
                'requester' => 'ioana@blueharbor.example',
                'priority' => 'low',
                'status' => 'open',
                'assignee' => null,
            ],
            [
                'reference' => 'TCK-0004',
                'subject' => 'Jenkins pipeline fails at the structure validation stage',
                'description' => 'Build 7 of app-blog stopped because docker-compose.yml was missing from the repository.',
                'requester' => 'mihai@verdant.example',
                'priority' => 'normal',
                'status' => 'resolved',
                'assignee' => 'Radu Dinu',
            ],
            [
                'reference' => 'TCK-0005',
                'subject' => 'Grafana dashboard has no data for VM4',
                'description' => 'The node_exporter on VM4 was not scraped. The Prometheus target was down for two hours.',
                'requester' => 'andreea@helix.example',
                'priority' => 'high',
                'status' => 'closed',
                'assignee' => 'Ana Pop',
            ],
        ];

        foreach ($tickets as $ticket) {
            $resolved = in_array($ticket['status'], ['resolved', 'closed'], true);

            Ticket::updateOrCreate(
                ['reference' => $ticket['reference']],
                $ticket + ['resolved_at' => $resolved ? now() : null]
            );
        }
    }
}
