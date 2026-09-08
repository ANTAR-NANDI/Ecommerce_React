<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View { return view('admin.variants.simples.index', ['items' => Unit::latest()->paginate(12), 'type' => 'unit']); }
    public function create(): View { return view('admin.variants.simples.form', ['item' => new Unit(['is_active' => true]), 'type' => 'unit']); }
    public function store(Request $request): RedirectResponse { Unit::create($request->validate(['name' => ['required','string','max:100','unique:units,name'], 'is_active' => ['required','boolean']])); return to_route('admin.units.index')->with('success', 'Unit created successfully.'); }
    public function edit(Unit $unit): View { return view('admin.variants.simples.form', ['item' => $unit, 'type' => 'unit']); }
    public function update(Request $request, Unit $unit): RedirectResponse { $unit->update($request->validate(['name' => ['required','string','max:100','unique:units,name,'.$unit->id], 'is_active' => ['required','boolean']])); return to_route('admin.units.index')->with('success', 'Unit updated successfully.'); }
    public function destroy(Unit $unit): RedirectResponse { $unit->delete(); return to_route('admin.units.index')->with('success', 'Unit deleted successfully.'); }
}
