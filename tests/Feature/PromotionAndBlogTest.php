<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionAndBlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
    }

    public function test_each_promotion_screen_is_available(): void
    {
        foreach (['flash-deals', 'banners', 'ads-campaigns', 'promo-codes'] as $type) {
            $this->get(route('admin.promotions.index', $type))->assertOk();
        }
    }

    public function test_a_promo_code_can_be_created(): void
    {
        $response = $this->post(route('admin.promotions.store', 'promo-codes'), [
            'title' => 'Welcome discount',
            'code' => 'welcome20',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'minimum_order_amount' => 50,
            'usage_limit' => 100,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.promotions.index', 'promo-codes'));
        $this->assertDatabaseHas('promotions', [
            'type' => 'promo_code',
            'code' => 'WELCOME20',
            'discount_value' => 20,
            'is_active' => true,
        ]);
    }

    public function test_blog_accepts_multiple_select2_tags(): void
    {
        $response = $this->post(route('admin.blogs.store'), [
            'title' => 'Autumn collection',
            'tags' => ['Fashion', 'New arrival', 'fashion'],
            'description' => 'A guide to the new autumn collection.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.blogs.index'));
        $blog = Blog::where('slug', 'autumn-collection')->firstOrFail();
        $this->assertSame(['Fashion', 'New arrival'], $blog->tags);
    }

    public function test_promotion_type_cannot_access_another_types_record(): void
    {
        $promotion = Promotion::create([
            'type' => 'promo_code',
            'title' => 'Private code',
            'code' => 'PRIVATE',
            'discount_type' => 'amount',
            'discount_value' => 5,
            'is_active' => true,
        ]);

        $this->get(route('admin.promotions.edit', ['flash-deals', $promotion]))->assertNotFound();
    }
}
