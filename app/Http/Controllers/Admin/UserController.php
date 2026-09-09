<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View { return view('admin.users.index',['users'=>User::with('warehouse')->latest()->paginate(15)]); }
    public function create(): View { return view('admin.users.form',['user'=>new User(),'warehouses'=>Warehouse::where('is_active',true)->orderBy('name')->get()]); }
    public function store(Request $request): RedirectResponse { $data=$request->validate(['name'=>'required|max:150','email'=>'required|email|unique:users,email','password'=>'required|min:8','role'=>'required|in:superadmin,warehouse_admin,salesman','warehouse_id'=>'nullable|exists:warehouses,id']);if($data['role']!=='superadmin')$request->validate(['warehouse_id'=>'required|exists:warehouses,id']);else $data['warehouse_id']=null;User::create($data);return to_route('admin.users.index')->with('success','User account created successfully.'); }
}
