<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    // ── Helper: create a category ─────────────────────────────────────────────
    private function makeCategory(array $attrs = []): Category
    {
        return Category::create(array_merge([
            'item_name'   => 'Smartphone',
            'cate_name'   => 'Phones ' . uniqid(),
            'description' => 'A category',
            'status'      => 'active',
        ], $attrs));
    }

    // ── Helper: create a product ──────────────────────────────────────────────
    private function makeProduct(Category $category, array $attrs = []): Product
    {
        return Product::create(array_merge([
            'category_id'    => $category->id,
            'product_name'   => 'iPhone ' . uniqid(),
            'quantity'       => 10,
            'purchase_price' => 800,
            'sale_price'     => 999,
            'currency'       => 'USD',
            'status'         => 'active',
        ], $attrs));
    }

    // =========================================================================
    // Products – index returns paginated response
    // =========================================================================

    public function test_products_index_returns_pagination_metadata(): void
    {
        $category = $this->makeCategory();
        for ($i = 0; $i < 5; $i++) {
            $this->makeProduct($category);
        }

        $response = $this->getJson('/api/products?per_page=3&page=1');

        $response->assertOk()
                 ->assertJsonStructure([
                     'message',
                     'list',
                     'pagination' => [
                         'current_page',
                         'last_page',
                         'per_page',
                         'total',
                         'from',
                         'to',
                     ],
                 ]);

        $response->assertJsonPath('pagination.per_page', 3)
                 ->assertJsonPath('pagination.current_page', 1)
                 ->assertJsonPath('pagination.total', 5);
    }

    public function test_products_index_returns_correct_page_count(): void
    {
        $category = $this->makeCategory();
        for ($i = 0; $i < 10; $i++) {
            $this->makeProduct($category);
        }

        $response = $this->getJson('/api/products?per_page=4&page=1');

        $response->assertOk();
        $this->assertCount(4, $response->json('list'));
        $this->assertEquals(3, $response->json('pagination.last_page'));
    }

    public function test_products_index_second_page(): void
    {
        $category = $this->makeCategory();
        for ($i = 0; $i < 7; $i++) {
            $this->makeProduct($category);
        }

        $response = $this->getJson('/api/products?per_page=5&page=2');

        $response->assertOk();
        $this->assertCount(2, $response->json('list'));
        $this->assertEquals(2, $response->json('pagination.current_page'));
    }

    // =========================================================================
    // Products – search by product_name
    // =========================================================================

    public function test_products_search_by_product_name(): void
    {
        $category = $this->makeCategory();
        $this->makeProduct($category, ['product_name' => 'iPhone 15 Pro']);
        $this->makeProduct($category, ['product_name' => 'Samsung Galaxy S24']);
        $this->makeProduct($category, ['product_name' => 'Google Pixel 8']);

        $response = $this->getJson('/api/products?search=iPhone');

        $response->assertOk();
        $this->assertCount(1, $response->json('list'));
        $this->assertEquals('iPhone 15 Pro', $response->json('list.0.product_name'));
    }

    public function test_products_search_is_case_insensitive(): void
    {
        $category = $this->makeCategory();
        $this->makeProduct($category, ['product_name' => 'iPhone 15 Pro']);

        $response = $this->getJson('/api/products?search=iphone');

        $response->assertOk();
        $this->assertCount(1, $response->json('list'));
    }

    public function test_products_search_returns_empty_for_no_match(): void
    {
        $category = $this->makeCategory();
        $this->makeProduct($category, ['product_name' => 'iPhone 15 Pro']);

        $response = $this->getJson('/api/products?search=Blackberry');

        $response->assertOk();
        $this->assertCount(0, $response->json('list'));
        $this->assertEquals(0, $response->json('pagination.total'));
    }

    // =========================================================================
    // Products – search by category name
    // =========================================================================

    public function test_products_search_by_category_name(): void
    {
        $appleCategory  = $this->makeCategory(['cate_name' => 'Apple Devices']);
        $samsungCategory = $this->makeCategory(['cate_name' => 'Samsung Devices']);

        $this->makeProduct($appleCategory,  ['product_name' => 'iPhone 15 Pro']);
        $this->makeProduct($samsungCategory, ['product_name' => 'Samsung Galaxy S24']);

        $response = $this->getJson('/api/products?search=Apple');

        $response->assertOk();
        $this->assertCount(1, $response->json('list'));
        $this->assertEquals('iPhone 15 Pro', $response->json('list.0.product_name'));
    }

    // =========================================================================
    // Categories – index returns paginated response
    // =========================================================================

    public function test_categories_index_returns_pagination_metadata(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->makeCategory(['cate_name' => 'Category ' . $i]);
        }

        $response = $this->getJson('/api/categories?per_page=3&page=1');

        $response->assertOk()
                 ->assertJsonStructure([
                     'message',
                     'list',
                     'pagination' => [
                         'current_page',
                         'last_page',
                         'per_page',
                         'total',
                         'from',
                         'to',
                     ],
                 ]);

        $response->assertJsonPath('pagination.per_page', 3)
                 ->assertJsonPath('pagination.total', 5);
    }

    public function test_categories_index_returns_correct_page_count(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->makeCategory(['cate_name' => 'Category ' . $i]);
        }

        $response = $this->getJson('/api/categories?per_page=4&page=1');

        $response->assertOk();
        $this->assertCount(4, $response->json('list'));
        $this->assertEquals(3, $response->json('pagination.last_page'));
    }

    // =========================================================================
    // Categories – search
    // =========================================================================

    public function test_categories_search_by_cate_name(): void
    {
        $this->makeCategory(['cate_name' => 'Apple Products', 'item_name' => 'Electronics']);
        $this->makeCategory(['cate_name' => 'Samsung Products', 'item_name' => 'Electronics']);
        $this->makeCategory(['cate_name' => 'Accessories', 'item_name' => 'Gadgets']);

        $response = $this->getJson('/api/categories?search=Apple');

        $response->assertOk();
        $this->assertCount(1, $response->json('list'));
        $this->assertEquals('Apple Products', $response->json('list.0.cate_name'));
    }

    public function test_categories_search_by_item_name(): void
    {
        $this->makeCategory(['cate_name' => 'Apple Products', 'item_name' => 'Smartphones']);
        $this->makeCategory(['cate_name' => 'Accessories', 'item_name' => 'Gadgets']);

        $response = $this->getJson('/api/categories?search=Gadgets');

        $response->assertOk();
        $this->assertCount(1, $response->json('list'));
        $this->assertEquals('Accessories', $response->json('list.0.cate_name'));
    }

    public function test_categories_search_by_description(): void
    {
        $this->makeCategory([
            'cate_name'   => 'Apple Products',
            'description' => 'High-end Apple devices',
        ]);
        $this->makeCategory([
            'cate_name'   => 'Budget Phones',
            'description' => 'Affordable smartphones',
        ]);

        $response = $this->getJson('/api/categories?search=Affordable');

        $response->assertOk();
        $this->assertCount(1, $response->json('list'));
        $this->assertEquals('Budget Phones', $response->json('list.0.cate_name'));
    }

    public function test_categories_search_returns_empty_for_no_match(): void
    {
        $this->makeCategory(['cate_name' => 'Apple Products']);

        $response = $this->getJson('/api/categories?search=Nokia');

        $response->assertOk();
        $this->assertCount(0, $response->json('list'));
        $this->assertEquals(0, $response->json('pagination.total'));
    }

    // =========================================================================
    // Web routes
    // =========================================================================

    public function test_products_page_returns_200(): void
    {
        $this->get('/products')->assertStatus(200);
    }

    public function test_categories_page_returns_200(): void
    {
        $this->get('/categories')->assertStatus(200);
    }
}
