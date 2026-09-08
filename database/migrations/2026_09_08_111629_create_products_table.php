<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 500);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('subcategory_ids')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('buying_price', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->string('discount_type', 20)->default('percent');
            $table->decimal('discount', 12, 2)->default(0);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('gallery_media_ids')->nullable();
            $table->string('video_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
