<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage victims',
            'send sos',
            'respond to sos',
            'view reports',
            'manage settings'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign created permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $rescuerRole = Role::firstOrCreate(['name' => 'rescuer']);
        $rescuerRole->givePermissionTo([
            'view dashboard',
            'respond to sos',
            'view reports'
        ]);

        $victimRole = Role::firstOrCreate(['name' => 'victim']);
        $victimRole->givePermissionTo([
            'view dashboard',
            'send sos'
        ]);
    }
}
