<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('roles.view'), 403);

        $roles = Role::query()->with('permissions')->latest()->paginate(10);

        return view('admin.roles.index', compact('roles'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('roles.create'), 403);

        return view('admin.roles.create', [
            'permissions' => Permission::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('roles.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::query()->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', 'Rol creado.');
    }

    public function edit(Request $request, Role $role): View
    {
        abort_unless($request->user()->can('roles.update'), 403);

        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->can('roles.update'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', 'Rol actualizado.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->can('roles.delete'), 403);

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Rol eliminado.');
    }
}
