<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The stock list and the create / edit / delete flow.
 *
 * Run by the "Test" stage of the Jenkins pipeline before every deployment.
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'sku' => 'TST-0001',
            'name' => 'Test product',
            'quantity' => 25,
            'reorder_level' => 5,
            'unit_price' => 19.99,
            'location' => 'A-01',
        ], $overrides);
    }

    public function test_the_root_url_redirects_to_the_stock_list(): void
    {
        $this->get('/')->assertRedirect(route('products.index'));
    }

    public function test_the_stock_list_shows_the_products(): void
    {
        Product::factory()->create(['sku' => 'ABC-1234', 'name' => 'Visible product']);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('ABC-1234');
        $response->assertSee('Visible product');
    }

    public function test_the_stock_list_can_be_searched(): void
    {
        Product::factory()->create(['sku' => 'AAA-1111', 'name' => 'Needle']);
        Product::factory()->create(['sku' => 'BBB-2222', 'name' => 'Haystack']);

        $response = $this->get(route('products.index', ['q' => 'Needle']));

        $response->assertOk();
        $response->assertSee('AAA-1111');
        $response->assertDontSee('BBB-2222');
    }

    public function test_the_low_stock_filter_only_returns_products_to_reorder(): void
    {
        Product::factory()->lowStock()->create(['sku' => 'LOW-0001']);
        Product::factory()->create(['sku' => 'OKS-0001', 'quantity' => 500, 'reorder_level' => 10]);

        $response = $this->get(route('products.index', ['low' => 1]));

        $response->assertOk();
        $response->assertSee('LOW-0001');
        $response->assertDontSee('OKS-0001');
    }

    public function test_a_product_can_be_created(): void
    {
        $response = $this->post(route('products.store'), $this->validPayload());

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('products', ['sku' => 'TST-0001', 'quantity' => 25]);
    }

    public function test_the_sku_is_stored_uppercased(): void
    {
        $this->post(route('products.store'), $this->validPayload(['sku' => 'low-0009']));

        $this->assertDatabaseHas('products', ['sku' => 'LOW-0009']);
    }

    public function test_the_required_fields_are_validated(): void
    {
        $response = $this->post(route('products.store'), []);

        $response->assertSessionHasErrors(['sku', 'name', 'quantity', 'reorder_level', 'unit_price']);
        $this->assertSame(0, Product::count());
    }

    public function test_a_duplicate_sku_is_rejected(): void
    {
        Product::factory()->create(['sku' => 'DUP-0001']);

        $response = $this->post(route('products.store'), $this->validPayload(['sku' => 'DUP-0001']));

        $response->assertSessionHasErrors('sku');
        $this->assertSame(1, Product::count());
    }

    public function test_a_negative_quantity_is_rejected(): void
    {
        $response = $this->post(route('products.store'), $this->validPayload(['quantity' => -5]));

        $response->assertSessionHasErrors('quantity');
    }

    public function test_a_product_can_be_updated(): void
    {
        $product = Product::factory()->create(['sku' => 'UPD-0001', 'quantity' => 10]);

        $response = $this->put(
            route('products.update', $product),
            $this->validPayload(['sku' => 'UPD-0001', 'name' => 'Renamed', 'quantity' => 42])
        );

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Renamed', 'quantity' => 42]);
    }

    public function test_a_product_keeps_its_own_sku_when_updated(): void
    {
        $product = Product::factory()->create(['sku' => 'KEEP-001']);

        $response = $this->put(route('products.update', $product), $this->validPayload(['sku' => 'KEEP-001']));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_a_product_can_be_deleted(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertSame(0, Product::count());
    }

    public function test_the_edit_page_of_an_unknown_product_returns_404(): void
    {
        $this->get('/products/999999/edit')->assertNotFound();
    }
}
