<?php

namespace Database\Seeders;

use App\Models\InsuranceCompany;
use Illuminate\Database\Seeder;

class InsuranceCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'PrimeSure Insurance Agency',
                'slug' => 'primesure-insurance-agency',
                'description' => 'Demo insurance partner offering CTPL and premium comprehensive protection for cars, motorcycles, trucks, and selected commercial units.',
                'coverage_types' => ['CTPL Insurance', 'Premium Insurance'],
                'ctpl_price' => 650,
                'premium_starting_price' => 8500,
                'contact_number' => '09171234567',
                'contact_email' => 'quotes@primesure.test',
                'website' => 'https://insurance.prime.test/primesure',
                'region' => 'Metro Manila',
                'province' => 'Metro Manila',
                'municipality' => 'City of Makati',
                'full_address' => 'Makati Insurance Desk, Ayala Avenue, City of Makati',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Isla Mutual Assurance',
                'slug' => 'isla-mutual-assurance',
                'description' => 'Sample provider for CTPL renewals and premium policies with Acts of Nature, theft, own damage, and third-party property damage options.',
                'coverage_types' => ['CTPL Insurance', 'Premium Insurance'],
                'ctpl_price' => 720,
                'premium_starting_price' => 9800,
                'contact_number' => '09179876543',
                'contact_email' => 'support@islamutual.test',
                'website' => 'https://insurance.prime.test/isla-mutual',
                'region' => 'Region VII',
                'province' => 'Cebu',
                'municipality' => 'City of Mandaue',
                'full_address' => 'North Reclamation Area, City of Mandaue, Cebu',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($companies as $company) {
            InsuranceCompany::query()->updateOrCreate(
                ['slug' => $company['slug']],
                $company,
            );
        }
    }
}
