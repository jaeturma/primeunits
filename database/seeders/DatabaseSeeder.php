<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RbacSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(LocationSeeder::class);
        $this->call(InsuranceCompanySeeder::class);
        $this->call(SeoPageSeeder::class);
        $this->call(LandingSeeder::class);
        $this->call(DemoSeeder::class);
        $this->call(RentalSeeder::class);
    }
}
