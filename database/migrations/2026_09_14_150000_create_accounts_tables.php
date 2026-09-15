<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_coas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('account_coas')->nullOnDelete();
            $table->string('code', 60)->unique();
            $table->string('head_name', 180);
            $table->string('account_type', 30);
            $table->boolean('is_group')->default(false);
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['parent_id', 'code']);
        });

        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 50)->nullable()->index();
            $table->string('voucher_type', 30)->default('journal');
            $table->date('transaction_date');
            $table->foreignId('account_coa_id')->constrained('account_coas')->restrictOnDelete();
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 16, 2);
            $table->text('ledger_comment')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('sale_id')->nullable()->index();
            $table->foreignId('ecommerce_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pos_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['account_coa_id', 'transaction_date']);
        });

        $roots = [
            ['code' => '100', 'head_name' => 'Assets', 'account_type' => 'asset'],
            ['code' => '200', 'head_name' => 'Liabilities', 'account_type' => 'liability'],
            ['code' => '300', 'head_name' => 'Equity', 'account_type' => 'equity'],
            ['code' => '400', 'head_name' => 'Income', 'account_type' => 'income'],
            ['code' => '500', 'head_name' => 'Expenses', 'account_type' => 'expense'],
        ];
        foreach ($roots as $root) DB::table('account_coas')->updateOrInsert(['code' => $root['code']], $root + ['is_group' => true, 'created_at' => now(), 'updated_at' => now()]);

        $add = function (string $code, string $name, string $type, string $parentCode, bool $group = true): void {
            $parentId = DB::table('account_coas')->where('code', $parentCode)->value('id');
            DB::table('account_coas')->updateOrInsert(['code' => $code], ['parent_id' => $parentId, 'head_name' => $name, 'account_type' => $type, 'is_group' => $group, 'updated_at' => now(), 'created_at' => now()]);
        };
        $add('1001', 'Current Assets', 'asset', '100');
        $add('10011', 'Cash in Hand', 'asset', '1001', false);
        $add('10012', 'Bank Accounts', 'asset', '1001');
        $add('10013', 'Accounts Receivable', 'asset', '1001');
        $add('10014', 'Inventory', 'asset', '1001', false);
        $add('10015', 'Employee Advances', 'asset', '1001');
        $add('2001', 'Current Liabilities', 'liability', '200');
        $add('20011', 'Accounts Payable', 'liability', '2001');
        $add('4001', 'Sales Revenue', 'income', '400', false);
        $add('5001', 'Cost of Goods Sold', 'expense', '500', false);
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('account_coas');
    }
};
