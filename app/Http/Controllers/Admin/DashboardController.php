<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\EcommerceOrder;
use App\Models\Product;
use App\Models\Warehouse;
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
        return view('admin.dashboard', compact('metrics', 'statuses', 'user'));
    }
}
