<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactSettingController extends Controller
{
    public function edit(): View { return view('admin.contact.edit',['contact'=>ContactSetting::firstOrCreate(['id'=>1])]); }
    public function update(Request $request): RedirectResponse { $data=$request->validate(['phone'=>'nullable|max:30','whatsapp'=>'nullable|max:30','messenger_url'=>'nullable|url|max:255','email'=>'nullable|email|max:255','address'=>'nullable|max:1000','business_hours'=>'nullable|max:255']); ContactSetting::updateOrCreate(['id'=>1],$data); return to_route('admin.contact.edit')->with('success','Live contact information updated.'); }
}
