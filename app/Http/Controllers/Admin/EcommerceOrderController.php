<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EcommerceOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        abort_unless($status === '' || in_array($status, EcommerceOrder::STATUSES, true), 404);
        $query=EcommerceOrder::with('warehouse')->when($status, fn($query) => $query->where('status',$status)); if(!auth()->user()->isSuperAdmin())$query->where('warehouse_id',auth()->user()->warehouse_id); return view('admin.orders.index', ['orders'=>$query->latest()->paginate(15)->withQueryString(), 'selectedStatus'=>$status, 'statuses'=>EcommerceOrder::STATUSES]);
    }

    public function show(EcommerceOrder $order): View { $this->authorizeWarehouse($order); return view('admin.orders.show', ['order'=>$order->load(['items.product','warehouse','statusHistory']), 'statuses'=>EcommerceOrder::STATUSES]); }
    public function updateStatus(Request $request, EcommerceOrder $order): RedirectResponse
    {
        $this->authorizeWarehouse($order); abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->role === 'warehouse_admin', 403, 'Only Warehouse Admins can update ecommerce order status.'); $data = $request->validate(['status'=>'required|in:pending,accepted,processing,shipped,delivered,cancelled','note'=>'nullable|max:500']);
        if ($order->status !== $data['status']) { $order->update(['status'=>$data['status']]); $order->statusHistory()->create($data); }
        return to_route('admin.orders.show',$order)->with('success','Order status updated.');
    }
    public function invoice(EcommerceOrder $order): View { $this->authorizeWarehouse($order); return view('admin.orders.invoice', ['order'=>$order->load(['items','warehouse'])]); }
    private function authorizeWarehouse(EcommerceOrder $order): void { abort_unless(auth()->user()->isSuperAdmin() || $order->warehouse_id===auth()->user()->warehouse_id,403); }
}
