<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use Illuminate\Http\JsonResponse;

class PublicCmsPageController extends Controller
{
    public function data(string $slug): JsonResponse
    {
        $page = CmsPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return response()->json($page->only(['title', 'slug', 'meta_title', 'meta_description', 'content']));
    }
}
