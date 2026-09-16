<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = ProductReview::updateOrCreate(
            ['product_id' => $product->id, 'customer_id' => $request->user('customer')->id],
            $data,
        )->load('customer');

        return response()->json([
            'message' => 'Your review has been saved.',
            'review' => $this->reviewPayload($review),
            'rating' => round((float) $product->reviews()->avg('rating'), 1),
            'reviews_count' => $product->reviews()->count(),
        ]);
    }

    private function reviewPayload(ProductReview $review): array
    {
        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'customer_name' => $review->customer->full_name,
            'created_at' => $review->updated_at->toDateString(),
        ];
    }
}
