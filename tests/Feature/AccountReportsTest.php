<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccountReportController;
use App\Models\AccountCoa;
use App\Models\AccountTransaction;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
    }

    public function test_every_accounts_report_renders(): void
    {
        $reports = array_keys(AccountReportController::FINANCIAL_REPORTS + AccountReportController::OPERATIONAL_REPORTS);

        foreach ($reports as $report) {
            $this->get(route('admin.accounts.reports', $report))
                ->assertOk()
                ->assertSee(AccountReportController::FINANCIAL_REPORTS[$report] ?? AccountReportController::OPERATIONAL_REPORTS[$report]);
        }
    }

    public function test_balance_sheet_includes_current_profit_in_equity_and_balances(): void
    {
        $cash = AccountCoa::where('code', '10011')->firstOrFail();
        $payable = AccountCoa::create(['code' => 'TEST-LIAB', 'head_name' => 'Test Payable', 'account_type' => 'liability']);
        $capital = AccountCoa::create(['code' => 'TEST-EQUITY', 'head_name' => 'Test Capital', 'account_type' => 'equity']);
        $income = AccountCoa::create(['code' => 'TEST-INCOME', 'head_name' => 'Test Income', 'account_type' => 'income']);
        $expense = AccountCoa::create(['code' => 'TEST-EXPENSE', 'head_name' => 'Test Expense', 'account_type' => 'expense']);
        foreach ([[$cash, 'debit', 1000], [$payable, 'credit', 200], [$capital, 'credit', 500], [$income, 'credit', 400], [$expense, 'debit', 100]] as [$account, $entryType, $amount]) {
            AccountTransaction::create(['voucher_no' => 'TEST-BALANCE', 'voucher_type' => 'journal', 'transaction_date' => '2026-09-21', 'account_coa_id' => $account->id, 'entry_type' => $entryType, 'amount' => $amount]);
        }

        $this->get(route('admin.accounts.reports', 'balance-sheet'))
            ->assertOk()
            ->assertSee('Current Year Profit')
            ->assertViewHas('result', function (array $result) {
                return $result['totals']['Assets'] === 1000.0
                    && $result['totals']['Liabilities'] === 200.0
                    && $result['totals']['Equity'] === 800.0
                    && $result['totals']['Current Year Profit / (Loss)'] === 300.0
                    && $result['totals']['Assets'] === $result['totals']['Liabilities'] + $result['totals']['Equity'];
            });
    }

    public function test_balance_sheet_reduces_equity_for_current_loss(): void
    {
        $cash = AccountCoa::where('code', '10011')->firstOrFail();
        $capital = AccountCoa::create(['code' => 'LOSS-EQUITY', 'head_name' => 'Loss Capital', 'account_type' => 'equity']);
        $expense = AccountCoa::create(['code' => 'LOSS-EXPENSE', 'head_name' => 'Loss Expense', 'account_type' => 'expense']);
        foreach ([[$cash, 'debit', 1000], [$capital, 'credit', 1000], [$expense, 'debit', 100], [$cash, 'credit', 100]] as [$account, $entryType, $amount]) {
            AccountTransaction::create(['voucher_no' => 'TEST-LOSS', 'voucher_type' => 'journal', 'transaction_date' => '2026-09-21', 'account_coa_id' => $account->id, 'entry_type' => $entryType, 'amount' => $amount]);
        }

        $this->get(route('admin.accounts.reports', 'balance-sheet'))
            ->assertOk()->assertSee('Current Year Loss')
            ->assertViewHas('result', fn (array $result) => $result['totals']['Assets'] === 900.0
                && $result['totals']['Equity'] === 900.0
                && $result['totals']['Current Year Profit / (Loss)'] === -100.0);
    }

    public function test_all_sales_reports_include_completed_pos_orders(): void
    {
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $product = Product::create(['name' => 'POS Phone', 'slug' => 'pos-phone', 'sku' => 'POS-PHONE', 'short_description' => 'POS report product', 'category_id' => $category->id, 'buying_price' => 60, 'selling_price' => 100]);
        $warehouse = Warehouse::create(['name' => 'Dhaka', 'code' => 'DHA']);
        $customer = Customer::create(['first_name' => 'POS', 'last_name' => 'Customer', 'phone' => '01800000001', 'password' => 'password']);
        $order = PosOrder::create(['order_number' => 'POS-REPORT-001', 'warehouse_id' => $warehouse->id, 'customer_id' => $customer->id, 'customer_name' => $customer->full_name, 'payment_method' => 'Due', 'status' => 'completed', 'subtotal' => 100, 'discount_type' => 'fixed', 'discount_value' => 10, 'discount_amount' => 10, 'total' => 90]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => 1, 'unit_price' => 100, 'line_total' => 100]);

        $this->get(route('admin.accounts.reports', 'sales'))->assertOk()->assertSee('POS-REPORT-001')->assertSee('POS')
            ->assertViewHas('result', fn (array $result) => $result['totals']['Orders'] === 1 && $result['totals']['Sales'] === 90.0 && $result['totals']['Discount'] === 10.0);
        $this->get(route('admin.accounts.reports', 'sales-product-wise'))->assertOk()->assertSee('POS Phone')->assertSee('$90.00');
        $this->get(route('admin.accounts.reports', 'sales-category-wise'))->assertOk()->assertSee('Electronics')->assertSee('$90.00');
        $this->get(route('admin.accounts.reports', 'due'))->assertOk()->assertSee('POS-REPORT-001')->assertSee('$90.00');
        $this->get(route('admin.accounts.reports', 'shipping-cost'))->assertOk()->assertSee('POS-REPORT-001')->assertSee('$0.00');
        $this->get(route('admin.accounts.reports', 'profit-sale-wise'))->assertOk()->assertSee('POS-REPORT-001')->assertSee('$30.00');
        $this->get(route('admin.accounts.reports', 'closing').'?to_date='.now()->toDateString())->assertOk()->assertSee('$90.00');
    }
}
