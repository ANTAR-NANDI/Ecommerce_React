<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View { return view('admin.categories.index', ['categories' => Category::with(['icon', 'banner'])->orderBy('display_order')->paginate(12)]); }
    public function create(): View { return view('admin.categories.create', ['media' => Media::latest()->get()]); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required','string','max:100','unique:categories,name'], 'display_order' => ['required','integer','min:0'], 'icon_media_id' => ['nullable','exists:media,id'], 'banner_media_id' => ['nullable','exists:media,id'], 'is_active' => ['required','boolean']]);
        $data['slug'] = Str::slug($data['name']);
        Category::create($data);
        return to_route('admin.categories.index')->with('success', 'Category created successfully.');
    }
}
