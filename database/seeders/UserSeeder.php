<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         \App\Models\User::create([
        'name' => 'Admin PROTEK',
        'email' => 'admin@protek.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    \App\Models\User::create([
        'name' => 'Victim Ali',
        'email' => 'victim@protek.com',
        'password' => bcrypt('password'),
        'role' => 'victim',
    ]);

    \App\Models\User::create([
        'name' => 'Rescuer APM',
        'email' => 'rescuer@protek.com',
        'password' => bcrypt('password'),
        'role' => 'rescuer',
    ]);
    }
}
