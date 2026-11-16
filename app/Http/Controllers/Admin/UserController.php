<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $users = User::with('roles')
            ->when($q, fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['active' => true]), 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'active' => $request->boolean('active'),
        ]);
        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('status', "Usuario {$user->email} creado.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $roles = $data['roles'] ?? [];
        $active = $request->boolean('active');

        // Evita que el sistema se quede sin administradores activos.
        if ($user->isLastActiveAdmin() && (! $active || ! in_array(Permissions::ROLE_ADMIN, $roles, true))) {
            return back()->withInput()->withErrors(['roles' => 'Debe existir al menos un administrador activo.']);
        }

        if ($user->is($request->user()) && ! $active) {
            return back()->withInput()->withErrors(['active' => 'No puede desactivar su propia cuenta.']);
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email'], 'active' => $active]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();
        $user->syncRoles($roles);

        return redirect()->route('admin.users.index')->with('status', "Usuario {$user->email} actualizado.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'No puede eliminar su propia cuenta.']);
        }
        if ($user->isLastActiveAdmin()) {
            return back()->withErrors(['user' => 'No puede eliminar al último administrador activo.']);
        }

        $email = $user->email;
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', "Usuario {$email} eliminado.");
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(10)->letters()->numbers()],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ], [], [
            'name' => 'nombre', 'email' => 'correo', 'password' => 'contraseña', 'roles' => 'roles',
        ]);
    }
}
