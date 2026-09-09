@extends('customer.layout')
@section('title','Change Password')
@section('content')
<section class="customer-card customer-form change-password-card"><h2>Change Password</h2><form method="POST" action="{{ route('customer.password.update') }}">@csrf @method('PUT')<label class="form-label">Current Password</label><input class="form-control mb-3" type="password" name="current_password" placeholder="Enter current password" required><label class="form-label">Create New Password</label><input class="form-control mb-3" type="password" name="password" placeholder="Enter new password" required><label class="form-label">Confirm New Password</label><input class="form-control" type="password" name="password_confirmation" placeholder="Confirm new password" required><button class="customer-action primary mt-3">Update Password</button></form></section>
@endsection
