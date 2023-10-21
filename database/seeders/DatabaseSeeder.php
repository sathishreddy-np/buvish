<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $user = \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'info@buvish.com',
            'password' => '12345678',
            'is_verified' => 1,
        ]);

        $company = Company::create(['user_id' => $user->id, 'name' => 'Pool']);

        $role = Role::create(['name' => 'admin', 'guard_name' => 'web', 'company_id' => $company->id]);

        $user->update(['company_id' => $company->id]);

        // below setPermissionsTeamId() is very crucial for getting and attaching team roles.
        setPermissionsTeamId($company->id);

        $user->assignRole($role);
    }
}
