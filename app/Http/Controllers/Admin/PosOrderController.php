<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountCoa;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\WarehouseProductStock;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosOrderController extends Controller
{
    public function __construct(private readonly AccountService $accounts) {}

    public function history(): View
    {
        $query = PosOrder::with(['warehouse', 'items'])->where('status', 'completed');
        if (! auth()->user()->isSuperAdmin()) {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        return view('admin.pos.history', ['orders' => $query->latest()->paginate(15)]);
    }

    public function drafts(): View
    {
        $query = PosOrder::with(['warehouse', 'items'])->where('status', 'draft');
        if (! auth()->user()->isSuperAdmin()) {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        return view('admin.pos.drafts', ['orders' => $query->latest()->paginate(15)]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        if (! auth()->user()->isSuperAdmin()) {
            $data['warehouse_id'] = auth()->user()->warehouse_id;
        }

        return DB::transaction(function () use ($data, $request) {
            $order = $this->createOrder($data);
            if ($data['status'] === 'completed') {
                $this->completeOrder($order, $data, $request->user()->id);
            }

            return $data['status'] === 'draft'
                ? to_route('admin.pos.drafts')->with('success', 'POS draft saved.')
                : to_route('admin.pos.invoice', $order)->with('success', 'Sale completed and account transaction posted.');
        });
    }

    public function invoice(PosOrder $order): View
    {
        abort_unless($order->status === 'completed' && $this->canAccess($order), 404);

        return view('admin.pos.invoice', ['order' => $order->load(['items', 'warehouse', 'customer', 'paymentMethod']), 'voucherNo' => AccountTransaction::where('pos_order_id', $order->id)->value('voucher_no')]);
    }

    public function continue(PosOrder $order): RedirectResponse
    {
        abort_unless($order->status === 'draft' && $this->canAccess($order), 404);

        return to_route('admin.pos.index', ['draft' => $order->id]);
    }

    public function update(Request $request, PosOrder $order): RedirectResponse
    {
        abort_unless($order->status === 'draft' && $this->canAccess($order), 404);
        $data = $this->validatedData($request);
        if (! auth()->user()->isSuperAdmin()) {
            $data['warehouse_id'] = auth()->user()->warehouse_id;
        }

        return DB::transaction(function () use ($data, $order, $request) {
            $this->updateOrder($order, $data);
            if ($data['status'] === 'completed') {
                $this->completeOrder($order, $data, $request->user()->id);
            }

            return $data['status'] === 'draft'
                ? to_route('admin.pos.drafts')->with('success', 'POS draft updated.')
                : to_route('admin.pos.invoice', $order)->with('success', 'Sale completed and account transaction posted.');
        });
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id', 'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|max:150', 'customer_phone' => 'nullable|max:30',
            'payment_method_id' => 'nullable|exists:payment_methods,id', 'payment_method' => 'nullable|in:Due',
            'status' => 'required|in:draft,completed', 'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id', 'items.*.product_name' => 'required|max:180',
            'items.*.quantity' => 'required|numeric|gt:0', 'items.*.unit_price' => 'required|numeric|min:0',
        ]);
        if ($data['status'] === 'completed' && ! ($data['payment_method_id'] ?? null) && ($data['payment_method'] ?? null) !== 'Due') {
            throw ValidationException::withMessages(['payment_method_id' => 'Choose a payment method before completing the sale.']);
        }
        if (($data['payment_method'] ?? null) === 'Due' && ! ($data['customer_id'] ?? null)) {
            throw ValidationException::withMessages(['customer_id' => 'A registered customer is required for a due sale.']);
        }
        if (($data['payment_method_id'] ?? null) && ! PaymentMethod::whereKey($data['payment_method_id'])->whereNotNull('account_coa_id')->exists()) {
            throw ValidationException::withMessages(['payment_method_id' => 'This payment method has no account head. Configure it from Accounts → Payment Methods.']);
        }

        return $data;
    }

    private function createOrder(array $data): PosOrder
    {
        $total = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $customer = ! empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;
        $method = ! empty($data['payment_method_id']) ? PaymentMethod::find($data['payment_method_id']) : null;
        $order = PosOrder::create(['order_number' => 'POS-'.now()->format('ymdHis').'-'.random_int(100, 999), 'warehouse_id' => $data['warehouse_id'], 'customer_id' => $customer?->id, 'customer_name' => $customer?->full_name ?: ($data['customer_name'] ?? null), 'customer_phone' => $customer?->phone ?: ($data['customer_phone'] ?? null), 'payment_method' => $method?->name ?: ($data['payment_method'] ?? null), 'payment_method_id' => $method?->id, 'status' => $data['status'], 'subtotal' => $total, 'total' => $total]);
        foreach ($data['items'] as $item) {
            $order->items()->create($item + ['line_total' => $item['quantity'] * $item['unit_price']]);
        }

        return $order;
    }

    private function updateOrder(PosOrder $order, array $data): void
    {
        $total = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $customer = ! empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;
        $method = ! empty($data['payment_method_id']) ? PaymentMethod::find($data['payment_method_id']) : null;
        $order->update(['warehouse_id' => $data['warehouse_id'], 'customer_id' => $customer?->id, 'customer_name' => $customer?->full_name ?: ($data['customer_name'] ?? null), 'customer_phone' => $customer?->phone ?: ($data['customer_phone'] ?? null), 'payment_method' => $method?->name ?: ($data['payment_method'] ?? null), 'payment_method_id' => $method?->id, 'status' => $data['status'], 'subtotal' => $total, 'total' => $total]);
        $order->items()->delete();
        foreach ($data['items'] as $item) {
            $order->items()->create($item + ['line_total' => $item['quantity'] * $item['unit_price']]);
        }
    }

    private function completeOrder(PosOrder $order, array $data, int $userId): void
    {
        $this->reserveStock($order->warehouse_id, $data['items']);
        $revenue = AccountCoa::where('code', '4001')->firstOrFail();
        $isDue = ($data['payment_method'] ?? null) === 'Due';
        $customer = $order->customer;
        $method = $isDue ? null : PaymentMethod::with('account')->findOrFail($order->payment_method_id);
        if ($method && ! $method->account) {
            throw ValidationException::withMessages(['payment_method_id' => 'This payment method has no account head. Configure it from Accounts → Payment Methods.']);
        }
        $debitAccount = $isDue ? $this->accounts->ensureCustomerHead($customer) : $method->account;
        $this->accounts->postVoucher(['voucher_type' => 'credit', 'transaction_date' => now()->toDateString(), 'debit_account_id' => $debitAccount->id, 'credit_account_id' => $revenue->id, 'amount' => $order->total, 'ledger_comment' => 'POS sale '.$order->order_number, 'customer_id' => $isDue ? $customer?->id : null, 'pos_order_id' => $order->id], $userId);
    }

    private function reserveStock(int $warehouseId, array $items): void
    {
        foreach ($items as $item) {
            $stock = WarehouseProductStock::where(['warehouse_id' => $warehouseId, 'product_id' => $item['product_id']])->lockForUpdate()->first();
            if (! $stock || $stock->quantity < $item['quantity']) {
                abort(422, 'Insufficient stock for '.$item['product_name'].' in the selected warehouse.');
            }
            $stock->decrement('quantity', $item['quantity']);
            Product::whereKey($item['product_id'])->where('stock_quantity', '>=', $item['quantity'])->decrement('stock_quantity', $item['quantity']);
        }
    }

    private function canAccess(PosOrder $order): bool
    {
        return auth()->user()->isSuperAdmin() || $order->warehouse_id === auth()->user()->warehouse_id;
    }
}
