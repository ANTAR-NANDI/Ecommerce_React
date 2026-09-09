<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsFooterSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsFooterController extends Controller
{
    public function edit(): View { return view('admin.cms.footer.edit', ['footer' => CmsFooterSetting::firstOrCreate(['id' => 1])]); }
    public function update(Request $request): RedirectResponse { $data = $request->validate(['about_title' => ['required', 'string', 'max:100'], 'about_text' => ['nullable', 'string', 'max:1000'], 'facebook_url' => ['nullable', 'url', 'max:255'], 'instagram_url' => ['nullable', 'url', 'max:255'], 'youtube_url' => ['nullable', 'url', 'max:255'], 'whatsapp_url' => ['nullable', 'url', 'max:255'], 'copyright_text' => ['nullable', 'string', 'max:255']]); CmsFooterSetting::updateOrCreate(['id' => 1], $data); return to_route('admin.cms.footer.edit')->with('success', 'Footer content updated for the live store.'); }
}
