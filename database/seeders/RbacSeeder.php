<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = collect([
            'superadmin' => 'Super Administrator',
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'coordinator' => 'Coordinator',
            'insurance_manager' => 'Insurance Manager',
            'seller' => 'Seller',
            'dealer' => 'Dealer',
            'rental_provider' => 'Rental Provider',
            'fleet_operator' => 'Fleet Operator',
            'financing_partner' => 'Financing Partner',
            'buyer' => 'Buyer',
        ])->map(fn (string $label, string $name): Role => Role::query()->updateOrCreate(
            ['name' => $name],
            ['label' => $label],
        ));

        $permissions = collect([
            'manage_users' => 'Manage Users',
            'manage_roles' => 'Manage Roles',
            'verify_sellers' => 'Verify Sellers',
            'manage_dealers' => 'Manage Dealers',
            'approve_listings' => 'Approve Listings',
            'manage_listings' => 'Manage Listings',
            'manage_rentals' => 'Manage Rentals',
            'view_reports' => 'View Reports',
            'view_own_analytics' => 'View Own Analytics',
            'manage_commissions' => 'Manage Commissions',
            'manage_subscriptions' => 'Manage Subscriptions',
            'manage_landing' => 'Manage Landing Page',
            'manage_locations' => 'Manage Locations',
            'manage_insurance' => 'Manage Insurance',
            'manage_catalog' => 'Manage Categories and Brands',
            'manage_financing' => 'Manage Financing Partners',
            'approve_financing' => 'Approve Financing Applications',
            'manage_farm_equipment' => 'Manage Farm Equipment Categories and Attributes',
            'review_drone_credentials' => 'Review Drone Pilot Credentials',
            'view_drone_credential_documents' => 'View Private Drone Credential Documents',
            'manage_drone_compliance' => 'Manage Drone Compliance Notices and Requirements',
            'suspend_drone_listings' => 'Suspend Noncompliant Drone Listings',
            'manage_memberships' => 'Manage Membership Plans and Statuses',
            'review_membership_applications' => 'Review Membership Applications',
            'review_buyer_access' => 'Review Buyer Access Applications',
            'review_seller_access' => 'Review Seller Access Applications',
            'review_stores' => 'Review Store Applications',
            'review_premium_listings' => 'Assign Listing Marketplace Tier and Visibility',
            'review_gold_listings' => 'Review Gold Candidate Listings',
            'view_confidential_documents' => 'View Private Membership Application Documents',
            'manage_listing_access' => 'Manage Invitation-Only Listing Access',
            'manage_category_tier_rules' => 'Configure Category Marketplace Tier Rules',
        ])->map(fn (string $label, string $name): Permission => Permission::query()->updateOrCreate(
            ['name' => $name],
            ['label' => $label],
        ));

        $roles->get('superadmin')?->permissions()->sync($permissions->pluck('id')->all());

        $adminPerms = array_filter([
            $permissions->get('manage_users')?->id,
            $permissions->get('manage_roles')?->id,
            $permissions->get('verify_sellers')?->id,
            $permissions->get('manage_dealers')?->id,
            $permissions->get('approve_listings')?->id,
            $permissions->get('manage_listings')?->id,
            $permissions->get('manage_rentals')?->id,
            $permissions->get('view_reports')?->id,
            $permissions->get('manage_commissions')?->id,
            $permissions->get('manage_subscriptions')?->id,
            $permissions->get('manage_landing')?->id,
            $permissions->get('manage_locations')?->id,
            $permissions->get('manage_insurance')?->id,
            $permissions->get('manage_catalog')?->id,
            $permissions->get('manage_financing')?->id,
            $permissions->get('approve_financing')?->id,
            $permissions->get('manage_farm_equipment')?->id,
            $permissions->get('review_drone_credentials')?->id,
            $permissions->get('view_drone_credential_documents')?->id,
            $permissions->get('manage_drone_compliance')?->id,
            $permissions->get('suspend_drone_listings')?->id,
            $permissions->get('manage_memberships')?->id,
            $permissions->get('review_membership_applications')?->id,
            $permissions->get('review_buyer_access')?->id,
            $permissions->get('review_seller_access')?->id,
            $permissions->get('review_stores')?->id,
            $permissions->get('review_premium_listings')?->id,
            $permissions->get('review_gold_listings')?->id,
            $permissions->get('view_confidential_documents')?->id,
            $permissions->get('manage_listing_access')?->id,
            $permissions->get('manage_category_tier_rules')?->id,
        ]);
        $roles->get('admin')?->permissions()->syncWithoutDetaching($adminPerms);

        $roles->get('manager')?->permissions()->syncWithoutDetaching(array_filter([
            $permissions->get('verify_sellers')?->id,
            $permissions->get('manage_dealers')?->id,
            $permissions->get('approve_listings')?->id,
            $permissions->get('manage_rentals')?->id,
            $permissions->get('view_reports')?->id,
            $permissions->get('manage_landing')?->id,
            $permissions->get('manage_locations')?->id,
            $permissions->get('manage_catalog')?->id,
            $permissions->get('manage_financing')?->id,
            $permissions->get('approve_financing')?->id,
            $permissions->get('manage_farm_equipment')?->id,
            $permissions->get('review_drone_credentials')?->id,
            $permissions->get('view_drone_credential_documents')?->id,
            $permissions->get('manage_drone_compliance')?->id,
            $permissions->get('suspend_drone_listings')?->id,
            $permissions->get('manage_memberships')?->id,
            $permissions->get('review_membership_applications')?->id,
            $permissions->get('review_buyer_access')?->id,
            $permissions->get('review_seller_access')?->id,
            $permissions->get('review_stores')?->id,
            $permissions->get('review_premium_listings')?->id,
            $permissions->get('review_gold_listings')?->id,
            $permissions->get('view_confidential_documents')?->id,
            $permissions->get('manage_listing_access')?->id,
            $permissions->get('manage_category_tier_rules')?->id,
        ]));

        $roles->get('coordinator')?->permissions()->syncWithoutDetaching(array_filter([
            $permissions->get('approve_listings')?->id,
            $permissions->get('manage_rentals')?->id,
            $permissions->get('review_drone_credentials')?->id,
            $permissions->get('review_membership_applications')?->id,
            $permissions->get('review_buyer_access')?->id,
            $permissions->get('review_seller_access')?->id,
        ]));

        $roles->get('insurance_manager')?->permissions()->syncWithoutDetaching(array_filter([
            $permissions->get('manage_insurance')?->id,
            $permissions->get('view_reports')?->id,
        ]));

        $roles->get('seller')?->permissions()->syncWithoutDetaching(array_filter([
            $permissions->get('view_own_analytics')?->id,
        ]));

        $roles->get('dealer')?->permissions()->syncWithoutDetaching(array_filter([
            $permissions->get('view_own_analytics')?->id,
        ]));

        $roles->get('rental_provider')?->permissions()->syncWithoutDetaching(array_filter([
            $permissions->get('manage_rentals')?->id,
            $permissions->get('view_own_analytics')?->id,
        ]));

        $roles->get('financing_partner')?->permissions()->syncWithoutDetaching(array_filter([
            $permissions->get('approve_financing')?->id,
            $permissions->get('view_own_analytics')?->id,
        ]));
    }
}
