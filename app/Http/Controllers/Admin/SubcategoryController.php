<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubcategoryController extends Controller
{
    public function index(): View { return view('admin.subcategories.index', ['subcategories' => Subcategory::with(['categories', 'icon'])->latest()->paginate(12)]); }
    public function create(): View { return view('admin.subcategories.create', ['categories' => Category::where('is_active', true)->orderBy('name')->get(), 'media' => Media::latest()->get()]); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required','string','max:100','unique:subcategories,name'], 'categories' => ['required','array','min:1'], 'categories.*' => ['exists:categories,id'], 'short_description' => ['nullable','string','max:255'], 'icon_media_id' => ['nullable','exists:media,id']]);
        $subcategory = Subcategory::create(['name' => $data['name'], 'slug' => Str::slug($data['name']), 'short_description' => $data['short_description'] ?? null, 'icon_media_id' => $data['icon_media_id'] ?? null]);
        $subcategory->categories()->sync($data['categories']);
        return to_route('admin.subcategories.index')->with('success', 'Subcategory created successfully.');
    }
}
