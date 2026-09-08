<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View { return view('admin.warehouses.index',['warehouses'=>Warehouse::latest()->paginate(12)]); }
    public function create(): View { return view('admin.warehouses.form',['warehouse'=>new Warehouse(['is_active'=>true])]); }
    public function store(Request $request): RedirectResponse { $request->merge(['is_active'=>$request->boolean('is_active')]); Warehouse::create($request->validate(['name'=>'required|max:150','code'=>'required|max:30|unique:warehouses,code','manager_name'=>'nullable|max:150','phone'=>'nullable|max:30','email'=>'nullable|email','address'=>'nullable','is_active'=>'boolean'])); return to_route('admin.warehouses.index')->with('success','Warehouse created successfully.'); }
    public function edit(Warehouse $warehouse): View { return view('admin.warehouses.form',compact('warehouse')); }
    public function update(Request $request, Warehouse $warehouse): RedirectResponse { $request->merge(['is_active'=>$request->boolean('is_active')]); $warehouse->update($request->validate(['name'=>'required|max:150','code'=>'required|max:30|unique:warehouses,code,'.$warehouse->id,'manager_name'=>'nullable|max:150','phone'=>'nullable|max:30','email'=>'nullable|email','address'=>'nullable','is_active'=>'boolean'])); return to_route('admin.warehouses.index')->with('success','Warehouse updated successfully.'); }
    public function destroy(Warehouse $warehouse): RedirectResponse { $warehouse->delete(); return to_route('admin.warehouses.index')->with('success','Warehouse deleted successfully.'); }
}
