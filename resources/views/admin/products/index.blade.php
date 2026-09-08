@extends('layouts.admin')
@section('title','Products')
@section('content')
<div class="d-flex justify-content-between align-items-center content-heading mb-4"><div><h1>Products</h1><p>Manage your storefront catalogue.</p></div><a href="{{ route('admin.products.create') }}" class="btn text-white" style="background:var(--brand)"><i class="bi bi-plus-lg me-1"></i>Add product</a></div><div class="panel p-0 overflow-hidden"><table class="table mb-0"><thead><tr><th>Product</th><th>SKU</th><th>Price</th><th>Stock</th></tr></thead><tbody>@forelse($products as $product)<tr><td class="fw-semibold">{{ $product->name }}</td><td>{{ $product->sku }}</td><td>${{ number_format($product->selling_price,2) }}</td><td>{{ $product->stock_quantity }}</td></tr>@empty<tr><td colspan="4" class="text-center py-5">No products yet. Add your first product.</td></tr>@endforelse</tbody></table></div>
@endsection
