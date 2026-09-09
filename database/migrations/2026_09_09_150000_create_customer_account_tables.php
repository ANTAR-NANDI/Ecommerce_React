<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->rememberToken();
        });

        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('order_number')->constrained()->nullOnDelete();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label', 30)->default('home');
            $table->string('name');
            $table->string('phone', 30);
            $table->string('address');
            $table->string('area')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('customer_wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['customer_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_wishlists');
        Schema::dropIfExists('customer_addresses');
        Schema::table('ecommerce_orders', fn (Blueprint $table) => $table->dropConstrainedForeignId('customer_id'));
        Schema::table('customers', fn (Blueprint $table) => $table->dropRememberToken());
    }
};
