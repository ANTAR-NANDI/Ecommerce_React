<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('color_product', function (Blueprint $table) { $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->foreignId('color_id')->constrained()->cascadeOnDelete(); $table->timestamps(); $table->unique(['product_id', 'color_id']); });
        Schema::create('product_size', function (Blueprint $table) { $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->foreignId('size_id')->constrained()->cascadeOnDelete(); $table->timestamps(); $table->unique(['product_id', 'size_id']); });
    }
    public function down(): void { Schema::dropIfExists('product_size'); Schema::dropIfExists('color_product'); }
};
