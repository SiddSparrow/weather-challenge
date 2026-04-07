<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CompanySeeder::class);

        Company::all()->each(function (Company $company, int $index) {
            User::factory()->create([
                'name' => "User {$company->name}",
                'email' => "user{$index}@example.com",
                'password' => bcrypt('password'),
                'company_id' => $company->id,
            ]);
        });
    }
}
