<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::withCount('users')->with('permissions')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', ['role' => new Role, 'permissions' => $this->permissionOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', "Rol «{$role->name}» creado.");
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', ['role' => $role, 'permissions' => $this->permissionOptions()]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->name === Permissions::ROLE_ADMIN) {
            return back()->withErrors(['name' => 'El rol administrador no se puede modificar: siempre tiene todos los permisos.']);
        }

        $data = $this->validated($request, $role);
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', "Rol «{$role->name}» actualizado.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === Permissions::ROLE_ADMIN) {
            return back()->withErrors(['role' => 'El rol administrador no se puede eliminar.']);
        }
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => "El rol «{$role->name}» tiene usuarios asignados. Reasígnelos antes de eliminarlo."]);
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', "Rol «{$name}» eliminado.");
    }

    /** @return array<string, string> */
    private function permissionOptions(): array
    {
        return Permission::orderBy('name')->pluck('name')
            ->mapWithKeys(fn ($name) => [$name => Permissions::label($name)])
            ->all();
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60', 'regex:/^[\pL\pN _-]+$/u', Rule::unique('roles', 'name')->ignore($role?->id)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ], [], ['name' => 'nombre del rol', 'permissions' => 'permisos']);
    }
}
