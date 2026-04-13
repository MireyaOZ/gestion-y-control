<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('permissions.view'), 403);

        $permissions = Permission::query()->orderBy('name')->paginate(20);

        return view('admin.permissions.index', compact('permissions'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('permissions.assign'), 403);

        return view('admin.permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('permissions.assign'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::query()->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        return redirect()->route('admin.permissions.index')->with('status', 'Permiso creado.');
    }

    public function edit(Request $request, Permission $permission): View
    {
        abort_unless($request->user()->can('permissions.assign'), 403);

        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        abort_unless($request->user()->can('permissions.assign'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        $permission->update(['name' => $data['name']]);

        return redirect()->route('admin.permissions.index')->with('status', 'Permiso actualizado.');
    }

    public function destroy(Request $request, Permission $permission): RedirectResponse
    {
        abort_unless($request->user()->can('permissions.assign'), 403);

        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('status', 'Permiso eliminado.');
    }
}
