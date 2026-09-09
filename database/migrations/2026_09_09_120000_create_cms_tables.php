<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cms_menus', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url');
            $table->enum('location', ['header', 'footer_shop', 'footer_support', 'footer_company'])->default('header');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('open_in_new_tab')->default(false);
            $table->timestamps();
        });

        Schema::create('cms_footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('about_title')->default('VeloraCommerce');
            $table->text('about_text')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('whatsapp_url')->nullable();
            $table->string('copyright_text')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('cms_menus')->insert([
            ['label' => 'Home', 'url' => '/', 'location' => 'header', 'sort_order' => 10, 'is_active' => true, 'open_in_new_tab' => false, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Products', 'url' => '/products', 'location' => 'header', 'sort_order' => 20, 'is_active' => true, 'open_in_new_tab' => false, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Brands', 'url' => '/brands', 'location' => 'header', 'sort_order' => 30, 'is_active' => true, 'open_in_new_tab' => false, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Blogs', 'url' => '/blogs', 'location' => 'header', 'sort_order' => 40, 'is_active' => true, 'open_in_new_tab' => false, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Contact', 'url' => '/contact-us', 'location' => 'header', 'sort_order' => 50, 'is_active' => true, 'open_in_new_tab' => false, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'All Products', 'url' => '/products', 'location' => 'footer_shop', 'sort_order' => 10, 'is_active' => true, 'open_in_new_tab' => false, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Help Center', 'url' => '/contact-us', 'location' => 'footer_support', 'sort_order' => 10, 'is_active' => true, 'open_in_new_tab' => false, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Blog', 'url' => '/blogs', 'location' => 'footer_company', 'sort_order' => 10, 'is_active' => true, 'open_in_new_tab' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_footer_settings');
        Schema::dropIfExists('cms_menus');
        Schema::dropIfExists('cms_pages');
    }
};
