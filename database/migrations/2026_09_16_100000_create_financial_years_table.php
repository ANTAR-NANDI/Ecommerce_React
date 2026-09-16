<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('financial_years', function (Blueprint $table) { $table->id(); $table->string('name', 30)->unique(); $table->date('start_date'); $table->date('end_date'); $table->timestamp('closed_at')->nullable(); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('financial_years'); } };
