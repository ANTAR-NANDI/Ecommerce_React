<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrder;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use App\Models\AccountCoa;
use App\Models\AccountTransaction;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EcommerceOrderController extends Controller
{
    public function __construct(private readonly AccountService $accounts) {}
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        abort_unless($status === '' || in_array($status, EcommerceOrder::STATUSES, true), 404);
        $query = EcommerceOrder::with('warehouse')->when($status, fn ($query) => $query->where('status', $status));
        if (! auth()->user()->isSuperAdmin()) {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        return view('admin.orders.index', ['orders' => $query->latest()->paginate(15)->withQueryString(), 'selectedStatus' => $status, 'statuses' => EcommerceOrder::STATUSES]);
    }

    public function show(EcommerceOrder $order): View
    {
        $this->authorizeWarehouse($order);
        $warehouses = Warehouse::where('is_active', true);
        if (! auth()->user()->isSuperAdmin()) {
            $warehouses->whereKey(auth()->user()->warehouse_id);
        }

        return view('admin.orders.show', ['order' => $order->load(['items.product', 'warehouse', 'statusHistory']), 'statuses' => EcommerceOrder::STATUSES, 'warehouses' => $warehouses->orderBy('name')->get()]);
    }

    public function updateStatus(Request $request, EcommerceOrder $order): RedirectResponse
    {
        $this->authorizeWarehouse($order);
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->role === 'warehouse_admin', 403, 'Only Warehouse Admins can update ecommerce order status.');
        $data = $request->validate(['status' => 'required|in:pending,accepted,processing,shipped,delivered,cancelled', 'warehouse_id' => 'nullable|exists:warehouses,id', 'note' => 'nullable|max:500']);
        $transitions = ['pending' => ['accepted', 'cancelled'], 'accepted' => ['processing', 'cancelled'], 'processing' => ['shipped', 'delivered'], 'shipped' => ['delivered']];

        if ($order->status === $data['status']) {
            return to_route('admin.orders.show', $order)->with('success', 'Order status is unchanged.');
        }
        if (! in_array($data['status'], $transitions[$order->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'This order must follow the workflow: Pending → Accepted → Processing → Delivered. Shipped is an optional step.']);
        }
        if ($data['status'] === 'processing' && ! $data['warehouse_id']) {
            throw ValidationException::withMessages(['warehouse_id' => 'Choose the warehouse that will fulfil this order before processing it.']);
        }

        DB::transaction(function () use ($order, $data, $request) {
            $historyNote = $data['note'] ?? null;
            if ($data['status'] === 'processing') {
                $warehouseQuery = Warehouse::whereKey($data['warehouse_id'])->where('is_active', true);
                if (! auth()->user()->isSuperAdmin()) {
                    $warehouseQuery->whereKey(auth()->user()->warehouse_id);
                }
                $warehouse = $warehouseQuery->first();
                if (! $warehouse) {
                    throw ValidationException::withMessages(['warehouse_id' => 'Choose an active warehouse.']);
                }

                foreach ($order->items as $item) {
                    if (! $item->product_id) {
                        continue;
                    }
                    $stock = WarehouseProductStock::where(['warehouse_id' => $warehouse->id, 'product_id' => $item->product_id])->lockForUpdate()->first();
                    if (! $stock || $stock->quantity < $item->quantity) {
                        throw ValidationException::withMessages(['warehouse_id' => "Insufficient stock for {$item->product_name} in {$warehouse->name}."]);
                    }
                    $stock->decrement('quantity', $item->quantity);
                    Product::whereKey($item->product_id)->where('stock_quantity', '>=', $item->quantity)->decrement('stock_quantity', $item->quantity);
                }
                $order->warehouse_id = $warehouse->id;
                $historyNote = trim('Fulfilment warehouse: '.$warehouse->name.($historyNote ? '. '.$historyNote : ''));
            }
            $order->status = $data['status'];
            $order->save();
            $order->statusHistory()->create(['status' => $data['status'], 'note' => $historyNote]);
            if ($data['status'] === 'delivered') {
                $this->postDeliveryLedger($order, $request->user()->id);
            }
        });

        return to_route('admin.orders.show', $order)->with('success', 'Order status updated.');
    }

    public function invoice(EcommerceOrder $order): View
    {
        $this->authorizeWarehouse($order);

        return view('admin.orders.invoice', ['order' => $order->load(['items', 'warehouse'])]);
    }

    private function authorizeWarehouse(EcommerceOrder $order): void
    {
        abort_unless(auth()->user()->isSuperAdmin() || $order->warehouse_id === auth()->user()->warehouse_id, 403);
    }

    private function postDeliveryLedger(EcommerceOrder $order, int $userId): void
    {
        if (AccountTransaction::where('ecommerce_order_id', $order->id)->exists()) {
            return;
        }

        $order->loadMissing(['items.product', 'customer']);
        $revenue = AccountCoa::where('code', '4001')->firstOrFail();
        $inventory = AccountCoa::where('code', '10014')->firstOrFail();
        $costOfGoods = AccountCoa::where('code', '5001')->firstOrFail();
        $receivable = $order->customer
            ? $this->accounts->ensureCustomerHead($order->customer)
            : AccountCoa::where('head_name', 'Online Order Receivable')->where('account_type', 'asset')->first()
                ?? $this->accounts->createChild(AccountCoa::where('code', '1001')->firstOrFail(), 'Online Order Receivable');

        $this->accounts->postVoucher([
            'voucher_type' => 'credit',
            'transaction_date' => now()->toDateString(),
            'debit_account_id' => $receivable->id,
            'credit_account_id' => $revenue->id,
            'amount' => $order->total,
            'ledger_comment' => 'Ecommerce sale '.$order->order_number,
            'customer_id' => $order->customer_id,
            'ecommerce_order_id' => $order->id,
        ], $userId);

        $cost = $order->items->sum(fn ($item) => (float) $item->quantity * (float) ($item->product?->buying_price ?? 0));
        if ($cost > 0) {
            $this->accounts->postVoucher([
                'voucher_type' => 'journal',
                'transaction_date' => now()->toDateString(),
                'debit_account_id' => $costOfGoods->id,
                'credit_account_id' => $inventory->id,
                'amount' => $cost,
                'ledger_comment' => 'Cost of goods sold for '.$order->order_number,
                'customer_id' => $order->customer_id,
                'ecommerce_order_id' => $order->id,
            ], $userId);
        }
    }
}
