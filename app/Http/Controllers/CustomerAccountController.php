<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\CustomerWishlist;
use App\Models\EcommerceOrder;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    private function customer() { return auth('customer')->user(); }

    public function dashboard(): View
    {
        $customer = $this->customer();
        $orders = $customer->orders()->with('items')->latest()->take(5)->get();
        return view('customer.dashboard', [
            'customer' => $customer,
            'orders' => $orders,
            'ongoingOrders' => $customer->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'productsOrdered' => $customer->orders()->with('items')->get()->sum(fn ($order) => $order->items->sum('quantity')),
            'wishlistCount' => $customer->wishlistItems()->count(),
            'address' => $customer->addresses()->where('is_default', true)->first() ?? $customer->addresses()->first(),
        ]);
    }

    public function orders(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', 'in:pending,accepted,processing,shipped,delivered,cancelled']])['status'] ?? null;
        $customer = $this->customer();
        return view('customer.orders', ['customer' => $customer, 'orders' => $customer->orders()->with('items')->when($status, fn ($query) => $query->where('status', $status))->latest()->paginate(12)->withQueryString(), 'selectedStatus' => $status, 'statusCounts' => collect(EcommerceOrder::STATUSES)->mapWithKeys(fn ($item) => [$item => $customer->orders()->where('status', $item)->count()])]);
    }
    public function order(EcommerceOrder $order): View { abort_unless($order->customer_id === $this->customer()->id, 404); return view('customer.order-detail', ['customer' => $this->customer(), 'order' => $order->load(['items.product.thumbnail', 'statusHistory'])]); }
    public function wishlist(): View { return view('customer.wishlist', ['customer' => $this->customer(), 'items' => $this->customer()->wishlistItems()->with(['product.thumbnail'])->latest()->paginate(12)]); }
    public function profile(): View { return view('customer.profile', ['customer' => $this->customer()]); }
    public function changePassword(): View { return view('customer.change-password', ['customer' => $this->customer()]); }
    public function addresses(): View { return view('customer.addresses', ['customer' => $this->customer(), 'addresses' => $this->customer()->addresses()->latest()->get()]); }

    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = $this->customer();
        $data = $request->validate(['first_name' => ['required', 'string', 'max:100'], 'last_name' => ['nullable', 'string', 'max:100'], 'phone' => ['required', 'string', 'max:30', 'unique:customers,phone,'.$customer->id], 'email' => ['nullable', 'email', 'max:255', 'unique:customers,email,'.$customer->id], 'date_of_birth' => ['nullable', 'date'], 'gender' => ['nullable', 'in:male,female,other']]);
        $customer->update($data);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate(['current_password' => ['required'], 'password' => ['required', 'min:8', 'confirmed']]);
        $customer = $this->customer();
        if (! Hash::check($data['current_password'], $customer->password)) return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        $customer->update(['password' => $data['password']]);
        return back()->with('success', 'Password changed successfully.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $this->addressData($request);
        $customer = $this->customer();
        if ($data['is_default'] || ! $customer->addresses()->exists()) $customer->addresses()->update(['is_default' => false]);
        $customer->addresses()->create($data);
        return back()->with('success', 'Address saved successfully.');
    }

    public function updateAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->customer_id === $this->customer()->id, 404);
        $data = $this->addressData($request);
        if ($data['is_default']) $this->customer()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        $address->update($data);
        return back()->with('success', 'Address updated successfully.');
    }

    public function deleteAddress(CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->customer_id === $this->customer()->id, 404);
        $address->delete();
        return back()->with('success', 'Address removed.');
    }

    public function wishlistData(): JsonResponse { return response()->json($this->customer()->wishlistItems()->with('product.thumbnail')->get()->map(fn ($item) => $this->wishlistPayload($item->product))); }
    public function addWishlist(Product $product): JsonResponse { CustomerWishlist::firstOrCreate(['customer_id' => $this->customer()->id, 'product_id' => $product->id]); return response()->json(['message' => 'Added to wishlist.']); }
    public function removeWishlist(Product $product): JsonResponse { $this->customer()->wishlistItems()->where('product_id', $product->id)->delete(); return response()->json(['message' => 'Removed from wishlist.']); }

    private function addressData(Request $request): array { $request->merge(['is_default' => $request->boolean('is_default')]); return $request->validate(['label' => ['required', 'in:home,office,other'], 'name' => ['required', 'string', 'max:150'], 'phone' => ['required', 'string', 'max:30'], 'address' => ['required', 'string', 'max:500'], 'area' => ['nullable', 'string', 'max:150'], 'is_default' => ['boolean']]); }
    private function wishlistPayload(Product $product): array { return ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'image_url' => $product->thumbnail?->url, 'price' => (float) $product->selling_price, 'stock' => $product->stock_quantity]; }
}
