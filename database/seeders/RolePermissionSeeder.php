<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cache roles dan permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat permissions dasar (opsional, bisa ditambah nanti sesuai fitur)
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage programs']);
        Permission::create(['name' => 'review applications']);
        Permission::create(['name' => 'apply programs']);

        // 3. Buat roles dan assign permission
        $roleSuperAdmin = Role::create(['name' => 'Super Admin']);
        // Super Admin tidak perlu di-assign permission karena sudah di-bypass di AppServiceProvider

        $roleAdminProgram = Role::create(['name' => 'Admin Program']);
        $roleAdminProgram->givePermissionTo(['manage programs', 'review applications']);

        $roleReviewer = Role::create(['name' => 'Reviewer']);
        $roleReviewer->givePermissionTo('review applications');

        $roleParticipant = Role::create(['name' => 'Participant']);
        $roleParticipant->givePermissionTo('apply programs');

        // 4. Buat Akun Super Admin Pertama
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@program.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(), // Langsung terverifikasi
            ]
        );

        $superAdmin->assignRole($roleSuperAdmin);
    }
}
