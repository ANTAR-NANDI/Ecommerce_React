<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration {public function up():void{Schema::create('payment_methods',function(Blueprint $t){$t->id();$t->string('name')->unique();$t->foreignId('account_coa_id')->constrained()->restrictOnDelete();$t->boolean('is_fixed')->default(false);$t->timestamps();});}public function down():void{Schema::dropIfExists('payment_methods');}};
