<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::query()->updateOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }

        if (class_exists(\BezhanSalleh\FilamentShield\Commands\SuperAdminCommand::class)) {
            Artisan::call('shield:super-admin', [
                '--user' => $user->id,
                '--panel' => 'admin',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}