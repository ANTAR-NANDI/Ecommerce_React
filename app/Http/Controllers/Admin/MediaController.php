<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View { return view('admin.media.index', ['media' => Media::latest()->paginate(24)]); }
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['images' => ['required','array','max:10'], 'images.*' => ['required','image','mimes:jpg,jpeg,png,webp,gif','max:4096']]);
        foreach ($request->file('images') as $image) {
            Media::create(['path' => $image->store('media', 'public'), 'original_name' => $image->getClientOriginalName(), 'mime_type' => $image->getMimeType(), 'size' => $image->getSize()]);
        }
        return back()->with('success', 'Images uploaded to Media Library.');
    }
}
