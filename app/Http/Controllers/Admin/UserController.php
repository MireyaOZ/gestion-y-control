<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const IMPERSONATOR_ID = 'impersonator_id';
    private const IMPERSONATOR_NAME = 'impersonator_name';

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('users.view'), 403);

        $users = User::query()->with('roles')->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('users.create'), 403);

        return view('admin.users.create', ['roles' => Role::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('users.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado.');
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($request->user()->can('users.update'), 403);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('users.update'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'password' => $data['password'] ?: $user->password,
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('status', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('users.delete'), 403);

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Usuario eliminado.');
    }

    public function impersonate(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('admin.access'), 403);

        if ($request->session()->has(self::IMPERSONATOR_ID)) {
            return back()->with('status', 'Ya tienes una sesión de acceso rápido activa. Regresa primero a tu cuenta.');
        }

        if ($request->user()->is($user)) {
            return back()->with('status', 'Ya estás usando esta cuenta.');
        }

        if (! $user->is_active) {
            return back()->with('status', 'No puedes acceder a una cuenta inactiva.');
        }

        $request->session()->put([
            self::IMPERSONATOR_ID => $request->user()->id,
            self::IMPERSONATOR_NAME => $request->user()->name,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Accediste rápidamente a la cuenta de '.$user->name.'.');
    }

    public function stopImpersonation(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get(self::IMPERSONATOR_ID);

        abort_unless($impersonatorId, 403);

        $impersonator = User::query()->find($impersonatorId);

        abort_unless($impersonator !== null, 403);

        Auth::login($impersonator);
        $request->session()->forget([self::IMPERSONATOR_ID, self::IMPERSONATOR_NAME]);
        $request->session()->regenerate();

        return redirect()->route('admin.users.index')->with('status', 'Regresaste a tu cuenta de administrador.');
    }
}
