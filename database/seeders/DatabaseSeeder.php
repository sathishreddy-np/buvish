<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // $user = \App\Models\User::factory()->create([
        //     'name' => 'Admin 1',
        //     'email' => 'info@buvish.com',
        //     'password' => '12345678',
        //     'is_verified' => 1,
        //     'is_active' => 1
        // ]);

        $permission_models = ['Companies', 'Branches', 'Customers', 'Users', 'Roles', 'Permissions'];
        $permissions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

        foreach ($permission_models as $permission_model) {
            foreach ($permissions as $permission) {
                Permission::create(
                    [
                        'name' => "$permission_model :: $permission",
                        'guard_name' => 'web',
                    ]
                );
            }
        }

        // DB::table('users')->update(['limits' => json_encode([
        //     "users" => 5,
        //     "roles" => 5,
        //     "companies" => 1,
        //     "branches" => 5,
        //     "customers" => 1000,
        // ])]);

    }
}
