<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index(): View { return view('admin.warehouses.index',['warehouses'=>Warehouse::with('users')->latest()->paginate(12)]); }
    public function create(): View { return view('admin.warehouses.form',['warehouse'=>new Warehouse(['is_active'=>true])]); }
    public function store(Request $request): RedirectResponse { $request->merge(['is_active'=>$request->boolean('is_active')]); $data=$request->validate(['name'=>'required|max:150','code'=>'required|max:30|unique:warehouses,code','manager_name'=>'nullable|max:150','phone'=>'nullable|max:30','email'=>'nullable|email','address'=>'nullable','is_active'=>'boolean','login_email'=>'required|email|unique:users,email','login_password'=>'required|min:8']); DB::transaction(function() use($data){$warehouse=Warehouse::create(collect($data)->except(['login_email','login_password'])->all());User::create(['name'=>$warehouse->manager_name ?: $warehouse->name.' Admin','email'=>$data['login_email'],'password'=>$data['login_password'],'role'=>'warehouse_admin','warehouse_id'=>$warehouse->id]);}); return to_route('admin.warehouses.index')->with('success','Warehouse and Warehouse Admin login created successfully.'); }
    public function edit(Warehouse $warehouse): View { return view('admin.warehouses.form',compact('warehouse')); }
    public function update(Request $request, Warehouse $warehouse): RedirectResponse { $request->merge(['is_active'=>$request->boolean('is_active')]); $admin=$warehouse->users()->where('role','warehouse_admin')->first(); $data=$request->validate(['name'=>'required|max:150','code'=>'required|max:30|unique:warehouses,code,'.$warehouse->id,'manager_name'=>'nullable|max:150','phone'=>'nullable|max:30','email'=>'nullable|email','address'=>'nullable','is_active'=>'boolean','login_email'=>'required|email|unique:users,email,'.($admin?->id ?? 'NULL'),'login_password'=>'nullable|min:8']); DB::transaction(function() use($data,$warehouse,$admin){$warehouse->update(collect($data)->except(['login_email','login_password'])->all());$account=['name'=>$warehouse->manager_name ?: $warehouse->name.' Admin','email'=>$data['login_email'],'role'=>'warehouse_admin','warehouse_id'=>$warehouse->id];if(!empty($data['login_password']))$account['password']=$data['login_password'];if($admin)$admin->update($account);else User::create($account+['password'=>$data['login_password'] ?? 'ChangeMe123!']);}); return to_route('admin.warehouses.index')->with('success','Warehouse and login updated successfully.'); }
    public function destroy(Warehouse $warehouse): RedirectResponse { $warehouse->delete(); return to_route('admin.warehouses.index')->with('success','Warehouse deleted successfully.'); }
}
