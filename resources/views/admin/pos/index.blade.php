@extends('layouts.admin')
@section('title', $draft ? 'Continue POS Draft' : 'Point of Sale')
@section('content')
@php($selectedWarehouseId = $draft ? $draft->warehouse_id : null)
<div class="content-heading mb-4">
    <h1>{{ $draft ? 'Continue POS Draft' : 'Point of Sale' }}</h1>
    <p>{{ $draft ? 'Your saved cart has been restored. Review it and complete the sale.' : 'Select a warehouse first. Stock shown belongs to that location.' }}</p>
</div>
<div class="row g-4">
    <div class="col-xl-8"><div class="panel">
        <div class="row g-2 mb-4">
            <div class="col-md-4"><label class="form-label">Selling warehouse <span class="text-danger">*</span></label><select class="form-select" id="pos-warehouse"><option value="">Select warehouse</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" {{ $selectedWarehouseId == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} — {{ $warehouse->code }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Brand</label><select class="form-select" id="pos-brand"><option value="">All brands</option>@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach</select></div>
            <div class="col-md-5"><label class="form-label">Find product</label><div class="input-group"><input id="pos-search" class="form-control" placeholder="Search by product name or SKU"><span class="input-group-text"><i class="bi bi-search"></i></span></div></div>
        </div>
        <div class="alert alert-primary border-0 small py-2" id="pos-warehouse-notice"><i class="bi bi-info-circle me-1"></i>Choose a warehouse to see available inventory.</div>
        <div class="row g-3" id="pos-products">
            @forelse($products as $product)
            <div class="col-md-6 pos-product" data-name="{{ strtolower($product->name.' '.$product->sku) }}" data-brand="{{ $product->brand_id }}" data-stocks='{{ $product->warehouseStocks->pluck('quantity','warehouse_id')->toJson() }}'>
                <button type="button" class="w-100 text-start border rounded-3 p-3 bg-white pos-add" disabled data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->selling_price }}"><div class="d-flex align-items-center gap-3"><div class="pos-product-image">@if($product->thumbnail)<img src="{{ $product->thumbnail->url }}" alt="{{ $product->name }}">@else<i class="bi bi-image"></i>@endif</div><div class="flex-grow-1"><div class="fw-semibold">{{ $product->name }}</div><div class="small text-muted">{{ $product->sku }}</div><div class="small mt-1 pos-stock text-muted">Select warehouse</div></div><div class="text-end"><div class="fw-bold" style="color:var(--brand)">${{ number_format($product->selling_price,2) }}</div><div class="small text-muted">Click to add</div></div></div></button>
            </div>
            @empty<div class="col-12 text-center py-5 text-muted">Add products first, then they will appear here for POS sales.</div>@endforelse
        </div>
    </div></div>
    <div class="col-xl-4"><div class="panel h-100">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3"><div class="panel-heading">{{ $draft ? $draft->order_number : 'Current sale' }}</div><button id="pos-clear" type="button" class="btn btn-sm text-danger">Clear cart</button></div>
        <div id="pos-cart" class="vstack gap-3"><div class="text-center py-5 text-muted"><i class="bi bi-basket fs-2 d-block mb-2"></i>Cart is empty</div></div>
        <div class="border-top mt-3 pt-3"><div class="row g-2 mb-3"><div class="col-12"><input id="pos-customer-name" class="form-control" value="{{ $draft ? $draft->customer_name : '' }}" placeholder="Customer name (optional)"></div><div class="col-12"><input id="pos-customer-phone" class="form-control" value="{{ $draft ? $draft->customer_phone : '' }}" placeholder="Customer phone (optional)"></div><div class="col-12"><label class="form-label">Payment method</label><select id="pos-payment-method" class="form-select"><option value="Cash">Cash</option><option value="Card">Card</option><option value="Mobile banking">Mobile banking</option><option value="Due">Due</option></select></div></div><div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="pos-subtotal">$0.00</strong></div><div class="d-flex justify-content-between fs-5"><strong>Total</strong><strong id="pos-total" style="color:var(--brand)">$0.00</strong></div><div class="d-grid gap-2 mt-3"><button class="btn btn-outline-primary" id="pos-save-draft" type="button"><i class="bi bi-pause-circle me-1"></i>{{ $draft ? 'Update draft' : 'Hold as draft' }}</button><button class="btn text-white" id="pos-checkout" type="button" style="background:var(--brand)"><i class="bi bi-credit-card me-1"></i>Complete sale</button></div></div>
    </div></div>
</div>
<form id="pos-order-form" method="POST" action="{{ $draft ? route('admin.pos.drafts.update',$draft) : route('admin.pos.orders.save') }}" class="d-none">@csrf @if($draft) @method('PUT') @endif</form>
@if($draft)<div id="pos-draft-data" data-payment="{{ $draft->payment_method ?: 'Cash' }}" data-items='{{ $draft->items->map(function($item) { return ['product_id'=>$item->product_id,'product_name'=>$item->product_name,'quantity'=>(float)$item->quantity,'unit_price'=>(float)$item->unit_price]; })->toJson() }}'></div>@endif
@endsection
