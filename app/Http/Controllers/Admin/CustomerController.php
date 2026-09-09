<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View { return view('admin.customers.index',['customers'=>Customer::with('profileMedia')->latest()->paginate(15)]); }
    public function create(): View { return view('admin.customers.form',['customer'=>new Customer(['is_active'=>true])]); }
    public function store(Request $request): RedirectResponse { $data=$this->validated($request);Customer::create($data);return to_route('admin.customers.index')->with('success','Customer created successfully.'); }
    public function edit(Customer $customer): View { return view('admin.customers.form',compact('customer')); }
    public function update(Request $request, Customer $customer): RedirectResponse { $data=$this->validated($request,$customer);if(empty($data['password']))unset($data['password']);$customer->update($data);return to_route('admin.customers.index')->with('success','Customer updated successfully.'); }
    public function destroy(Customer $customer): RedirectResponse { $customer->delete();return to_route('admin.customers.index')->with('success','Customer deleted successfully.'); }
    private function validated(Request $request, ?Customer $customer=null): array { $request->merge(['is_active'=>$request->boolean('is_active')]);$passwordRule=$customer ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed';return $request->validate(['first_name'=>'required|max:100','last_name'=>'nullable|max:100','phone'=>'required|max:30|unique:customers,phone,'.($customer?->id ?? 'NULL'),'email'=>'nullable|email|max:255|unique:customers,email,'.($customer?->id ?? 'NULL'),'password'=>$passwordRule,'profile_media_id'=>'nullable|exists:media,id','date_of_birth'=>'nullable|date','gender'=>'nullable|in:male,female,other','is_active'=>'boolean']); }
}
