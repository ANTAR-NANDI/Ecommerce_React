<?php

namespace Tests\Feature;

use App\Models\AccountCoa;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\PosOrder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_blank_voucher_narration_is_generated_from_the_debit_and_credit_accounts(): void
    {
        $cash = AccountCoa::where('code', '10011')->firstOrFail();
        $expense = AccountCoa::create(['code' => 'TEST-NARRATION', 'head_name' => 'Test Expense', 'account_type' => 'expense']);

        $voucher = app(AccountService::class)->postVoucher([
            'voucher_type' => 'debit',
            'transaction_date' => '2026-09-21',
            'debit_account_id' => $expense->id,
            'credit_account_id' => $cash->id,
            'amount' => 1000,
            'ledger_comment' => '   ',
        ]);

        $this->assertDatabaseHas('account_transactions', [
            'voucher_no' => $voucher,
            'ledger_comment' => 'Debit entry: Debit Test Expense; Credit Cash in Hand',
        ]);
    }

    public function test_customer_receive_lists_a_pos_due_invoice_and_records_receipt_against_it(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $customer = Customer::create(['first_name' => 'Rafi', 'last_name' => 'Ahmed', 'phone' => '01800000001', 'password' => 'password']);
        $warehouse = Warehouse::create(['name' => 'Dhaka', 'code' => 'DHA']);
        $order = PosOrder::create([
            'order_number' => 'POS-DUE-001', 'warehouse_id' => $warehouse->id, 'customer_id' => $customer->id,
            'customer_name' => $customer->full_name, 'payment_method' => 'Due', 'status' => 'completed',
            'subtotal' => 1000, 'discount_type' => 'fixed', 'discount_value' => 0, 'discount_amount' => 0, 'total' => 1000,
        ]);
        $customerHead = app(AccountService::class)->ensureCustomerHead($customer);
        $cash = AccountCoa::where('code', '10011')->firstOrFail();
        $method = PaymentMethod::firstOrCreate(['name' => 'Cash in Hand'], ['account_coa_id' => $cash->id, 'is_fixed' => true]);

        $this->get(route('admin.accounts.settlement', 'customer-receive'))
            ->assertOk()->assertSee('POS-DUE-001')->assertSee('Due BDT 1,000.00');

        $this->post(route('admin.accounts.settlement.store', 'customer-receive'), [
            'entity_id' => $customer->id, 'reference' => 'pos:'.$order->id, 'payment_method_id' => $method->id,
            'amount' => 400, 'transaction_date' => '2026-09-21', 'remark' => 'Partial receipt',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('account_transactions', [
            'pos_order_id' => $order->id, 'customer_id' => $customer->id,
            'account_coa_id' => $customerHead->id, 'entry_type' => 'credit', 'amount' => 400,
        ]);
        $this->get(route('admin.accounts.settlement', 'customer-receive'))
            ->assertOk()->assertSee('POS-DUE-001')->assertSee('Due BDT 600.00');
    }

    public function test_customer_receive_rejects_more_than_the_selected_invoice_due(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $customer = Customer::create(['first_name' => 'Hasan', 'phone' => '01800000002', 'password' => 'password']);
        $warehouse = Warehouse::create(['name' => 'Chattogram', 'code' => 'CTG']);
        $order = PosOrder::create(['order_number' => 'POS-DUE-002', 'warehouse_id' => $warehouse->id, 'customer_id' => $customer->id, 'payment_method' => 'Due', 'status' => 'completed', 'subtotal' => 500, 'discount_type' => 'fixed', 'discount_value' => 0, 'discount_amount' => 0, 'total' => 500]);
        $cash = AccountCoa::where('code', '10011')->firstOrFail();
        $method = PaymentMethod::firstOrCreate(['name' => 'Cash in Hand'], ['account_coa_id' => $cash->id, 'is_fixed' => true]);

        $this->post(route('admin.accounts.settlement.store', 'customer-receive'), [
            'entity_id' => $customer->id, 'reference' => 'pos:'.$order->id, 'payment_method_id' => $method->id,
            'amount' => 501, 'transaction_date' => '2026-09-21',
        ])->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('account_transactions', 0);
    }
}
