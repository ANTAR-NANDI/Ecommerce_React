@extends('layouts.admin')
@section('title', $config['label'])
@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="content-heading">
        <h1><i class="bi {{ $config['icon'] }} me-2" style="color:var(--brand)"></i>{{ $config['label'] }}</h1>
        <p>Manage {{ strtolower($config['label']) }} displayed across your ecommerce store.</p>
    </div>
    <a href="{{ route('admin.promotions.create', $key) }}" class="btn text-white" style="background:var(--brand)"><i class="bi bi-plus-lg me-1"></i>Add new</a>
</div>

<div class="panel p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Promotion</th><th>Offer / Placement</th><th>Schedule</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($promotions as $promotion)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($promotion->banner)<img src="{{ $promotion->banner->url }}" alt="" class="promotion-thumb">@else<div class="promotion-thumb"><i class="bi {{ $config['icon'] }}"></i></div>@endif
                            <div><div class="fw-semibold">{{ $promotion->title }}</div>@if($promotion->code)<code>{{ $promotion->code }}</code>@endif</div>
                        </div>
                    </td>
                    <td>
                        @if($promotion->discount_type)<span class="fw-semibold">{{ $promotion->discount_type === 'percent' ? rtrim(rtrim($promotion->discount_value, '0'), '.').'%' : '$'.number_format($promotion->discount_value, 2) }}</span>@endif
                        @if($promotion->placement)<span class="badge rounded-pill text-bg-light">{{ str_replace('_', ' ', ucfirst($promotion->placement)) }}</span>@endif
                        @if($key === 'flash-deals')<div class="small text-muted">{{ count($promotion->product_ids ?? []) }} product(s)</div>@endif
                        @if($key === 'ads-campaigns' && $promotion->budget)<div class="small text-muted">Budget ${{ number_format($promotion->budget, 2) }}</div>@endif
                    </td>
                    <td><div class="small">{{ $promotion->starts_at?->format('d M Y, h:i A') ?? 'Immediately' }}</div><div class="small text-muted">to {{ $promotion->ends_at?->format('d M Y, h:i A') ?? 'No end date' }}</div></td>
                    <td><span class="order-status {{ $promotion->is_active ? 'delivered' : 'cancelled' }}">{{ $promotion->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><div class="d-flex justify-content-end gap-2"><a href="{{ route('admin.promotions.edit', [$key, $promotion]) }}" class="action-button edit"><i class="bi bi-pencil-square me-1"></i>Edit</a><form method="POST" action="{{ route('admin.promotions.destroy', [$key, $promotion]) }}" onsubmit="return confirm('Delete this promotion?')">@csrf @method('DELETE')<button class="action-button delete"><i class="bi bi-trash3 me-1"></i>Delete</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="text-center py-5 text-muted"><i class="bi {{ $config['icon'] }} fs-1 d-block mb-2"></i>No {{ strtolower($config['label']) }} created yet.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($promotions->hasPages())<div class="p-3 border-top">{{ $promotions->links() }}</div>@endif
</div>
@endsection
