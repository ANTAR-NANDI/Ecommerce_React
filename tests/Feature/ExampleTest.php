<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_homepage_redirects_to_the_admin_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_the_admin_dashboard_is_available(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_a_category_can_be_created(): void
    {
        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Electronics',
            'display_order' => 2,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Electronics', 'slug' => 'electronics', 'display_order' => 2]);
    }

    public function test_a_subcategory_can_belong_to_multiple_categories(): void
    {
        $firstCategory = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'display_order' => 1, 'is_active' => true]);
        $secondCategory = Category::create(['name' => 'Offers', 'slug' => 'offers', 'display_order' => 2, 'is_active' => true]);

        $response = $this->post(route('admin.subcategories.store'), [
            'name' => 'Mobile accessories',
            'categories' => [$firstCategory->id, $secondCategory->id],
            'short_description' => 'Cases, cables and chargers.',
        ]);

        $response->assertRedirect(route('admin.subcategories.index'));
        $subcategory = Subcategory::where('slug', 'mobile-accessories')->firstOrFail();
        $this->assertCount(2, $subcategory->categories);
    }
}
