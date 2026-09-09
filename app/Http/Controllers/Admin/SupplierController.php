<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View { return view('admin.suppliers.index',['suppliers'=>Supplier::with('photo')->latest()->paginate(12)]); }
    public function create(): View { return view('admin.suppliers.form',['supplier'=>new Supplier(['is_active'=>true])]); }
    public function store(Request $request): RedirectResponse { $request->merge(['is_active'=>$request->boolean('is_active')]); Supplier::create($request->validate(['name'=>'required|max:150','phone'=>'required|max:30','email'=>'nullable|email','address'=>'nullable','photo_media_id'=>'nullable|exists:media,id','is_active'=>'boolean'])); return to_route('admin.suppliers.index')->with('success','Supplier created successfully.'); }
    public function edit(Supplier $supplier): View { return view('admin.suppliers.form',compact('supplier')); }
    public function update(Request $request,Supplier $supplier): RedirectResponse { $request->merge(['is_active'=>$request->boolean('is_active')]); $supplier->update($request->validate(['name'=>'required|max:150','phone'=>'required|max:30','email'=>'nullable|email','address'=>'nullable','photo_media_id'=>'nullable|exists:media,id','is_active'=>'boolean'])); return to_route('admin.suppliers.index')->with('success','Supplier updated successfully.'); }
    public function destroy(Supplier $supplier): RedirectResponse { $supplier->delete(); return to_route('admin.suppliers.index')->with('success','Supplier deleted.'); }
}
