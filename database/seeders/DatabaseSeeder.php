<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Create admin user
        \App\Models\User::create([
            'name' => 'Admin PROTEK',
            'email' => 'admin@protek.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        
        // Seed vulnerable groups data
        $this->call([
            VulnerableGroupsTableSeeder::class,
        ]);

    \App\Models\User::factory(10)->create(['role' => 'victim']);
    \App\Models\User::factory(5)->create(['role' => 'rescuer']);
    }

}
