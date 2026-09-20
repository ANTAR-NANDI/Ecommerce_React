@extends('layouts.admin')
@section('title','Purchase Invoice '.$purchase->purchase_number)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div class="content-heading"><h1>Purchase Invoice</h1><p>{{ $purchase->purchase_number }}</p></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">Back to purchases</a><button type="button" class="btn text-white" style="background:var(--brand)" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print invoice</button></div>
</div>
<div class="panel invoice-sheet mx-auto">
    <div class="d-flex justify-content-between border-bottom pb-4 mb-4"><div><div class="brand-name fs-4">EBay</div><div class="text-muted small mt-2">Purchase and goods-receipt invoice</div></div><div class="text-end"><div class="fw-bold fs-5">{{ $purchase->purchase_number }}</div><div class="text-muted small">{{ $purchase->purchase_date->format('d M Y') }}</div></div></div>
    <div class="row mb-4"><div class="col-6"><div class="text-muted small text-uppercase">Supplier</div><div class="fw-semibold">{{ $purchase->supplier_name }}</div><div class="small">{{ $purchase->supplier_phone }}</div>@if($purchase->supplier?->email)<div class="small">{{ $purchase->supplier->email }}</div>@endif</div><div class="col-6 text-end"><div class="text-muted small text-uppercase">Receiving details</div><div>Warehouse: {{ $purchase->warehouse->name }}</div><div>Status: {{ ucfirst($purchase->status) }}</div>@if($purchase->invoice_number)<div>Supplier invoice: {{ $purchase->invoice_number }}</div>@endif</div></div>
    <table class="table"><thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Cost</th><th class="text-end">Amount</th></tr></thead><tbody>@foreach($purchase->items as $item)<tr><td>{{ $item->product_name }}</td><td class="text-center">{{ rtrim(rtrim(number_format($item->quantity,2),'0'),'.') }}</td><td class="text-end">${{ number_format($item->unit_cost,2) }}</td><td class="text-end fw-semibold">${{ number_format($item->line_total,2) }}</td></tr>@endforeach</tbody><tfoot><tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">${{ number_format($purchase->subtotal,2) }}</td></tr><tr><td colspan="3" class="text-end">Discount</td><td class="text-end">−${{ number_format($purchase->discount,2) }}</td></tr><tr><td colspan="3" class="text-end">Tax</td><td class="text-end">${{ number_format($purchase->tax,2) }}</td></tr><tr><th colspan="3" class="text-end">Total payable</th><th class="text-end fs-5">${{ number_format($purchase->total,2) }}</th></tr></tfoot></table>
    @if($vouchers->isNotEmpty())<div class="border-top pt-3 small text-muted">Ledger vouchers: {{ $vouchers->implode(', ') }}</div>@endif
    @if($purchase->notes)<div class="border-top pt-3 mt-3"><b>Notes</b><p class="mb-0 small">{{ $purchase->notes }}</p></div>@endif
</div>
@endsection
