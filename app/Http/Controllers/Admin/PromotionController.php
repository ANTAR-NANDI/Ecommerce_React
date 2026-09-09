<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromotionController extends Controller
{
    private const TYPES = [
        'flash-deals' => ['value' => 'flash_deal', 'label' => 'Flash Deals', 'icon' => 'bi-lightning-charge-fill'],
        'banners' => ['value' => 'banner', 'label' => 'Banner Setup', 'icon' => 'bi-image-fill'],
        'ads-campaigns' => ['value' => 'ads_campaign', 'label' => 'Ads Campaigns', 'icon' => 'bi-megaphone-fill'],
        'promo-codes' => ['value' => 'promo_code', 'label' => 'Promo Codes', 'icon' => 'bi-ticket-perforated-fill'],
    ];

    public function index(string $type): View
    {
        $config = $this->config($type);

        return view('admin.promotions.index', [
            'key' => $type,
            'config' => $config,
            'promotions' => Promotion::with('banner')->where('type', $config['value'])->latest()->paginate(12),
        ]);
    }

    public function create(string $type): View
    {
        return $this->form($type, new Promotion(['is_active' => true]));
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $config = $this->config($type);
        $data = $this->validated($request, $type);
        $data['type'] = $config['value'];
        Promotion::create($this->prepare($data));

        return to_route('admin.promotions.index', $type)->with('success', $config['label'].' entry created successfully.');
    }

    public function edit(string $type, Promotion $promotion): View
    {
        $this->ensureCorrectType($type, $promotion);

        return $this->form($type, $promotion);
    }

    public function update(Request $request, string $type, Promotion $promotion): RedirectResponse
    {
        $config = $this->config($type);
        $this->ensureCorrectType($type, $promotion);
        $promotion->update($this->prepare($this->validated($request, $type, $promotion)));

        return to_route('admin.promotions.index', $type)->with('success', $config['label'].' entry updated successfully.');
    }

    public function destroy(string $type, Promotion $promotion): RedirectResponse
    {
        $config = $this->config($type);
        $this->ensureCorrectType($type, $promotion);
        $promotion->delete();

        return to_route('admin.promotions.index', $type)->with('success', $config['label'].' entry deleted successfully.');
    }

    private function form(string $type, Promotion $promotion): View
    {
        return view('admin.promotions.form', [
            'key' => $type,
            'config' => $this->config($type),
            'promotion' => $promotion,
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    private function validated(Request $request, string $type, ?Promotion $promotion = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'banner_media_id' => ['nullable', 'exists:media,id'],
            'product_ids' => ['nullable', 'array', 'max:100'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'placement' => ['nullable', Rule::in(['home_hero', 'home_middle', 'sidebar', 'checkout'])],
            'link_url' => ['nullable', 'url', 'max:255'],
            'discount_type' => ['nullable', Rule::in(['percent', 'amount'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['required', 'boolean'],
        ];

        if ($type === 'flash-deals') {
            $rules['product_ids'] = ['required', 'array', 'min:1', 'max:100'];
            $rules['discount_type'] = ['required', Rule::in(['percent', 'amount'])];
            $rules['discount_value'] = ['required', 'numeric', 'min:0.01'];
        }

        if (in_array($type, ['banners', 'ads-campaigns'], true)) {
            $rules['banner_media_id'] = ['required', 'exists:media,id'];
            $rules['placement'] = ['required', Rule::in(['home_hero', 'home_middle', 'sidebar', 'checkout'])];
        }

        if ($type === 'promo-codes') {
            $rules['code'] = ['required', 'string', 'max:50', Rule::unique('promotions', 'code')->ignore($promotion)];
            $rules['discount_type'] = ['required', Rule::in(['percent', 'amount'])];
            $rules['discount_value'] = ['required', 'numeric', 'min:0.01'];
        }

        return $request->validate($rules);
    }

    private function prepare(array $data): array
    {
        $data['is_active'] = (bool) $data['is_active'];
        $data['code'] = isset($data['code']) ? mb_strtoupper(trim($data['code'])) : null;
        $data['product_ids'] = isset($data['product_ids']) ? array_values(array_unique($data['product_ids'])) : null;

        return $data;
    }

    private function config(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }

    private function ensureCorrectType(string $type, Promotion $promotion): void
    {
        abort_unless($promotion->type === $this->config($type)['value'], 404);
    }
}
