<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AccountCoa, AccountTransaction, Customer, EcommerceOrder, Product, Purchase, PurchaseItem, Supplier, Warehouse, WarehouseProductStock};
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accounts) {}

    public function coa(): View
    {
        $this->accounts->syncProfileHeads();
        $nodes = AccountCoa::with('children.children.children.children')->whereNull('parent_id')->orderBy('code')->get();
        return view('admin.accounts.coa', ['nodes' => $nodes, 'groups' => AccountCoa::where('is_group', true)->orderBy('code')->get()]);
    }

    public function storeCoa(Request $request): RedirectResponse
    {
        $data = $request->validate(['parent_id' => ['required', 'exists:account_coas,id'], 'head_name' => ['required', 'string', 'max:180']]);
        $account = $this->accounts->createChild(AccountCoa::findOrFail($data['parent_id']), $data['head_name']);
        return back()->with('success', "Account head {$account->code} created successfully.");
    }

    public function vouchers(): View
    {
        return view('admin.accounts.vouchers', [
            'accounts' => AccountCoa::where('is_group', false)->orderBy('code')->get(),
            'transactions' => AccountTransaction::with('account')->latest('transaction_date')->latest('id')->paginate(20),
            'suppliers' => Supplier::orderBy('name')->get(), 'customers' => Customer::orderBy('first_name')->get(),
            'purchases' => Purchase::latest('purchase_date')->limit(100)->get(), 'orders' => EcommerceOrder::latest()->limit(100)->get(),
        ]);
    }

    public function storeVoucher(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'voucher_type' => ['required', 'in:debit,credit,contra,journal'], 'transaction_date' => ['required', 'date'],
            'debit_account_id' => ['required', 'different:credit_account_id', 'exists:account_coas,id'], 'credit_account_id' => ['required', 'exists:account_coas,id'],
            'amount' => ['required', 'numeric', 'gt:0'], 'ledger_comment' => ['nullable', 'string', 'max:2000'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'], 'customer_id' => ['nullable', 'exists:customers,id'],
            'purchase_id' => ['nullable', 'exists:purchases,id'], 'sale_id' => ['nullable', 'integer'],
        ]);
        $voucher = $this->accounts->postVoucher($data, $request->user()->id);
        return to_route('admin.accounts.vouchers')->with('success', "Voucher {$voucher} posted with balanced debit and credit entries.");
    }

    public function reports(Request $request): View
    {
        $report = $request->string('report', 'stock')->toString();
        abort_unless(in_array($report, ['stock', 'sales', 'purchase', 'supplier-ledger', 'customer-ledger'], true), 404);
        $filters = $request->validate(['from_date' => ['nullable', 'date'], 'to_date' => ['nullable', 'date', 'after_or_equal:from_date'], 'product_id' => ['nullable', 'exists:products,id'], 'warehouse_id' => ['nullable', 'exists:warehouses,id'], 'customer_id' => ['nullable'], 'supplier_id' => ['nullable', 'exists:suppliers,id']]);
        $data = match ($report) {
            'stock' => $this->stockReport($filters),
            'sales' => $this->salesReport($filters),
            'purchase' => $this->purchaseReport($filters),
            'supplier-ledger' => $this->ledgerReport($filters, 'supplier'),
            'customer-ledger' => $this->ledgerReport($filters, 'customer'),
        };
        return view('admin.accounts.reports', $data + ['report' => $report, 'filters' => $filters, 'products' => Product::orderBy('name')->get(), 'warehouses' => Warehouse::orderBy('name')->get(), 'customers' => Customer::orderBy('first_name')->get(), 'suppliers' => Supplier::orderBy('name')->get()]);
    }

    private function stockReport(array $filters): array
    {
        $query = WarehouseProductStock::with(['product', 'warehouse'])->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('updated_at', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('updated_at', '<=', $date));
        return ['rows' => $query->orderBy('warehouse_id')->paginate(50), 'summary' => ['quantity' => (clone $query)->sum('quantity')]];
    }

    private function salesReport(array $filters): array
    {
        $query = EcommerceOrder::with(['customer', 'items'])->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))->when($filters['customer_id'] ?? null, fn ($q, $id) => $id === 'walking' ? $q->whereNull('customer_id') : $q->where('customer_id', $id))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->whereHas('items', fn ($items) => $items->where('product_id', $id)));
        return ['rows' => $query->latest()->paginate(50), 'summary' => ['total' => (clone $query)->sum('total'), 'count' => (clone $query)->count()]];
    }

    private function purchaseReport(array $filters): array
    {
        $query = Purchase::with(['supplier', 'warehouse', 'items'])->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '<=', $date))->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->whereHas('items', fn ($items) => $items->where('product_id', $id)));
        return ['rows' => $query->latest('purchase_date')->paginate(50), 'summary' => ['total' => (clone $query)->sum('total'), 'count' => (clone $query)->count()]];
    }

    private function ledgerReport(array $filters, string $entity): array
    {
        $key = $entity.'_id';
        $query = AccountTransaction::with('account')->whereNotNull($key)->when($filters[$key] ?? null, fn ($q, $id) => $q->where($key, $id))->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date));
        return ['rows' => $query->orderBy('transaction_date')->orderBy('id')->paginate(100), 'summary' => ['debit' => (clone $query)->where('entry_type', 'debit')->sum('amount'), 'credit' => (clone $query)->where('entry_type', 'credit')->sum('amount')]];
    }
}
