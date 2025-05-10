<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password')
            ]
        );
        $admin->assignRole('admin');

        // Guru
        $guru = User::firstOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'customer',
                'password' => Hash::make('password')
            ]
        );
        $guru->assignRole('customer');

        // Siswa
        $siswa = User::firstOrCreate(
            ['email' => 'super-admin@gmail.com'],
            [
                'name' => 'super',
                'password' => Hash::make('password')
            ]
        );
        $siswa->assignRole('super-admin');
    }
}