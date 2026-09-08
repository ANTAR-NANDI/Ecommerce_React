@extends('layouts.admin')
@php($label = ucfirst($type))
@php($routeName = $type.'s')
@php($editing = $item->exists)
@section('title',($editing ? 'Edit ' : 'Add ').$label)
@section('content')
<div class="content-heading mb-4"><h1>{{ $editing ? 'Edit '.$label : 'Add '.$label }}</h1><p>Set the name and storefront visibility.</p></div><form method="POST" action="{{ $editing ? route('admin.'.$routeName.'.update',$item) : route('admin.'.$routeName.'.store') }}">@csrf @if($editing) @method('PUT') @endif<div class="panel" style="max-width:620px"><label class="form-label">{{ $label }} name</label><input name="name" value="{{ old('name',$item->name) }}" class="form-control" required><div class="mt-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active',$item->is_active))> Active</label></div><button class="btn text-white mt-4" style="background:var(--brand)">{{ $editing ? 'Update '.$label : 'Save '.$label }}</button></div></form>
@endsection
