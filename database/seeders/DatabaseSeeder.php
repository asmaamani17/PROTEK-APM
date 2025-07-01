<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Seed roles and permissions first
        $this->call([
            RolesAndPermissionsSeeder::class,
            VulnerableGroupsTableSeeder::class,
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin PROTEK',
            'email' => 'admin@protek.com',
            'password' => Hash::make('password'),
            'no_telefon' => '0123456789',
            'daerah' => 'BATU PAHAT',
        ]);
        $admin->assignRole('admin');

        // Create victim users
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('victim');
        });

        // Create rescuer users
        User::factory(5)->create(['role' => 'rescuer'])->each(function ($user) {
            $user->assignRole('rescuer');
        });
    }

}
