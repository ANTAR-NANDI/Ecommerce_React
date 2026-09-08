@extends('layouts.admin')
@php($label = ucfirst($type))
@php($routeName = $type.'s')
@section('title',$label.'s')
@section('content')
<div class="d-flex justify-content-between align-items-center content-heading mb-4"><div><h1>{{ $label }}s</h1><p>Manage product {{ strtolower($label) }} options.</p></div><a href="{{ route('admin.'.$routeName.'.create') }}" class="btn text-white" style="background:var(--brand)">Add {{ strtolower($label) }}</a></div><div class="panel p-0 overflow-hidden"><table class="table mb-0"><thead><tr><th>{{ $label }}</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody>@forelse($items as $item)<tr><td class="fw-semibold">{{ $item->name }}</td><td><span class="badge {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span></td><td class="text-end"><a class="action-button edit" href="{{ route('admin.'.$routeName.'.edit',$item) }}">Edit</a><form class="d-inline" method="POST" action="{{ route('admin.'.$routeName.'.destroy',$item) }}" onsubmit="return confirm('Delete this {{ $type }}?')">@csrf @method('DELETE')<button class="action-button delete">Delete</button></form></td></tr>@empty<tr><td colspan="3" class="text-center py-5">No {{ $routeName }} yet.</td></tr>@endforelse</tbody></table></div>
@endsection
