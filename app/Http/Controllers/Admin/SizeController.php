<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SizeController extends Controller
{
    public function index(): View { return view('admin.variants.simples.index', ['items' => Size::latest()->paginate(12), 'type' => 'size']); }
    public function create(): View { return view('admin.variants.simples.form', ['item' => new Size(['is_active' => true]), 'type' => 'size']); }
    public function store(Request $request): RedirectResponse { Size::create($request->validate(['name' => ['required','string','max:100','unique:sizes,name'], 'is_active' => ['required','boolean']])); return to_route('admin.sizes.index')->with('success', 'Size created successfully.'); }
    public function edit(Size $size): View { return view('admin.variants.simples.form', ['item' => $size, 'type' => 'size']); }
    public function update(Request $request, Size $size): RedirectResponse { $size->update($request->validate(['name' => ['required','string','max:100','unique:sizes,name,'.$size->id], 'is_active' => ['required','boolean']])); return to_route('admin.sizes.index')->with('success', 'Size updated successfully.'); }
    public function destroy(Size $size): RedirectResponse { $size->delete(); return to_route('admin.sizes.index')->with('success', 'Size deleted successfully.'); }
}
