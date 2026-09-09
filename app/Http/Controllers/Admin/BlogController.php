<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View { $search=$request->string('search')->toString();return view('admin.blogs.index',['blogs'=>Blog::with(['category','thumbnail'])->when($search,fn($query)=>$query->where('title','like','%'.$search.'%'))->latest()->paginate(12)->withQueryString(),'search'=>$search]); }
    public function create(): View { return $this->form(new Blog(['status'=>'draft'])); }
    public function store(Request $request): RedirectResponse { $data=$this->validated($request);$data['slug']=$this->uniqueSlug($data['title']);$data=$this->prepare($data);Blog::create($data);return to_route('admin.blogs.index')->with('success','Blog post created successfully.'); }
    public function edit(Blog $blog): View { return $this->form($blog); }
    public function update(Request $request, Blog $blog): RedirectResponse { $data=$this->validated($request);if($blog->title!==$data['title'])$data['slug']=$this->uniqueSlug($data['title'],$blog->id);$blog->update($this->prepare($data));return to_route('admin.blogs.index')->with('success','Blog post updated successfully.'); }
    public function destroy(Blog $blog): RedirectResponse { $blog->delete();return to_route('admin.blogs.index')->with('success','Blog post deleted successfully.'); }
    private function form(Blog $blog): View { return view('admin.blogs.form',['blog'=>$blog,'categories'=>Category::where('is_active',true)->orderBy('name')->get()]); }
    private function validated(Request $request): array { return $request->validate(['title'=>'required|max:200','category_id'=>'nullable|exists:categories,id','tags'=>'nullable|array|max:20','tags.*'=>'string|max:50','description'=>'required','thumbnail_media_id'=>'nullable|exists:media,id','status'=>'required|in:draft,published','meta_title'=>'nullable|max:255','meta_description'=>'nullable|max:1000']); }
    private function prepare(array $data): array { $data['tags']=collect($data['tags']??[])->map(fn($tag)=>trim($tag))->filter()->unique(fn($tag)=>mb_strtolower($tag))->values()->all();$data['published_at']=$data['status']==='published' ? now() : null;return $data; }
    private function uniqueSlug(string $title, ?int $ignore=null): string { $base=Str::slug($title) ?: 'post';$slug=$base;$number=2;while(Blog::where('slug',$slug)->when($ignore,fn($query)=>$query->whereKeyNot($ignore))->exists())$slug=$base.'-'.$number++;return $slug; }
}
