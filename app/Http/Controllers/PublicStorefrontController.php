<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\ContactSetting;
use App\Models\CmsFooterSetting;
use App\Models\CmsMenu;
use App\Models\Media;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicStorefrontController extends Controller
{
    public function data(): JsonResponse
    {
        $now = now();
        $categories = Category::with(['icon', 'banner'])
            ->where('is_active', true)->orderBy('display_order')->get();
        $products = Product::with(['thumbnail', 'category', 'brand', 'color', 'size'])
            ->where('is_active', true)->latest()->take(24)->get();
        $promotions = Promotion::with('banner')->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->latest()->get();
        $flashProductIds = $promotions->where('type', 'flash_deal')->flatMap(fn (Promotion $promotion) => $promotion->product_ids ?? [])->unique()->values();
        $flashProducts = $products->whereIn('id', $flashProductIds)->values();

        return response()->json([
            'categories' => $categories->map(fn (Category $category) => [
                'id' => $category->id, 'name' => $category->name, 'slug' => $category->slug,
                'icon_url' => $category->icon?->url, 'banner_url' => $category->banner?->url,
            ]),
            'products' => $products->map(fn (Product $product) => $this->productPayload($product)),
            'flash_products' => $flashProducts->map(fn (Product $product) => $this->productPayload($product)),
            'banners' => $promotions->whereIn('type', ['banner', 'ads_campaign'])->map(fn (Promotion $promotion) => [
                'id' => $promotion->id, 'type' => $promotion->type, 'title' => $promotion->title,
                'description' => $promotion->description, 'image_url' => $promotion->banner?->url,
                'link_url' => $promotion->link_url, 'placement' => $promotion->placement,
            ])->values(),
            'flash_deal' => $promotions->firstWhere('type', 'flash_deal') ? [
                'title' => $promotions->firstWhere('type', 'flash_deal')->title,
                'description' => $promotions->firstWhere('type', 'flash_deal')->description,
                'ends_at' => $promotions->firstWhere('type', 'flash_deal')->ends_at,
            ] : null,
            'blogs' => Blog::with('thumbnail')->where('status', 'published')->whereNotNull('published_at')->latest('published_at')->take(3)->get()->map(fn (Blog $blog) => [
                'title' => $blog->title, 'slug' => $blog->slug, 'thumbnail_url' => $blog->thumbnail?->url,
                'published_at' => $blog->published_at,
            ]),
            'contact' => ContactSetting::firstOrCreate(['id' => 1]),
            'menus' => CmsMenu::where('is_active', true)->orderBy('sort_order')->get()->groupBy('location'),
            'footer' => CmsFooterSetting::firstOrCreate(['id' => 1]),
        ]);
    }

    public function catalog(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'categories' => ['nullable', 'array'], 'categories.*' => ['integer'],
            'brands' => ['nullable', 'array'], 'brands.*' => ['integer'],
            'colors' => ['nullable', 'array'], 'colors.*' => ['integer'],
            'sizes' => ['nullable', 'array'], 'sizes.*' => ['integer'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,name_asc'],
        ]);
        $products = Product::with(['thumbnail', 'category', 'brand', 'color', 'size'])->where('is_active', true)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")))
            ->when($filters['min_price'] ?? null, fn ($query, $price) => $query->where('selling_price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn ($query, $price) => $query->where('selling_price', '<=', $price))
            ->when($filters['categories'] ?? null, fn ($query, $ids) => $query->whereIn('category_id', $ids))
            ->when($filters['brands'] ?? null, fn ($query, $ids) => $query->whereIn('brand_id', $ids))
            ->when($filters['colors'] ?? null, fn ($query, $ids) => $query->whereIn('color_id', $ids))
            ->when($filters['sizes'] ?? null, fn ($query, $ids) => $query->whereIn('size_id', $ids))
            ->when($request->boolean('in_stock'), fn ($query) => $query->where('stock_quantity', '>', 0));

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $products->orderBy('selling_price'),
            'price_desc' => $products->orderByDesc('selling_price'),
            'name_asc' => $products->orderBy('name'),
            default => $products->latest(),
        };

        $page = $products->paginate(16)->withQueryString();
        $page->through(fn (Product $product) => $this->productPayload($product));

        return response()->json([
            'products' => $page,
            'filters' => [
                'categories' => Category::where('is_active', true)->orderBy('display_order')->get(['id', 'name']),
                'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'colors' => Color::where('is_active', true)->orderBy('name')->get(['id', 'name', 'hex_code']),
                'sizes' => Size::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    public function brands(): JsonResponse
    {
        return response()->json(Brand::with('icon')
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)->orderBy('name')->get()
            ->map(fn (Brand $brand) => [
                'id' => $brand->id, 'name' => $brand->name, 'icon_url' => $brand->icon?->url,
                'products_count' => $brand->products_count,
            ]));
    }

    public function product(string $slug): JsonResponse
    {
        $product = Product::with(['thumbnail', 'category', 'brand', 'color', 'size', 'unit'])
            ->where('is_active', true)->where('slug', $slug)->firstOrFail();
        $mediaById = Media::whereIn('id', $product->gallery_media_ids ?? [])->get()->keyBy('id');
        $gallery = collect($product->gallery_media_ids ?? [])->map(fn ($id) => $mediaById->get($id))
            ->filter()->prepend($product->thumbnail)->filter()->unique('id')->values();

        return response()->json([
            'product' => array_merge($this->productPayload($product), [
                'description' => $product->description,
                'brand' => $product->brand?->name,
                'size' => $product->size?->name,
                'color' => $product->color?->name,
                'unit' => $product->unit?->name,
                'gallery' => $gallery->map(fn (Media $media) => ['id' => $media->id, 'url' => $media->url]),
            ]),
        ]);
    }

    private function productPayload(Product $product): array
    {
        $salePrice = (float) $product->selling_price;
        $discount = (float) $product->discount;
        $originalPrice = $salePrice;
        if ($discount > 0) $salePrice = $product->discount_type === 'percent' ? $salePrice * (1 - ($discount / 100)) : max(0, $salePrice - $discount);

        return [
            'id' => $product->id, 'name' => $product->name, 'slug' => $product->slug,
            'short_description' => $product->short_description, 'image_url' => $product->thumbnail?->url,
            'category' => $product->category?->name, 'brand' => $product->brand?->name,
            'color' => $product->color?->name, 'size' => $product->size?->name, 'price' => round($salePrice, 2),
            'original_price' => $discount > 0 ? $originalPrice : null, 'discount' => $discount,
            'stock' => $product->stock_quantity,
        ];
    }
}
