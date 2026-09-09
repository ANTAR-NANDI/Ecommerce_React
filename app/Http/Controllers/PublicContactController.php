<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PublicContactController extends Controller
{
    public function show(): View { return view('contact-us',['contact'=>ContactSetting::firstOrCreate(['id'=>1])]); }
    public function data(): JsonResponse { return response()->json(ContactSetting::firstOrCreate(['id'=>1])); }
}
