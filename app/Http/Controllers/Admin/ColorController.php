<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ColorController extends Controller
{
    public function index(): View { return view('admin.variants.colors.index', ['colors' => Color::latest()->paginate(12)]); }
    public function create(): View { return view('admin.variants.colors.form', ['color' => new Color(['is_active' => true, 'hex_code' => '#7256E7'])]); }
    public function store(Request $request): RedirectResponse { Color::create($request->validate(['name' => ['required','string','max:100','unique:colors,name'], 'hex_code' => ['required','regex:/^#[A-Fa-f0-9]{6}$/'], 'is_active' => ['required','boolean']])); return to_route('admin.colors.index')->with('success', 'Color created successfully.'); }
    public function edit(Color $color): View { return view('admin.variants.colors.form', compact('color')); }
    public function update(Request $request, Color $color): RedirectResponse { $color->update($request->validate(['name' => ['required','string','max:100','unique:colors,name,'.$color->id], 'hex_code' => ['required','regex:/^#[A-Fa-f0-9]{6}$/'], 'is_active' => ['required','boolean']])); return to_route('admin.colors.index')->with('success', 'Color updated successfully.'); }
    public function destroy(Color $color): RedirectResponse { $color->delete(); return to_route('admin.colors.index')->with('success', 'Color deleted successfully.'); }
}
