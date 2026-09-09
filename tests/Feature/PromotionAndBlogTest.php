<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\ContactMessage;
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

    public function test_storefront_data_endpoint_is_publicly_available(): void
    {
        auth()->logout();

        $this->get(route('storefront.data'))
            ->assertOk()
            ->assertJsonStructure(['categories', 'products', 'flash_products', 'banners', 'blogs', 'contact']);
    }

    public function test_public_catalog_endpoint_provides_filters_and_paginated_products(): void
    {
        auth()->logout();

        $this->get(route('products.public.index'))
            ->assertOk()
            ->assertJsonStructure([
                'products' => ['data', 'current_page', 'last_page', 'total'],
                'filters' => ['categories', 'brands', 'colors', 'sizes'],
            ]);
    }

    public function test_public_brand_directory_data_is_available(): void
    {
        auth()->logout();

        $this->get(route('brands.public.data'))
            ->assertOk()
            ->assertJsonStructure(['*' => ['id', 'name', 'icon_url', 'products_count']]);
    }

    public function test_a_guest_can_send_a_contact_message(): void
    {
        auth()->logout();

        $this->postJson(route('contact.messages.store'), [
            'name' => 'Amina Rahman',
            'phone' => '+8801700000000',
            'email' => 'amina@example.com',
            'subject' => 'Delivery question',
            'message' => 'Could you please help with delivery timing?',
        ])->assertCreated()->assertJsonPath('message', 'Thank you. Your message has been sent successfully.');

        $this->assertDatabaseHas('contact_messages', ['name' => 'Amina Rahman', 'subject' => 'Delivery question']);
    }

    public function test_public_blog_listing_returns_posts_and_categories(): void
    {
        auth()->logout();

        $this->get(route('blogs.public.index'))
            ->assertOk()
            ->assertJsonStructure(['posts' => ['data', 'current_page', 'last_page'], 'categories']);
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
