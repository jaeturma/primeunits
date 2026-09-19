<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RbacSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(LocationSeeder::class);
        $this->call(InsuranceCompanySeeder::class);

        $this->seedUsers();

        $this->call(DemoSeeder::class);
    }

    /**
     * @return array<string, User>
     */
    public function seedUsers(): array
    {
        $superadmin = $this->user('PrimeUnits Admin', 'admin@prime.test', 'superadmin', 'super', 'super123');
        $manager = $this->user('Operations Manager', 'manager@prime.test', 'manager', 'manager');
        $insuranceManager = $this->user('Insurance Desk Manager', 'insurance@prime.test', 'insurance_manager', 'insurance_mgr');
        $sellerOne = $this->user('Cebu Equipment Hub', 'seller.cebu@prime.test', 'seller', 'cebu_hub');
        $sellerTwo = $this->user('Davao Agri Machines', 'seller.davao@prime.test', 'seller', 'davao_agri');
        $sellerPending = $this->user('Pending Seller Co.', 'seller.pending@prime.test', 'seller', 'pending_seller');
        $buyerOne = $this->user('Miguel Santos', 'buyer.miguel@prime.test', 'buyer', 'miguel_santos');
        $buyerTwo = $this->user('Ana Reyes', 'buyer.ana@prime.test', 'buyer', 'ana_reyes');
        $buyerThree = $this->user('Jon Cruz', 'buyer.jon@prime.test', 'buyer', 'jon_cruz');

        $this->sellerProfile($sellerOne, [
            'seller_type' => 'business',
            'business_name' => 'Cebu Equipment Hub',
            'owner_name' => 'Carmela Uy',
            'region' => 'Region VII',
            'province' => 'Cebu',
            'municipality' => 'City of Mandaue',
            'barangay' => 'Subangdaku',
            'status' => SellerProfile::StatusVerified,
            'verified_at' => now()->subMonths(3),
        ]);

        $this->sellerProfile($sellerTwo, [
            'seller_type' => 'business',
            'business_name' => 'Davao Agri Machines',
            'owner_name' => 'Ramon Lim',
            'region' => 'Region XI',
            'province' => 'Davao del Sur',
            'municipality' => 'City of Davao',
            'barangay' => 'Buhangin',
            'status' => SellerProfile::StatusVerified,
            'verified_at' => now()->subMonths(2),
        ]);

        $this->sellerProfile($sellerPending, [
            'seller_type' => 'individual',
            'owner_name' => 'Paolo Navarro',
            'region' => 'Region IV-A',
            'province' => 'Laguna',
            'municipality' => 'City of Calamba',
            'barangay' => 'Real',
            'status' => SellerProfile::StatusPending,
            'verified_at' => null,
        ]);

        foreach ([$superadmin, $manager, $insuranceManager, $sellerOne, $sellerTwo, $sellerPending, $buyerOne, $buyerTwo, $buyerThree] as $user) {
            $user->notificationPreferenceOrDefault();
        }

        return compact('superadmin', 'manager', 'insuranceManager', 'sellerOne', 'sellerTwo', 'sellerPending', 'buyerOne', 'buyerTwo', 'buyerThree');
    }

    private function user(string $name, string $email, string $role, string $username = '', string $password = 'password'): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => $username ?: strtolower(str_replace(' ', '_', $name)),
                'email_verified_at' => now(),
                'password' => Hash::make($password),
            ],
        );

        $roleModel = Role::query()->where('name', $role)->firstOrFail();
        $user->roles()->syncWithoutDetaching([$roleModel->id]);

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function sellerProfile(User $seller, array $attributes): SellerProfile
    {
        return SellerProfile::query()->updateOrCreate(
            ['user_id' => $seller->id],
            [
                'contact_number' => '09170000000',
                'email' => $seller->email,
                'full_address' => ($attributes['municipality'] ?? 'City').' Office',
                'permit_number' => 'PU-DEMO-'.$seller->id,
                ...$attributes,
            ],
        );
    }
}
