<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View { return view('admin.roles.index', ['roles' => Role::orderByDesc('is_system')->orderBy('name')->get(), 'modules' => Role::MODULES]); }
    public function create(): View { return view('admin.roles.form', ['role' => new Role(['permissions' => []]), 'modules' => Role::MODULES]); }
    public function store(Request $request): RedirectResponse { Role::create($this->validated($request)); return to_route('admin.roles.index')->with('success', 'Role and permissions created.'); }
    public function edit(Role $role): View { return view('admin.roles.form', ['role' => $role, 'modules' => Role::MODULES]); }
    public function update(Request $request, Role $role): RedirectResponse { $role->update($this->validated($request, $role)); return to_route('admin.roles.index')->with('success', 'Role permissions updated.'); }
    public function destroy(Role $role): RedirectResponse { abort_if($role->is_system, 422, 'System roles cannot be deleted.'); abort_if(User::where('role', $role->slug)->exists(), 422, 'Reassign users before deleting this role.'); $role->delete(); return to_route('admin.roles.index')->with('success', 'Role deleted.'); }
    private function validated(Request $request, ?Role $role = null): array { $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'slug' => ['nullable', 'string', 'max:80', Rule::unique('roles', 'slug')->ignore($role)], 'permissions' => ['nullable', 'array'], 'permissions.*' => [Rule::in(array_keys(Role::MODULES))]]); $data['slug'] = Str::slug($data['slug'] ?: $data['name'], '_'); $data['permissions'] = array_values($data['permissions'] ?? []); return $data; }
}
