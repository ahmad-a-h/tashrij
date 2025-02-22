<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Misc
        $miscPermission = Permission::create(['name' => 'N/A']);

        // USER MODEL
        $userPermission0 = Permission::create(['name' => 'view_user']);
        $userPermission1 = Permission::create(['name' => 'create_user']);
        $userPermission2 = Permission::create(['name' => 'read_user']);
        $userPermission3 = Permission::create(['name' => 'update_user']);
        $userPermission4 = Permission::create(['name' => 'delete_user']);
        $userPermission5 = Permission::create(['name' => 'delete_any_user']);
        $userPermission6 = Permission::create(['name' => 'force_delete_user']);
        $userPermission7 = Permission::create(['name' => 'force_delete_any_user']);
        $userPermission8 = Permission::create(['name' => 'restore_user']);
        $userPermission9 = Permission::create(['name' => 'restore_any_user']);
        $userPermission10 = Permission::create(['name' => 'replicate_user']);
        $userPermission11 = Permission::create(['name' => 'reorder_user']);
        // ROLE MODEL
        $rolePermission0 = Permission::create(['name' => 'view_role']);
        $rolePermission1 = Permission::create(['name' => 'create_role']);
        $rolePermission2 = Permission::create(['name' => 'read_role']);
        $rolePermission3 = Permission::create(['name' => 'update_role']);
        $rolePermission4 = Permission::create(['name' => 'delete_role']);
        $rolePermission4 = Permission::create(['name' => 'view_any_role']);

        // PERMISSION MODEL
        $permission0 = Permission::create(['name' => 'view_permission']);
        $permission1 = Permission::create(['name' => 'create_permission']);
        $permission2 = Permission::create(['name' => 'read_permission']);
        $permission3 = Permission::create(['name' => 'update_permission']);
        $permission4 = Permission::create(['name' => 'delete_permission']);

        // ADMINS
        $adminPermission1 = Permission::create(['name' => 'read_admin']);
        $adminPermission2 = Permission::create(['name' => 'update_admin']);

        // CREATE ROLES
        $userRole = Role::create(['name' => 'user'])->syncPermissions([
            $miscPermission,
        ]);

        $superAdminRole = Role::create(['name' => 'super-admin'])->syncPermissions([
            $userPermission0,
            $userPermission1,
            $userPermission2,
            $userPermission3,
            $userPermission4,
            $rolePermission0,
            $rolePermission1,
            $rolePermission2,
            $rolePermission3,
            $rolePermission4,
            $permission0,
            $permission1,
            $permission2,
            $permission3,
            $permission4,
            $adminPermission1,
            $adminPermission2,
            $userPermission1,
        ]);
        $adminRole = Role::create(['name' => 'admin'])->syncPermissions([
            $userPermission0,
            $userPermission1,
            $userPermission2,
            $userPermission3,
            $userPermission4,
            $rolePermission0,
            $rolePermission1,
            $rolePermission2,
            $rolePermission3,
            $rolePermission4,
            $permission0,
            $permission1,
            $permission2,
            $permission3,
            $permission4,
            $adminPermission1,
            $adminPermission2,
            $userPermission1,
        ]);


        // CREATE ADMINS & USERS
        User::create([
            'name' => 'super admin',
            'email' => 'super@admin.com',
            'is_admin' =>1,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'admin_create'=>0
        ])->assignRole($superAdminRole);

        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'admin_create'=>0

        ])->assignRole($adminRole);



    }
}
