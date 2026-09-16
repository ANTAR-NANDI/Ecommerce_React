<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountCoa;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $draft = null;
        if ($request->filled('draft')) {
            $draft = PosOrder::with('items')->findOrFail($request->integer('draft'));
            abort_unless($draft->status === 'draft' && ($user->isSuperAdmin() || $draft->warehouse_id === $user->warehouse_id), 404);
        } $warehouseQuery = Warehouse::where('is_active', true);
        if (! $user->isSuperAdmin()) {
            $warehouseQuery->whereKey($user->warehouse_id);
        }$stockQuery = Product::with('thumbnail')->where('is_active', true);
        if (! $user->isSuperAdmin()) {
            $stockQuery->with(['warehouseStocks' => fn ($stocks) => $stocks->where('warehouse_id', $user->warehouse_id)]);
        } else {
            $stockQuery->with('warehouseStocks');
        }
        $cash = AccountCoa::where('code', '10011')->firstOrFail();
        $cashMethod = PaymentMethod::firstOrCreate(['name' => 'Cash in Hand'], ['account_coa_id' => $cash->id, 'is_fixed' => true]);
        if ($cashMethod->account_coa_id !== $cash->id) {
            $cashMethod->update(['account_coa_id' => $cash->id, 'is_fixed' => true]);
        }
        $draftItems = $draft ? $draft->items->map(fn ($item) => ['product_id' => $item->product_id, 'product_name' => $item->product_name, 'quantity' => (float) $item->quantity, 'unit_price' => (float) $item->unit_price])->values()->all() : [];

        return view('admin.pos.index', ['products' => $stockQuery->latest()->take(30)->get(), 'brands' => Brand::where('is_active', true)->get(), 'categories' => Category::where('is_active', true)->get(), 'customers' => Customer::where('is_active', true)->orderBy('first_name')->get(), 'paymentMethods' => PaymentMethod::with('account')->orderByDesc('is_fixed')->orderBy('name')->get(), 'warehouses' => $warehouseQuery->get(), 'draft' => $draft, 'draftItems' => $draftItems]);
    }
}
