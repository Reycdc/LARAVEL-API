<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);


        $admin = Role::create(['name' => 'admin']);
        $superAdmin = Role::create(['name' => 'super-admin']);
        $customer = Role::create(['name' => 'customer']);
        
        // Membuat permissions
        $manage_role = Permission::create(['name' => 'manage roles']);
        $manage_product = Permission::create(['name' => 'manage products']);
        $manage_profile = Permission::create(['name' => 'manage profile']);

        // Memberikan permissions ke role
        $superAdmin->givePermissionTo([
            'manage roles',
            'manage products',
            'manage profile'
        ]);
        $admin->givePermissionTo('manage products');
        $customer->givePermissionTo('manage profile');

       
    }
}
