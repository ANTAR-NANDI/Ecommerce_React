<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CmsMenuController extends Controller
{
    public function index(): View { return view('admin.cms.menus.index', ['menus' => CmsMenu::orderBy('location')->orderBy('sort_order')->get()]); }
    public function create(): View { return view('admin.cms.menus.form', ['menu' => new CmsMenu(['location' => 'header', 'is_active' => true])]); }
    public function store(Request $request): RedirectResponse { CmsMenu::create($this->validated($request)); return to_route('admin.cms.menus.index')->with('success', 'Menu item created.'); }
    public function edit(CmsMenu $menu): View { return view('admin.cms.menus.form', compact('menu')); }
    public function update(Request $request, CmsMenu $menu): RedirectResponse { $menu->update($this->validated($request)); return to_route('admin.cms.menus.index')->with('success', 'Menu item updated.'); }
    public function destroy(CmsMenu $menu): RedirectResponse { $menu->delete(); return to_route('admin.cms.menus.index')->with('success', 'Menu item deleted.'); }
    private function validated(Request $request): array { $data = $request->validate(['label' => ['required', 'string', 'max:80'], 'url' => ['required', 'string', 'max:255'], 'location' => ['required', Rule::in(['header', 'footer_shop', 'footer_support', 'footer_company'])], 'sort_order' => ['required', 'integer', 'min:0', 'max:9999'], 'is_active' => ['required', 'boolean'], 'open_in_new_tab' => ['required', 'boolean']]); $data['is_active'] = (bool) $data['is_active']; $data['open_in_new_tab'] = (bool) $data['open_in_new_tab']; return $data; }
}
