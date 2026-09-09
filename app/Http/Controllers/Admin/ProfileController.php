<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View { return view('admin.profile.edit', ['user' => auth()->user()]); }
    public function update(Request $request): RedirectResponse { $user = $request->user(); $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id], 'password' => ['nullable', 'string', 'min:8', 'confirmed']]); if (! empty($data['password'])) $data['password'] = Hash::make($data['password']); else unset($data['password']); $user->update($data); return to_route('admin.profile.edit')->with('success', 'Profile updated successfully.'); }
}
