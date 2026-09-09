@extends('layouts.admin')
@php($editing = $promotion->exists)
@section('title', ($editing ? 'Edit ' : 'Add ').$config['label'])
@section('content')
<div class="content-heading mb-4">
    <h1><i class="bi {{ $config['icon'] }} me-2" style="color:var(--brand)"></i>{{ $editing ? 'Edit' : 'Add' }} {{ $config['label'] }}</h1>
    <p>Configure the content, schedule, and storefront behavior.</p>
</div>

<form method="POST" action="{{ $editing ? route('admin.promotions.update', [$key, $promotion]) : route('admin.promotions.store', $key) }}">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="panel">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">Title <span class="text-danger">*</span></label><input name="title" value="{{ old('title', $promotion->title) }}" class="form-control @error('title') is-invalid @enderror" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>

                    @if($key === 'promo-codes')
                        <div class="col-md-6"><label class="form-label">Promo code <span class="text-danger">*</span></label><input name="code" value="{{ old('code', $promotion->code) }}" class="form-control text-uppercase @error('code') is-invalid @enderror" placeholder="SAVE20" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    @endif

                    @if($key === 'flash-deals')
                        <div class="col-12"><label class="form-label">Products <span class="text-danger">*</span></label><select name="product_ids[]" class="form-select js-product-select @error('product_ids') is-invalid @enderror" multiple>@foreach($products as $product)<option value="{{ $product->id }}" @selected(in_array($product->id, old('product_ids', $promotion->product_ids ?? [])))>{{ $product->name }}{{ $product->sku ? ' · '.$product->sku : '' }}</option>@endforeach</select>@error('product_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    @endif

                    @if(in_array($key, ['flash-deals', 'promo-codes']))
                        <div class="col-md-6"><label class="form-label">Discount type <span class="text-danger">*</span></label><select name="discount_type" class="form-select" required><option value="percent" @selected(old('discount_type', $promotion->discount_type) === 'percent')>Percentage</option><option value="amount" @selected(old('discount_type', $promotion->discount_type) === 'amount')>Fixed amount</option></select></div>
                        <div class="col-md-6"><label class="form-label">Discount value <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" name="discount_value" value="{{ old('discount_value', $promotion->discount_value) }}" class="form-control @error('discount_value') is-invalid @enderror" required>@error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    @endif

                    @if($key === 'promo-codes')
                        <div class="col-md-6"><label class="form-label">Minimum order amount</label><input type="number" step="0.01" min="0" name="minimum_order_amount" value="{{ old('minimum_order_amount', $promotion->minimum_order_amount) }}" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Usage limit</label><input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $promotion->usage_limit) }}" class="form-control" placeholder="Leave empty for unlimited"></div>
                    @endif

                    @if(in_array($key, ['banners', 'ads-campaigns']))
                        <div class="col-md-6"><label class="form-label">Placement <span class="text-danger">*</span></label><select name="placement" class="form-select" required>@foreach(['home_hero'=>'Home hero','home_middle'=>'Home middle','sidebar'=>'Sidebar','checkout'=>'Checkout'] as $value=>$label)<option value="{{ $value }}" @selected(old('placement', $promotion->placement) === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label">Destination URL</label><input type="url" name="link_url" value="{{ old('link_url', $promotion->link_url) }}" class="form-control @error('link_url') is-invalid @enderror" placeholder="https://example.com/collection">@error('link_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    @endif

                    @if($key === 'ads-campaigns')
                        <div class="col-md-6"><label class="form-label">Campaign budget</label><input type="number" step="0.01" min="0" name="budget" value="{{ old('budget', $promotion->budget) }}" class="form-control"></div>
                    @endif

                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $promotion->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">Starts at</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at', $promotion->starts_at?->format('Y-m-d\TH:i')) }}" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Ends at</label><input type="datetime-local" name="ends_at" value="{{ old('ends_at', $promotion->ends_at?->format('Y-m-d\TH:i')) }}" class="form-control @error('ends_at') is-invalid @enderror">@error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            @if(in_array($key, ['banners', 'ads-campaigns']))
                <div class="panel mb-4">
                    <div class="panel-heading mb-2">Campaign image <span class="text-danger">*</span></div>
                    <p class="small text-muted">Choose a wide, optimized image from the Media Library.</p>
                    <div class="media-preview promotion-media-preview mb-3" data-media-preview="promotion-banner">@if($promotion->banner)<img src="{{ $promotion->banner->url }}" alt="{{ $promotion->title }}">@else<i class="bi bi-image fs-1"></i>@endif</div>
                    <input type="hidden" id="promotion-banner" name="banner_media_id" value="{{ old('banner_media_id', $promotion->banner_media_id) }}">
                    <button type="button" class="btn btn-outline-primary w-100" data-media-picker-target="promotion-banner"><i class="bi bi-images me-1"></i>Choose image</button>
                    @error('banner_media_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            @endif
            <div class="panel">
                <div class="panel-heading mb-3">Availability</div>
                <div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" role="switch" id="promotion-status" name="is_active" value="1" @checked(old('is_active', $promotion->is_active))><label class="form-check-label" for="promotion-status">Active</label></div>
                <p class="small text-muted mb-0 mt-2">Inactive promotions remain saved but are hidden from the storefront.</p>
            </div>
        </div>
    </div>
    <div class="mt-4"><a href="{{ route('admin.promotions.index', $key) }}" class="btn btn-light">Cancel</a><button class="btn text-white ms-2" style="background:var(--brand)">{{ $editing ? 'Update' : 'Save' }} {{ strtolower($config['label']) }}</button></div>
</form>
@endsection
