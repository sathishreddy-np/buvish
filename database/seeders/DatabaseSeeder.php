<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Company;
use Illuminate\Database\Seeder;

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
            'password' => '12345678'
        ]);


        $company = Company::create([ 'user_id'=> $user->id, 'name' => 'Pool']);

        $user->update(['company_id'=> $company->id]);

    }
}
