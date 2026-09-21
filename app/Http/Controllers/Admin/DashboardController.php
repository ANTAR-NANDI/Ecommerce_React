<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountTransaction;
use App\Models\Category;
use App\Models\Customer;
use App\Models\EcommerceOrder;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $orders = EcommerceOrder::query()->when(! $user->isSuperAdmin(), fn ($query) => $query->where('warehouse_id', $user->warehouse_id));
        $products = Product::query();
        $metrics = [
            'warehouses' => $user->isSuperAdmin() ? Warehouse::where('is_active', true)->count() : 1,
            'products' => $products->where('is_active', true)->count(),
            'categories' => Category::where('is_active', true)->count(),
            'orders_month' => (clone $orders)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'customers' => Customer::where('is_active', true)->count(),
            'revenue' => (float) (clone $orders)->where('status', 'delivered')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total'),
        ];
        $statuses = collect(EcommerceOrder::STATUSES)->mapWithKeys(fn ($status) => [$status => (clone $orders)->where('status', $status)->count()]);
        $chartData = $user->isSuperAdmin() ? $this->financialChartData() : null;

        return view('admin.dashboard', compact('metrics', 'statuses', 'user', 'chartData'));
    }

    private function financialChartData(): array
    {
        $months = collect(range(5, 0))->map(function (int $offset) {
            $date = now()->subMonths($offset)->startOfMonth();

            return ['key' => $date->format('Y-m'), 'label' => $date->format('M Y')];
        });
        $start = Carbon::parse($months->first()['key'].'-01')->startOfMonth();
        $end = now()->endOfMonth();
        $entries = AccountTransaction::with('account')->whereBetween('transaction_date', [$start, $end])->get();
        $purchases = Purchase::where('status', 'received')->whereBetween('purchase_date', [$start, $end])->get();

        $monthly = $months->map(function (array $month) use ($entries, $purchases) {
            $monthEntries = $entries->filter(fn (AccountTransaction $entry) => $entry->transaction_date->format('Y-m') === $month['key']);
            $sales = $monthEntries->filter(fn (AccountTransaction $entry) => $entry->account?->code === '4001' && $entry->entry_type === 'credit')->sum('amount');
            $purchasesTotal = $purchases->filter(fn (Purchase $purchase) => $purchase->purchase_date->format('Y-m') === $month['key'])->sum('total');

            return ['label' => $month['label'], 'sales' => (float) $sales, 'purchases' => (float) $purchasesTotal];
        })->values();

        $sales = $entries->filter(fn (AccountTransaction $entry) => $entry->account?->code === '4001' && $entry->entry_type === 'credit')->sum('amount');
        $costOfGoodsSold = $entries->filter(fn (AccountTransaction $entry) => $entry->account?->code === '5001' && $entry->entry_type === 'debit')->sum('amount');
        $operatingExpense = $entries->filter(fn (AccountTransaction $entry) => $entry->account?->account_type === 'expense' && $entry->account?->code !== '5001' && $entry->entry_type === 'debit')->sum('amount');
        $grossProfit = $sales - $costOfGoodsSold;
        $netResult = $grossProfit - $operatingExpense;
        $totalExpense = $costOfGoodsSold + $operatingExpense;

        return [
            'monthly' => $monthly,
            'monthly_max' => max(1, $monthly->max(fn (array $item) => max($item['sales'], $item['purchases']))),
            'performance' => collect([
                ['label' => 'Sales income', 'value' => (float) $sales, 'class' => 'bg-success'],
                ['label' => 'Cost of goods sold', 'value' => (float) $costOfGoodsSold, 'class' => 'bg-warning'],
                ['label' => 'Operating expenses', 'value' => (float) $operatingExpense, 'class' => 'bg-primary'],
                ['label' => $netResult >= 0 ? 'Net profit' : 'Net loss', 'value' => abs((float) $netResult), 'class' => $netResult >= 0 ? 'bg-success' : 'bg-danger'],
            ]),
            'performance_max' => max(1, (float) $sales, (float) $costOfGoodsSold, (float) $operatingExpense),
            'kpis' => [
                ['label' => 'Sales income', 'value' => (float) $sales, 'icon' => 'graph-up-arrow', 'tint' => '#eaf9ef', 'color' => '#198754'],
                ['label' => 'Gross profit', 'value' => (float) $grossProfit, 'icon' => 'cash-stack', 'tint' => '#e8f3ff', 'color' => '#0d6efd'],
                ['label' => 'Operating expenses', 'value' => (float) $operatingExpense, 'icon' => 'receipt', 'tint' => '#fff6df', 'color' => '#b77900'],
                ['label' => $netResult >= 0 ? 'Net profit' : 'Net loss', 'value' => abs((float) $netResult), 'negative' => $netResult < 0, 'icon' => $netResult >= 0 ? 'arrow-up-right-circle' : 'arrow-down-right-circle', 'tint' => $netResult >= 0 ? '#eaf9ef' : '#ffedf0', 'color' => $netResult >= 0 ? '#198754' : '#dc3545'],
            ],
            'expense_pie' => [
                ['label' => 'Cost of goods sold', 'value' => (float) $costOfGoodsSold, 'color' => '#f0ad4e'],
                ['label' => 'Operating expenses', 'value' => (float) $operatingExpense, 'color' => '#0d6efd'],
                'total' => max(1, (float) $totalExpense),
            ],
            'waterfall' => [
                ['label' => 'Sales income', 'value' => (float) $sales, 'direction' => 'up', 'color' => '#198754'],
                ['label' => 'Less COGS', 'value' => (float) $costOfGoodsSold, 'direction' => 'down', 'color' => '#f0ad4e'],
                ['label' => 'Less expenses', 'value' => (float) $operatingExpense, 'direction' => 'down', 'color' => '#0d6efd'],
                ['label' => $netResult >= 0 ? 'Net profit' : 'Net loss', 'value' => abs((float) $netResult), 'direction' => $netResult >= 0 ? 'up' : 'down', 'color' => $netResult >= 0 ? '#198754' : '#dc3545'],
            ],
            'waterfall_max' => max(1, (float) $sales, (float) $costOfGoodsSold, (float) $operatingExpense, abs((float) $netResult)),
        ];
    }
}
