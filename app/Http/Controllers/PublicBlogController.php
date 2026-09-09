<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\JsonResponse;

class PublicBlogController extends Controller
{
    public function index(): JsonResponse
    {
        $posts=Blog::with(['category','thumbnail'])->where('status','published')->whereNotNull('published_at')->latest('published_at')->paginate(12);
        $posts->through(fn(Blog $blog)=>$this->payload($blog));
        return response()->json($posts);
    }
    public function show(string $slug): JsonResponse { $blog=Blog::with(['category','thumbnail'])->where('status','published')->where('slug',$slug)->firstOrFail();return response()->json($this->payload($blog)); }
    private function payload(Blog $blog): array { return ['id'=>$blog->id,'title'=>$blog->title,'slug'=>$blog->slug,'category'=>$blog->category?->name,'tags'=>$blog->tags ?? [],'description'=>$blog->description,'thumbnail_url'=>$blog->thumbnail?->url,'meta_title'=>$blog->meta_title,'meta_description'=>$blog->meta_description,'published_at'=>$blog->published_at]; }
}
