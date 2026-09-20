<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AccountCoa, AccountTransaction, Category, Customer, EcommerceOrder, EcommerceOrderItem, PosOrder, Product, Purchase, PurchaseItem, Supplier, User, Warehouse, WarehouseProductStock};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AccountReportController extends Controller
{
    public const FINANCIAL_REPORTS = [
        'cash-book' => 'Cash Book', 'bank-book' => 'Bank Book', 'day-book' => 'Day Book',
        'general-ledger' => 'General Ledger', 'sub-ledger' => 'Sub Ledger', 'trial-balance' => 'Trial Balance',
        'income-statement' => 'Income Statement', 'expenditure-statement' => 'Expenditure Statement',
        'profit-loss' => 'Profit & Loss', 'balance-sheet' => 'Balance Sheet',
        'fixed-asset-schedule' => 'Fixed Asset Schedule', 'receipt-payment' => 'Receipt & Payment',
        'bank-reconciliation' => 'Bank Reconciliation Report', 'coa-print' => 'COA Print',
    ];

    public const OPERATIONAL_REPORTS = [
        'stock' => 'Stock Report', 'closing' => 'Closing', 'closing-report' => 'Closing Report',
        'todays-report' => "Today's Report", 'todays-customer-receipt' => "Today's Customer Receipt",
        'sales' => 'Sales Report', 'user-wise-sales' => 'User Wise Sales Report', 'due' => 'Due Report',
        'shipping-cost' => 'Shipping Cost Report', 'purchase' => 'Purchase Report',
        'product-price-comparison' => 'Product Price Comparison Report',
        'purchase-category-wise' => 'Purchase Report (Category Wise)', 'sales-product-wise' => 'Sales Report (Product Wise)',
        'sales-category-wise' => 'Sales Report (Category Wise)', 'sales-return' => 'Sales Return',
        'supplier-return' => 'Supplier Return', 'tax' => 'Tax Report', 'profit-sale-wise' => 'Profit Report (Sale Wise)',
        'supplier-ledger' => 'Supplier Ledger', 'customer-ledger' => 'Customer Ledger',
    ];

    public function index(Request $request, ?string $report = null): View
    {
        $report ??= $request->string('report', 'cash-book')->toString();
        abort_unless(isset(self::FINANCIAL_REPORTS[$report]) || isset(self::OPERATIONAL_REPORTS[$report]), 404);

        $filters = $request->validate([
            'from_date' => ['nullable', 'date'], 'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'account_id' => ['nullable', 'exists:account_coas,id'], 'product_id' => ['nullable', 'exists:products,id'],
            'category_id' => ['nullable', 'exists:categories,id'], 'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'customer_id' => ['nullable'], 'supplier_id' => ['nullable', 'exists:suppliers,id'], 'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $result = $this->build($report, $filters);

        return view('admin.accounts.report', compact('report', 'filters', 'result') + [
            'financialReports' => self::FINANCIAL_REPORTS, 'operationalReports' => self::OPERATIONAL_REPORTS,
            'accounts' => AccountCoa::orderBy('code')->get(), 'products' => Product::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(), 'warehouses' => Warehouse::orderBy('name')->get(),
            'customers' => Customer::orderBy('first_name')->get(), 'suppliers' => Supplier::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    private function build(string $report, array $filters): array
    {
        return match ($report) {
            'cash-book' => $this->book($filters, '10011', 'Cash Book'),
            'bank-book' => $this->book($filters, '10012', 'Bank Book'),
            'day-book' => $this->dayBook($filters),
            'general-ledger' => $this->generalLedger($filters),
            'sub-ledger' => $this->subLedger($filters),
            'trial-balance' => $this->trialBalance($filters),
            'income-statement' => $this->statement($filters, ['income'], 'Income Statement'),
            'expenditure-statement' => $this->statement($filters, ['expense'], 'Expenditure Statement'),
            'profit-loss' => $this->profitLoss($filters),
            'balance-sheet' => $this->balanceSheet($filters),
            'fixed-asset-schedule' => $this->fixedAssets($filters),
            'receipt-payment' => $this->receiptPayment($filters),
            'bank-reconciliation' => $this->bankReconciliation($filters),
            'coa-print' => $this->coaPrint(),
            'stock' => $this->stock($filters),
            'closing', 'closing-report' => $this->closing($filters, $report === 'closing' ? 'Closing' : 'Closing Report'),
            'todays-report' => $this->today($filters),
            'todays-customer-receipt' => $this->todayReceipts(),
            'sales' => $this->sales($filters),
            'user-wise-sales' => $this->userSales($filters),
            'due' => $this->due($filters),
            'shipping-cost' => $this->shipping($filters),
            'purchase' => $this->purchases($filters),
            'product-price-comparison' => $this->productPriceComparison($filters),
            'purchase-category-wise' => $this->purchaseByCategory($filters),
            'sales-product-wise' => $this->salesByProduct($filters),
            'sales-category-wise' => $this->salesByCategory($filters),
            'sales-return' => $this->emptyReport('Sales Return', 'No sales-return records exist yet. This report will populate when a sales-return workflow is added.'),
            'supplier-return' => $this->emptyReport('Supplier Return', 'No supplier-return records exist yet. This report will populate when a purchase-return workflow is added.'),
            'tax' => $this->tax($filters),
            'profit-sale-wise' => $this->saleProfit($filters),
            'supplier-ledger' => $this->entityLedger($filters, 'supplier'),
            'customer-ledger' => $this->entityLedger($filters, 'customer'),
        };
    }

    private function base(string $title, array $columns, Collection|array $rows, array $totals = [], array $filterKeys = ['date'], ?string $notice = null): array
    {
        return compact('title', 'columns', 'rows', 'totals', 'filterKeys', 'notice');
    }

    private function ledgerQuery(array $filters): Builder
    {
        return AccountTransaction::with('account')
            ->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date));
    }

    private function book(array $filters, string $codePrefix, string $title): array
    {
        $ids = AccountCoa::where('code', 'like', $codePrefix.'%')->pluck('id');
        $entries = $this->ledgerQuery($filters)->whereIn('account_coa_id', $ids)->orderBy('transaction_date')->orderBy('id')->get();
        $running = 0;
        $rows = $entries->map(function ($entry) use (&$running) { $running += $entry->entry_type === 'debit' ? (float) $entry->amount : -(float) $entry->amount; return [$entry->transaction_date->format('d M Y'), $entry->voucher_no, $entry->account->head_name, $entry->ledger_comment ?: '—', $entry->entry_type === 'debit' ? $this->money($entry->amount) : '—', $entry->entry_type === 'credit' ? $this->money($entry->amount) : '—', $this->money($running)]; });
        return $this->base($title, ['Date', 'Voucher', 'Account', 'Narration', 'Receipt', 'Payment', 'Balance'], $rows, ['Receipts' => $entries->where('entry_type', 'debit')->sum('amount'), 'Payments' => $entries->where('entry_type', 'credit')->sum('amount'), 'Balance' => $running]);
    }

    private function dayBook(array $filters): array
    {
        $entries = $this->ledgerQuery($filters)->orderBy('transaction_date')->orderBy('voucher_no')->get();
        return $this->base('Day Book', ['Date', 'Voucher', 'Type', 'Account', 'Narration', 'Debit', 'Credit'], $entries->map(fn ($e) => [$e->transaction_date->format('d M Y'), $e->voucher_no, ucfirst($e->voucher_type), $e->account->code.' — '.$e->account->head_name, $e->ledger_comment ?: '—', $e->entry_type === 'debit' ? $this->money($e->amount) : '—', $e->entry_type === 'credit' ? $this->money($e->amount) : '—']), ['Debit' => $entries->where('entry_type', 'debit')->sum('amount'), 'Credit' => $entries->where('entry_type', 'credit')->sum('amount')]);
    }

    private function balances(array $filters): Collection
    {
        $entries = $this->ledgerQuery($filters)->get()->groupBy('account_coa_id');
        return AccountCoa::where('is_group', false)->orderBy('code')->get()->map(function ($account) use ($entries) { $tx = $entries->get($account->id, collect()); $debit = (float) $tx->where('entry_type', 'debit')->sum('amount'); $credit = (float) $tx->where('entry_type', 'credit')->sum('amount'); $normal = in_array($account->account_type, ['asset', 'expense'], true) ? $debit - $credit : $credit - $debit; return compact('account', 'debit', 'credit', 'normal'); });
    }

    private function generalLedger(array $filters): array
    {
        $rows = $this->balances($filters);
        return $this->base('General Ledger', ['Code', 'Account Head', 'Type', 'Total Debit', 'Total Credit', 'Balance'], $rows->map(fn ($r) => [$r['account']->code, $r['account']->head_name, ucfirst($r['account']->account_type), $this->money($r['debit']), $this->money($r['credit']), $this->money($r['normal'])]), ['Debit' => $rows->sum('debit'), 'Credit' => $rows->sum('credit')], ['date', 'account']);
    }

    private function subLedger(array $filters): array
    {
        $query = $this->ledgerQuery($filters)->when($filters['account_id'] ?? null, fn ($q, $id) => $q->where('account_coa_id', $id));
        $entries = $query->orderBy('transaction_date')->orderBy('id')->get(); $running = 0;
        $rows = $entries->map(function ($e) use (&$running) { $running += $e->entry_type === 'debit' ? (float) $e->amount : -(float) $e->amount; return [$e->transaction_date->format('d M Y'), $e->voucher_no, $e->account->code.' — '.$e->account->head_name, $e->ledger_comment ?: '—', $e->entry_type === 'debit' ? $this->money($e->amount) : '—', $e->entry_type === 'credit' ? $this->money($e->amount) : '—', $this->money($running)]; });
        return $this->base('Sub Ledger', ['Date', 'Voucher', 'Account', 'Narration', 'Debit', 'Credit', 'Running Balance'], $rows, ['Debit' => $entries->where('entry_type', 'debit')->sum('amount'), 'Credit' => $entries->where('entry_type', 'credit')->sum('amount'), 'Balance' => $running], ['date', 'account']);
    }

    private function trialBalance(array $filters): array
    {
        $balances = $this->balances($filters); $debits = 0; $credits = 0;
        $rows = $balances->map(function ($r) use (&$debits, &$credits) { $signed = in_array($r['account']->account_type, ['asset', 'expense'], true) ? $r['normal'] : -$r['normal']; $debit = max($signed, 0); $credit = max(-$signed, 0); $debits += $debit; $credits += $credit; return [$r['account']->code, $r['account']->head_name, $this->money($debit), $this->money($credit)]; });
        return $this->base('Trial Balance', ['Code', 'Account Head', 'Debit Balance', 'Credit Balance'], $rows, ['Debit Balance' => $debits, 'Credit Balance' => $credits]);
    }

    private function statement(array $filters, array $types, string $title): array
    {
        $rows = $this->balances($filters)->filter(fn ($r) => in_array($r['account']->account_type, $types, true));
        return $this->base($title, ['Code', 'Account Head', 'Amount'], $rows->map(fn ($r) => [$r['account']->code, $r['account']->head_name, $this->money($r['normal'])]), ['Total' => $rows->sum('normal')]);
    }

    private function profitLoss(array $filters): array
    {
        $balances = $this->balances($filters); $income = $balances->filter(fn ($r) => $r['account']->account_type === 'income')->sum('normal'); $expense = $balances->filter(fn ($r) => $r['account']->account_type === 'expense')->sum('normal');
        return $this->base('Profit & Loss', ['Particular', 'Amount'], collect([['Total Income', $this->money($income)], ['Total Expenses', $this->money($expense)], [$income - $expense >= 0 ? 'Net Profit' : 'Net Loss', $this->money(abs($income - $expense))]]), ['Net Profit / Loss' => $income - $expense]);
    }

    private function balanceSheet(array $filters): array
    {
        $balances = $this->balances($filters)->filter(fn ($r) => in_array($r['account']->account_type, ['asset', 'liability', 'equity'], true));
        return $this->base('Balance Sheet', ['Type', 'Code', 'Account Head', 'Balance'], $balances->map(fn ($r) => [ucfirst($r['account']->account_type), $r['account']->code, $r['account']->head_name, $this->money($r['normal'])]), ['Assets' => $balances->filter(fn ($r) => $r['account']->account_type === 'asset')->sum('normal'), 'Liabilities' => $balances->filter(fn ($r) => $r['account']->account_type === 'liability')->sum('normal'), 'Equity' => $balances->filter(fn ($r) => $r['account']->account_type === 'equity')->sum('normal')]);
    }

    private function fixedAssets(array $filters): array
    {
        $rows = $this->balances($filters)->filter(fn ($r) => $r['account']->account_type === 'asset' && ! str_starts_with($r['account']->code, '1001'));
        return $this->base('Fixed Asset Schedule', ['Code', 'Asset Account', 'Book Value'], $rows->map(fn ($r) => [$r['account']->code, $r['account']->head_name, $this->money($r['normal'])]), ['Book Value' => $rows->sum('normal')], ['date'], 'Fixed assets are derived from non-current asset ledger heads.');
    }

    private function receiptPayment(array $filters): array { return $this->book($filters, '1001', 'Receipt & Payment'); }
    private function bankReconciliation(array $filters): array { $result = $this->book($filters, '10012', 'Bank Reconciliation Report'); $result['notice'] = 'This compares recorded bank-ledger receipts and payments. Statement matching can be added when bank statements are imported.'; return $result; }

    private function coaPrint(): array
    {
        $accounts = AccountCoa::with('parent')->orderBy('code')->get();
        return $this->base('Chart of Accounts', ['Code', 'Account Head', 'Parent', 'Type', 'Kind'], $accounts->map(fn ($a) => [$a->code, $a->head_name, $a->parent?->head_name ?: 'Root', ucfirst($a->account_type), $a->is_group ? 'Group' : 'Ledger']), [], [], 'Use the Print button to print or save this COA as PDF.');
    }

    private function stock(array $filters): array
    {
        $stocks = WarehouseProductStock::with(['warehouse', 'product'])->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))->get();
        return $this->base('Stock Report', ['Warehouse', 'SKU', 'Product', 'Quantity', 'Cost Value', 'Selling Value'], $stocks->map(fn ($s) => [$s->warehouse->name, $s->product->sku, $s->product->name, number_format((float) $s->quantity, 3), $this->money((float) $s->quantity * (float) $s->product->buying_price), $this->money((float) $s->quantity * (float) $s->product->selling_price)]), ['Quantity' => $stocks->sum('quantity'), 'Cost Value' => $stocks->sum(fn ($s) => (float) $s->quantity * (float) $s->product->buying_price), 'Selling Value' => $stocks->sum(fn ($s) => (float) $s->quantity * (float) $s->product->selling_price)], ['warehouse', 'product']);
    }

    private function salesQuery(array $filters): Builder
    {
        return EcommerceOrder::with(['customer', 'items.product'])->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))->when($filters['customer_id'] ?? null, fn ($q, $id) => $id === 'walking' ? $q->whereNull('customer_id') : $q->where('customer_id', $id))->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->whereHas('items', fn ($items) => $items->where('product_id', $id)))->when($filters['category_id'] ?? null, fn ($q, $id) => $q->whereHas('items.product', fn ($p) => $p->where('category_id', $id)));
    }

    private function sales(array $filters): array
    {
        $orders = $this->salesQuery($filters)->latest()->get();
        return $this->base('Sales Report', ['Date', 'Order', 'Customer', 'Status', 'Items', 'Subtotal', 'Discount', 'Total'], $orders->map(fn ($o) => [$o->created_at->format('d M Y'), $o->order_number, $o->customer?->full_name ?: 'Walking customer / guest', ucfirst($o->status), number_format((float) $o->items->sum('quantity'), 3), $this->money($o->subtotal), $this->money($o->discount), $this->money($o->total)]), ['Orders' => $orders->count(), 'Sales' => $orders->sum('total'), 'Discount' => $orders->sum('discount')], ['date', 'customer', 'product', 'warehouse']);
    }

    private function purchasesQuery(array $filters): Builder
    {
        return Purchase::with(['supplier', 'warehouse', 'items.product'])->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '<=', $date))->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->whereHas('items', fn ($items) => $items->where('product_id', $id)))->when($filters['category_id'] ?? null, fn ($q, $id) => $q->whereHas('items.product', fn ($p) => $p->where('category_id', $id)));
    }

    private function purchases(array $filters): array
    {
        $purchases = $this->purchasesQuery($filters)->latest('purchase_date')->get();
        return $this->base('Purchase Report', ['Date', 'Purchase', 'Supplier', 'Warehouse', 'Status', 'Items', 'Tax', 'Total'], $purchases->map(fn ($p) => [$p->purchase_date->format('d M Y'), $p->purchase_number, $p->supplier?->name ?: $p->supplier_name, $p->warehouse->name, ucfirst($p->status), number_format((float) $p->items->sum('quantity'), 3), $this->money($p->tax), $this->money($p->total)]), ['Purchases' => $purchases->count(), 'Tax' => $purchases->sum('tax'), 'Total' => $purchases->sum('total')], ['date', 'supplier', 'product', 'category', 'warehouse']);
    }

    private function productPriceComparison(array $filters): array
    {
        $items = PurchaseItem::with(['purchase', 'product'])
            ->whereHas('purchase', function (Builder $query) use ($filters) {
                $query->where('status', 'received')
                    ->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '>=', $date))
                    ->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '<=', $date));
            })
            ->when($filters['product_id'] ?? null, fn ($query, $id) => $query->where('product_id', $id))
            ->orderByDesc(Purchase::select('purchase_date')->whereColumn('purchases.id', 'purchase_items.purchase_id')->limit(1))
            ->orderByDesc('purchase_id')
            ->orderBy('id')
            ->get();

        return $this->base(
            'Product Price Comparison Report',
            ['Purchase Date', 'Supplier', 'Product', 'SKU', 'Purchase Reference', 'Quantity', 'Unit Cost', 'Line Total'],
            $items->map(fn ($item) => [
                $item->purchase->purchase_date->format('d M Y'),
                $item->purchase->supplier_name,
                $item->product_name,
                $item->product?->sku ?? '—',
                $item->purchase->purchase_number,
                number_format((float) $item->quantity, 3),
                $this->money($item->unit_cost),
                $this->money($item->line_total),
            ]),
            [],
            ['date', 'product'],
            'Received purchases only. Unit costs are the prices saved on each purchase, before purchase-level discounts and tax. Select a product to compare supplier prices over time.',
        );
    }

    private function purchaseByCategory(array $filters): array
    {
        $items = PurchaseItem::with(['purchase', 'product.category'])->whereHas('purchase', fn ($q) => $this->applyPurchaseFilters($q, $filters))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))->when($filters['category_id'] ?? null, fn ($q, $id) => $q->whereHas('product', fn ($product) => $product->where('category_id', $id)))->get();
        $groups = $items->groupBy(fn ($i) => $i->product?->category?->name ?: 'Uncategorized');
        return $this->base('Purchase Report (Category Wise)', ['Category', 'Quantity', 'Purchase Value'], $groups->map(fn ($group, $name) => [$name, number_format((float) $group->sum('quantity'), 3), $this->money($group->sum('line_total'))])->values(), ['Quantity' => $items->sum('quantity'), 'Purchase Value' => $items->sum('line_total')], ['date', 'supplier', 'product', 'category', 'warehouse']);
    }

    private function salesByProduct(array $filters): array
    {
        $items = $this->salesItems($filters); $groups = $items->groupBy(fn ($i) => $i->product_id ?: $i->product_name);
        return $this->base('Sales Report (Product Wise)', ['Product', 'SKU', 'Quantity', 'Sales Value'], $groups->map(function ($group) { $first = $group->first(); return [$first->product?->name ?: $first->product_name, $first->product?->sku ?: '—', number_format((float) $group->sum('quantity'), 3), $this->money($group->sum('line_total'))]; })->values(), ['Quantity' => $items->sum('quantity'), 'Sales Value' => $items->sum('line_total')], ['date', 'customer', 'product', 'category', 'warehouse']);
    }

    private function salesByCategory(array $filters): array
    {
        $items = $this->salesItems($filters); $groups = $items->groupBy(fn ($i) => $i->product?->category?->name ?: 'Uncategorized');
        return $this->base('Sales Report (Category Wise)', ['Category', 'Quantity', 'Sales Value'], $groups->map(fn ($group, $name) => [$name, number_format((float) $group->sum('quantity'), 3), $this->money($group->sum('line_total'))])->values(), ['Quantity' => $items->sum('quantity'), 'Sales Value' => $items->sum('line_total')], ['date', 'customer', 'product', 'category', 'warehouse']);
    }

    private function salesItems(array $filters): Collection
    {
        return EcommerceOrderItem::with(['order', 'product.category'])->whereHas('order', fn ($q) => $this->applySalesFilters($q, $filters))->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))->when($filters['category_id'] ?? null, fn ($q, $id) => $q->whereHas('product', fn ($p) => $p->where('category_id', $id)))->get();
    }

    private function userSales(array $filters): array
    {
        $online = $this->salesQuery($filters)->get();
        $pos = PosOrder::where('status', 'completed')->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))->get();
        $rows = collect([['Online Store', $online->count(), $this->money($online->sum('total'))], ['POS Users', $pos->count(), $this->money($pos->sum('total'))]]);
        return $this->base('User Wise Sales Report', ['Sales Channel / User', 'Orders', 'Sales'], $rows, ['Orders' => $online->count() + $pos->count(), 'Sales' => $online->sum('total') + $pos->sum('total')], ['date', 'warehouse'], 'Existing sales do not store created_by, so current records are grouped by Online Store and POS.');
    }

    private function due(array $filters): array
    {
        $orders = $this->salesQuery($filters)->where('payment_status', '!=', 'paid')->get();
        return $this->base('Due Report', ['Date', 'Order', 'Customer', 'Payment Status', 'Due Amount'], $orders->map(fn ($o) => [$o->created_at->format('d M Y'), $o->order_number, $o->customer?->full_name ?: $o->customer_name, ucfirst($o->payment_status), $this->money($o->total)]), ['Due Orders' => $orders->count(), 'Due Amount' => $orders->sum('total')], ['date', 'customer', 'warehouse']);
    }

    private function shipping(array $filters): array
    {
        $orders = $this->salesQuery($filters)->get();
        return $this->base('Shipping Cost Report', ['Date', 'Order', 'Customer', 'Warehouse', 'Shipping Cost'], $orders->map(fn ($o) => [$o->created_at->format('d M Y'), $o->order_number, $o->customer_name, $o->warehouse?->name ?: '—', $this->money($o->shipping_charge)]), ['Shipping Cost' => $orders->sum('shipping_charge')], ['date', 'customer', 'warehouse']);
    }

    private function tax(array $filters): array
    {
        $purchases = $this->purchasesQuery($filters)->get();
        return $this->base('Tax Report', ['Date', 'Reference', 'Source', 'Tax Amount'], $purchases->map(fn ($p) => [$p->purchase_date->format('d M Y'), $p->purchase_number, 'Purchase', $this->money($p->tax)]), ['Purchase Tax' => $purchases->sum('tax')], ['date', 'supplier', 'warehouse'], 'Sales tax is not stored separately in the current order table; this report shows recorded purchase tax.');
    }

    private function saleProfit(array $filters): array
    {
        $orders = $this->salesQuery($filters)->get();
        $rows = $orders->map(function ($o) { $cost = $o->items->sum(fn ($i) => (float) $i->quantity * (float) ($i->product?->buying_price ?? 0)); $profit = (float) $o->total - $cost - (float) $o->shipping_charge; return [$o->created_at->format('d M Y'), $o->order_number, $o->customer_name, $this->money($o->total), $this->money($cost), $this->money($o->shipping_charge), $this->money($profit), (float) $o->total > 0 ? number_format($profit / (float) $o->total * 100, 2).'%' : '0%']; });
        $profit = $orders->sum(fn ($o) => (float) $o->total - $o->items->sum(fn ($i) => (float) $i->quantity * (float) ($i->product?->buying_price ?? 0)) - (float) $o->shipping_charge);
        return $this->base('Profit Report (Sale Wise)', ['Date', 'Order', 'Customer', 'Sales', 'Product Cost', 'Shipping', 'Profit', 'Margin'], $rows, ['Sales' => $orders->sum('total'), 'Profit' => $profit], ['date', 'customer', 'product', 'warehouse']);
    }

    private function entityLedger(array $filters, string $entity): array
    {
        $key = $entity.'_id'; $entries = $this->ledgerQuery($filters)->whereNotNull($key)->when($filters[$key] ?? null, fn ($q, $id) => $q->where($key, $id))->orderBy('transaction_date')->orderBy('id')->get(); $running = 0;
        $rows = $entries->map(function ($e) use (&$running, $entity) { $running += $e->entry_type === 'debit' ? (float) $e->amount : -(float) $e->amount; $party = $entity === 'supplier' ? $e->supplier?->name : $e->customer?->full_name; return [$e->transaction_date->format('d M Y'), $party ?: '—', $e->voucher_no, $e->ledger_comment ?: '—', $e->entry_type === 'debit' ? $this->money($e->amount) : '—', $e->entry_type === 'credit' ? $this->money($e->amount) : '—', $this->money($running)]; });
        return $this->base(ucfirst($entity).' Ledger', ['Date', ucfirst($entity), 'Voucher', 'Narration', 'Debit', 'Credit', 'Balance'], $rows, ['Debit' => $entries->where('entry_type', 'debit')->sum('amount'), 'Credit' => $entries->where('entry_type', 'credit')->sum('amount'), 'Balance' => $running], ['date', $entity]);
    }

    private function closing(array $filters, string $title): array
    {
        $date = $filters['to_date'] ?? now()->toDateString();
        $sales = EcommerceOrder::whereDate('created_at', $date)->sum('total'); $purchases = Purchase::whereDate('purchase_date', $date)->sum('total');
        $receipts = AccountTransaction::whereDate('transaction_date', $date)->where('entry_type', 'debit')->sum('amount'); $payments = AccountTransaction::whereDate('transaction_date', $date)->where('entry_type', 'credit')->sum('amount');
        return $this->base($title, ['Particular', 'Amount'], collect([['Sales', $this->money($sales)], ['Purchases', $this->money($purchases)], ['Ledger Receipts', $this->money($receipts)], ['Ledger Payments', $this->money($payments)], ['Net Cash Movement', $this->money($receipts - $payments)]]), ['Sales' => $sales, 'Purchases' => $purchases, 'Net Movement' => $receipts - $payments], ['date']);
    }

    private function today(array $filters): array { $filters['from_date'] = now()->toDateString(); $filters['to_date'] = now()->toDateString(); return $this->closing($filters, "Today's Report"); }

    private function todayReceipts(): array
    {
        $entries = AccountTransaction::with(['account', 'customer'])->whereDate('transaction_date', now()->toDateString())->whereNotNull('customer_id')->where('entry_type', 'debit')->get();
        return $this->base("Today's Customer Receipt", ['Date', 'Customer', 'Voucher', 'Account', 'Amount'], $entries->map(fn ($e) => [$e->transaction_date->format('d M Y'), $e->customer?->full_name ?: '—', $e->voucher_no, $e->account->head_name, $this->money($e->amount)]), ['Receipts' => $entries->sum('amount')], []);
    }

    private function applySalesFilters($q, array $filters): void
    {
        $q->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))->when($filters['customer_id'] ?? null, fn ($q, $id) => $id === 'walking' ? $q->whereNull('customer_id') : $q->where('customer_id', $id))->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id));
    }

    private function applyPurchaseFilters($q, array $filters): void
    {
        $q->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '>=', $date))->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('purchase_date', '<=', $date))->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id));
    }

    private function emptyReport(string $title, string $notice): array { return $this->base($title, ['Information'], collect(), [], ['date'], $notice); }
    private function money(float|int|string|null $amount): string { return '$'.number_format((float) $amount, 2); }
}
