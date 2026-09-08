<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View { return view('admin.variants.brands.index', ['brands' => Brand::with('icon')->latest()->paginate(12)]); }
    public function create(): View { return view('admin.variants.brands.form', ['brand' => new Brand(['is_active' => true])]); }
    public function store(Request $request): RedirectResponse { $request->merge(['is_active' => $request->boolean('is_active')]); Brand::create($request->validate(['name' => ['required','string','max:100','unique:brands,name'], 'icon_media_id' => ['nullable','exists:media,id'], 'is_active' => ['required','boolean']])); return to_route('admin.brands.index')->with('success', 'Brand created successfully.'); }
    public function edit(Brand $brand): View { return view('admin.variants.brands.form', ['brand' => $brand->load('icon')]); }
    public function update(Request $request, Brand $brand): RedirectResponse { $request->merge(['is_active' => $request->boolean('is_active')]); $brand->update($request->validate(['name' => ['required','string','max:100','unique:brands,name,'.$brand->id], 'icon_media_id' => ['nullable','exists:media,id'], 'is_active' => ['required','boolean']])); return to_route('admin.brands.index')->with('success', 'Brand updated successfully.'); }
    public function destroy(Brand $brand): RedirectResponse { $brand->delete(); return to_route('admin.brands.index')->with('success', 'Brand deleted. Its media file was kept in the library.'); }
}
