<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function index(): View { return view('admin.cms.pages.index', ['pages' => CmsPage::latest()->paginate(15)]); }
    public function create(): View { return view('admin.cms.pages.form', ['page' => new CmsPage(['is_active' => true])]); }
    public function store(Request $request): RedirectResponse { CmsPage::create($this->validated($request)); return to_route('admin.cms.pages.index')->with('success', 'Page created and ready to publish.'); }
    public function edit(CmsPage $page): View { return view('admin.cms.pages.form', compact('page')); }
    public function update(Request $request, CmsPage $page): RedirectResponse { $page->update($this->validated($request, $page)); return to_route('admin.cms.pages.index')->with('success', 'Page updated successfully.'); }
    public function destroy(CmsPage $page): RedirectResponse { $page->delete(); return to_route('admin.cms.pages.index')->with('success', 'Page deleted.'); }
    private function validated(Request $request, ?CmsPage $page = null): array {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', Rule::unique('cms_pages', 'slug')->ignore($page)], 'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:500'], 'content' => ['required', 'string'], 'is_active' => ['required', 'boolean']]);
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']); $data['is_active'] = (bool) $data['is_active']; return $data;
    }
}
