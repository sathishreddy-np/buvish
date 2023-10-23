<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $admin_user_1 = \App\Models\User::factory()->create([
            'name' => 'Admin 1',
            'email' => 'info@buvish.com',
            'password' => '12345678',
            'is_verified' => 1,
            'is_active' => 1
        ]);

        $admin_user_2 = \App\Models\User::factory()->create([
            'name' => 'Admin 2',
            'email' => 'info2@buvish.com',
            'password' => '12345678',
            'is_verified' => 1,
            'is_active' => 1
        ]);



        $company_1 = Company::create(['user_id' => $admin_user_1->id, 'name' => 'Pool1']);
        $company_2 = Company::create(['user_id' => $admin_user_2->id, 'name' => 'Pool2']);

        $role_1 = Role::create(['name' => 'Admin', 'guard_name' => 'web','company_id' => $company_1->id]);
        $role_2 = Role::create(['name' => 'Admin', 'guard_name' => 'web','company_id' => $company_2->id]);

        $admin_user_1->update(['company_id' => $company_1->id]);
        $admin_user_2->update(['company_id' => $company_2->id]);

        // below setPermissionsTeamId() is very crucial for getting and attaching team roles.
        // setPermissionsTeamId($company->id);

        $admin_user_1->assignRole($role_1);
        $admin_user_2->assignRole($role_2);

        $permission_models = ['Companies', 'Branches','Customers','Users', 'Roles', 'Permissions'];
        $permissions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

        foreach ($permission_models as $permission_model) {
            foreach ($permissions as $permission) {
                Permission::create(
                    [
                        'name' => "$permission_model :: $permission",
                        "guard_name" => "web"
                    ]
                );
            }
        }

        $all_permissions = Permission::all()->pluck('name');
        $role_1->syncPermissions($all_permissions);
        $role_2->syncPermissions($all_permissions);
    }
}
