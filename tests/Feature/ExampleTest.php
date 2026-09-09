<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->actingAs(User::factory()->create(['role'=>'superadmin'])); }
    /**
     * A basic test example.
     */
    public function test_the_homepage_renders_the_react_storefront(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('storefront-root');
    }

    public function test_the_admin_dashboard_is_available(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_the_pos_screen_renders_without_a_template_error(): void
    {
        $this->get(route('admin.pos.index'))->assertOk()->assertSee('Point of Sale');
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

    public function test_a_category_can_be_updated(): void
    {
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'display_order' => 1, 'is_active' => true]);

        $response = $this->put(route('admin.categories.update', $category), [
            'name' => 'Consumer electronics',
            'display_order' => 3,
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Consumer electronics', 'slug' => 'consumer-electronics', 'is_active' => 0]);
    }

    public function test_a_category_can_be_deleted_without_deleting_its_subcategory(): void
    {
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'display_order' => 1, 'is_active' => true]);
        $subcategory = Subcategory::create(['name' => 'Cables', 'slug' => 'cables']);
        $subcategory->categories()->attach($category);

        $response = $this->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('subcategories', ['id' => $subcategory->id]);
        $this->assertDatabaseMissing('category_subcategory', ['category_id' => $category->id]);
    }
}
