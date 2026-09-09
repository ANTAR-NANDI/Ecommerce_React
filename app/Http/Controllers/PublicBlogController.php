<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicBlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query=$request->validate(['search'=>'nullable|string|max:150','category'=>'nullable|string|max:150','sort'=>'nullable|in:newest,oldest']);
        $posts=Blog::with(['category','thumbnail'])->where('status','published')->whereNotNull('published_at')
            ->when($query['search'] ?? null, fn($builder,$search)=>$builder->where(fn($nested)=>$nested->where('title','like',"%{$search}%")->orWhere('description','like',"%{$search}%")))
            ->when($query['category'] ?? null, fn($builder,$category)=>$builder->whereHas('category',fn($nested)=>$nested->where('name',$category)))
            ->when(($query['sort'] ?? 'newest')==='oldest',fn($builder)=>$builder->oldest('published_at'),fn($builder)=>$builder->latest('published_at'))
            ->paginate(9)->withQueryString();
        $posts->through(fn(Blog $blog)=>$this->payload($blog));
        return response()->json(['posts'=>$posts,'categories'=>Category::whereHas('blogs',fn($builder)=>$builder->where('status','published')->whereNotNull('published_at'))->withCount(['blogs'=>fn($builder)=>$builder->where('status','published')->whereNotNull('published_at')])->orderBy('name')->get(['id','name'])]);
    }
    public function show(string $slug): JsonResponse { $blog=Blog::with(['category','thumbnail'])->where('status','published')->where('slug',$slug)->firstOrFail();$related=Blog::with('thumbnail')->where('status','published')->whereKeyNot($blog->id)->when($blog->category_id,fn($query)=>$query->where('category_id',$blog->category_id))->latest('published_at')->take(4)->get()->map(fn(Blog $post)=>$this->payload($post));return response()->json(['post'=>$this->payload($blog),'related'=>$related]); }
    private function payload(Blog $blog): array { return ['id'=>$blog->id,'title'=>$blog->title,'slug'=>$blog->slug,'category'=>$blog->category?->name,'tags'=>$blog->tags ?? [],'description'=>$blog->description,'thumbnail_url'=>$blog->thumbnail?->url,'meta_title'=>$blog->meta_title,'meta_description'=>$blog->meta_description,'published_at'=>$blog->published_at]; }
}
