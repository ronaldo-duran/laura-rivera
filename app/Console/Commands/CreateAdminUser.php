<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    protected $signature = 'app:crear-admin
                            {--name= : Nombre completo}
                            {--email= : Correo de acceso}
                            {--password= : Contraseña (si se omite, se pide de forma oculta)}';

    protected $description = 'Crea (o promueve) un usuario administrador del panel';

    public function handle(): int
    {
        $this->callSilently('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);

        $name = $this->option('name') ?: $this->ask('Nombre completo');
        $email = $this->option('email') ?: $this->ask('Correo');
        $password = $this->option('password') ?: $this->secret('Contraseña (mínimo 10 caracteres)');

        $existing = User::where('email', $email)->first();

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', Password::min(10)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = $existing ?? new User;
        $user->fill(['name' => $name, 'email' => $email, 'password' => $password, 'active' => true]);
        $user->save();
        $user->assignRole(Permissions::ROLE_ADMIN);

        $this->info(($existing ? 'Usuario actualizado' : 'Administrador creado').": {$user->email}");

        return self::SUCCESS;
    }
}
