<?php

namespace App\Http\Controllers;

use App\Models\EcommerceOrder;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicCheckoutController extends Controller
{
    public function coupon(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:50'], 'subtotal' => ['required', 'numeric', 'min:0']]);
        $promotion = $this->couponFor($data['code']);
        if (! $promotion || ($promotion->minimum_order_amount && $data['subtotal'] < $promotion->minimum_order_amount) || ($promotion->usage_limit && $promotion->used_count >= $promotion->usage_limit)) return response()->json(['message' => 'This coupon is not available for the current order.'], 422);
        $discount = $promotion->discount_type === 'percent' ? $data['subtotal'] * ((float) $promotion->discount_value / 100) : min($data['subtotal'], (float) $promotion->discount_value);
        return response()->json(['code' => $promotion->code, 'title' => $promotion->title, 'discount' => round($discount, 2)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:255'], 'phone' => ['required', 'string', 'max:30'], 'address' => ['required', 'string', 'max:1000'], 'area' => ['nullable', 'string', 'max:150'], 'address_tag' => ['nullable', 'in:home,office,other'], 'note' => ['nullable', 'string', 'max:1000'], 'payment_method' => ['required', 'in:cash_on_delivery,card'], 'coupon_code' => ['nullable', 'string', 'max:50'], 'items' => ['required', 'array', 'min:1'], 'items.*.id' => ['required', 'integer', 'exists:products,id'], 'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $products = Product::whereIn('id', collect($data['items'])->pluck('id'))->where('is_active', true)->get()->keyBy('id');
        abort_unless($products->count() === count($data['items']), 422, 'One or more products are no longer available.');
        $items = collect($data['items'])->map(function ($item) use ($products) { $product = $products[$item['id']]; $price = (float) $product->selling_price; if ($product->discount > 0) $price = $product->discount_type === 'percent' ? $price * (1 - $product->discount / 100) : max(0, $price - $product->discount); return ['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $item['quantity'], 'unit_price' => round($price, 2), 'line_total' => round($price * $item['quantity'], 2)]; });
        $subtotal = (float) $items->sum('line_total'); $promotion = $data['coupon_code'] ? $this->couponFor($data['coupon_code']) : null;
        $discount = $promotion && (! $promotion->minimum_order_amount || $subtotal >= $promotion->minimum_order_amount) && (! $promotion->usage_limit || $promotion->used_count < $promotion->usage_limit) ? ($promotion->discount_type === 'percent' ? $subtotal * ((float) $promotion->discount_value / 100) : min($subtotal, (float) $promotion->discount_value)) : 0;
        $order = DB::transaction(function () use ($data, $items, $subtotal, $discount, $promotion) { $customer = auth('customer')->user(); $order = EcommerceOrder::create(['order_number' => 'EC-'.now()->format('ymdHis').'-'.random_int(100, 999), 'customer_id' => $customer?->id, 'customer_name' => $data['name'], 'customer_phone' => $data['phone'], 'customer_email' => $data['email'], 'shipping_address' => trim(($data['address_tag'] ? ucfirst($data['address_tag']).': ' : '').$data['address'].($data['area'] ? ', '.$data['area'] : '')), 'warehouse_id' => Warehouse::where('is_active', true)->value('id'), 'payment_method' => $data['payment_method'] === 'card' ? 'Credit / Debit Card' : 'Cash on delivery', 'subtotal' => $subtotal, 'discount' => round($discount, 2), 'total' => round($subtotal - $discount, 2), 'customer_note' => $data['note']]); $order->items()->createMany($items->all()); if ($promotion) $promotion->increment('used_count'); return $order; });
        return response()->json(['message' => 'Your order has been placed.', 'order_number' => $order->order_number]);
    }
    private function couponFor(string $code): ?Promotion { return Promotion::where('type', 'promo_code')->where('code', mb_strtoupper(trim($code)))->where('is_active', true)->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))->first(); }
}
