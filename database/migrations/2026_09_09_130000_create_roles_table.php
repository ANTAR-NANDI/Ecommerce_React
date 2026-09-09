<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        $now = now();
        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'slug' => 'superadmin', 'permissions' => json_encode(['*']), 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Warehouse Admin', 'slug' => 'warehouse_admin', 'permissions' => json_encode(['dashboard', 'products', 'orders', 'pos']), 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Salesman', 'slug' => 'salesman', 'permissions' => json_encode(['dashboard', 'products', 'orders', 'pos']), 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
    public function down(): void { Schema::dropIfExists('roles'); }
};
