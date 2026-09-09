<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function create(): View { return view('customer.login'); }
    public function register(): View { return view('customer.register'); }

    public function storeRegistration(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);
        $customer = Customer::create($data + ['is_active' => true]);
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        return to_route('customer.dashboard')->with('success', 'Welcome to VeloraCommerce — your account is ready.');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        $credentials['is_active'] = true;
        if (! Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These customer credentials do not match our records.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return to_route('customer.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return to_route('storefront');
    }
}
