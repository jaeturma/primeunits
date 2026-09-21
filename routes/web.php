<?php

use App\Http\Controllers\Adm\AnalyticsController;
use App\Http\Controllers\Adm\BrandController as AdminBrandController;
use App\Http\Controllers\Adm\CategoryController as AdminCategoryController;
use App\Http\Controllers\Adm\DealerProfileController as AdminDealerProfileController;
use App\Http\Controllers\Adm\DroneCredentialController as AdminDroneCredentialController;
use App\Http\Controllers\Adm\FinancingApplicationController as AdminFinancingApplicationController;
use App\Http\Controllers\Adm\FinancingPartnerController as AdminFinancingPartnerController;
use App\Http\Controllers\Adm\LandingAdController as AdminLandingAdController;
use App\Http\Controllers\Adm\LandingPageController as AdminLandingPageController;
use App\Http\Controllers\Adm\ListingController as AdminListingController;
use App\Http\Controllers\Adm\LocationController as AdminLocationController;
use App\Http\Controllers\Adm\PaymentController as AdminPaymentController;
use App\Http\Controllers\Adm\PlanController as AdminPlanController;
use App\Http\Controllers\Adm\RentalController as AdminRentalController;
use App\Http\Controllers\Adm\RentalProfileController as AdminRentalProfileController;
use App\Http\Controllers\Adm\SellerProfileController as AdminSellerProfileController;
use App\Http\Controllers\Adm\TransactionController as AdminTransactionController;
use App\Http\Controllers\Adm\UserController;
use App\Http\Controllers\DealerProfileController;
use App\Http\Controllers\DronePilotCredentialController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FinancingApplicationController;
use App\Http\Controllers\FinancingController;
use App\Http\Controllers\FinancingPartnerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ListingBoostController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\RentalProfileController;
use App\Http\Controllers\SellerAnalyticsController;
use App\Http\Controllers\SellerProfileController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('insurance', InsuranceController::class)->name('insurance');

Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
Route::get('listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
Route::get('categories/{category}/spec-fields', [ListingController::class, 'specFields'])->name('categories.spec-fields');
Route::get('categories/{category}/classifications', [ListingController::class, 'classifications'])->name('categories.classifications');

// Public seller profiles
Route::get('sellers/{sellerProfile}', [SellerProfileController::class, 'publicShow'])->name('sellers.show');

// Public financing browse
Route::get('financing', [FinancingController::class, 'index'])->name('financing.index');
Route::get('financing/{financingPartner:slug}', [FinancingController::class, 'show'])->name('financing.show');

// Public dealer & rental browse
Route::get('dealers', [DealerProfileController::class, 'index'])->name('dealers.index');
Route::get('dealers/{dealerProfile:slug}', [DealerProfileController::class, 'show'])->name('dealers.show');
Route::get('dealer/{dealerProfile:slug}', [DealerProfileController::class, 'show'])->name('dealer.show');
Route::get('rentals', [RentalController::class, 'index'])->name('rentals.index');
Route::get('rentals/{provider}', [RentalController::class, 'provider'])->name('rentals.provider');
Route::get('rentals/{provider:username}/{rentalUnit:slug}', [RentalController::class, 'show'])->name('rentals.show');
Route::get('rental-units/{rentalUnit:slug}', [RentalController::class, 'legacyShow'])->name('rentals.legacy-show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('transactions/{transaction}/confirm-buyer', [TransactionController::class, 'confirmByBuyer'])->name('transactions.confirm-buyer');
    Route::post('transactions/{transaction}/confirm-seller', [TransactionController::class, 'confirmBySeller'])->name('transactions.confirm-seller');
    Route::post('transactions/{transaction}/proof', [TransactionController::class, 'uploadProof'])->name('transactions.proof');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/{payment}', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::put('notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences');

    Route::post('listings/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite');

    // Financing applications (any authenticated user)
    Route::get('buyer/financing-applications', [FinancingApplicationController::class, 'index'])->name('buyer.financing-applications.index');
    Route::get('buyer/financing-applications/apply', [FinancingApplicationController::class, 'create'])->name('buyer.financing-applications.create');
    Route::post('buyer/financing-applications', [FinancingApplicationController::class, 'store'])->name('buyer.financing-applications.store');
    Route::get('buyer/financing-applications/{financingApplication}', [FinancingApplicationController::class, 'show'])->name('buyer.financing-applications.show');

    // Financing partner portal
    Route::prefix('financing-partner')->name('financing-partner.')->group(function (): void {
        Route::get('apply', [FinancingPartnerController::class, 'apply'])->name('apply');
        Route::post('apply', [FinancingPartnerController::class, 'store'])->name('store');
        Route::get('status', [FinancingPartnerController::class, 'status'])->name('status');
        Route::middleware('role:financing_partner')->group(function (): void {
            Route::get('products', [FinancingPartnerController::class, 'products'])->name('products.index');
            Route::post('products', [FinancingPartnerController::class, 'storeProduct'])->name('products.store');
            Route::get('applications', [FinancingPartnerController::class, 'applications'])->name('applications.index');
            Route::patch('applications/{financingApplication}/review', [FinancingPartnerController::class, 'reviewApplication'])->name('applications.review');
        });
    });

    Route::middleware('role:buyer')
        ->prefix('buyer')
        ->name('buyer.')
        ->group(function () {
            Route::get('profile', [RentalProfileController::class, 'show'])->name('profile.show');
            Route::post('profile', [RentalProfileController::class, 'store'])->name('profile.store');
            Route::get('leads', [LeadController::class, 'myLeads'])->name('leads.index');
            Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        });

    // Rental provider routes
    Route::middleware('role:rental_provider')
        ->prefix('rental-provider')
        ->name('rental-provider.')
        ->group(function () {
            Route::get('units', [RentalController::class, 'myUnits'])->name('units.index');
            Route::get('units/create', [RentalController::class, 'create'])->name('units.create');
            Route::post('units', [RentalController::class, 'store'])->name('units.store');
            Route::delete('units/{rentalUnit}', [RentalController::class, 'destroy'])->name('units.destroy');
            Route::get('bookings', [RentalController::class, 'myBookings'])->name('bookings.index');
            Route::patch('bookings/{rentalBooking}/status', [RentalController::class, 'updateBookingStatus'])->name('bookings.status');
        });

    // Rental booking (any auth user)
    Route::post('rentals/{rentalUnit}/book', [RentalController::class, 'submitBooking'])->name('rentals.book');

    // Drone pilot credential self-service (any auth user)
    Route::prefix('drone-credentials')->name('drone-credentials.')->group(function (): void {
        Route::get('/', [DronePilotCredentialController::class, 'show'])->name('show');
        Route::post('/', [DronePilotCredentialController::class, 'store'])->name('store');
        Route::get('{credential}/documents/{type}', [DronePilotCredentialController::class, 'document'])->name('documents');
    });

    Route::middleware('role:dealer')
        ->prefix('dealer')
        ->name('dealer.')
        ->group(function () {
            Route::get('apply', [DealerProfileController::class, 'apply'])->name('apply');
            Route::post('apply', [DealerProfileController::class, 'store'])->name('store');
            Route::get('status', [DealerProfileController::class, 'status'])->name('status');
            Route::get('units', [ListingController::class, 'myListings'])->name('units.index');
            Route::get('units/create', [ListingController::class, 'create'])->name('units.create');
            Route::post('units', [ListingController::class, 'store'])->name('units.store');
            Route::get('units/{listing}/edit', [ListingController::class, 'edit'])->name('units.edit');
            Route::put('units/{listing}', [ListingController::class, 'update'])->name('units.update');
            Route::delete('units/{listing}', [ListingController::class, 'destroy'])->name('units.destroy');
        });

    Route::middleware('role:seller')
        ->prefix('seller')
        ->name('seller.')
        ->group(function () {
            Route::get('apply', [SellerProfileController::class, 'apply'])->name('apply');
            Route::post('apply', [SellerProfileController::class, 'store'])->name('store');
            Route::get('status', [SellerProfileController::class, 'show'])->name('status');
            Route::put('profile', [SellerProfileController::class, 'update'])->name('update');
            Route::middleware('seller.verified')->group(function () {
                Route::get('analytics', [SellerAnalyticsController::class, 'index'])->name('analytics.index');
                Route::get('monetization', [PlanController::class, 'index'])->name('monetization.index');
                Route::post('plans/{plan}/subscribe', [SubscriptionController::class, 'subscribe'])->name('plans.subscribe');
                Route::post('listings/{listing}/boosts/{plan}', [ListingBoostController::class, 'boostListing'])->name('listings.boosts.store');
                Route::get('leads', [LeadController::class, 'sellerLeads'])->name('leads.index');
                Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');
                Route::post('leads/{lead}/transactions', [TransactionController::class, 'createFromLead'])->name('leads.transactions.store');
                Route::get('listings', [ListingController::class, 'myListings'])->name('listings.index');
                Route::get('listings/create', [ListingController::class, 'create'])->name('listings.create');
                Route::post('listings', [ListingController::class, 'store'])->name('listings.store');
                Route::get('listings/{listing}/edit', [ListingController::class, 'edit'])->name('listings.edit');
                Route::put('listings/{listing}', [ListingController::class, 'update'])->name('listings.update');
                Route::delete('listings/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');
                Route::patch('listings/{listing}/renew', [ListingController::class, 'renew'])->name('listings.renew');
            });
        });
});

Route::middleware(['auth', 'verified', 'role:superadmin,admin,manager,coordinator,insurance_manager'])
    ->prefix('adm')
    ->name('adm.')
    ->group(function () {
        Route::get('dashboard', [AnalyticsController::class, 'dashboardSummary'])->name('dashboard');
        Route::get('analytics/revenue', [AnalyticsController::class, 'revenueReport'])
            ->middleware('permission:view_reports')
            ->name('analytics.revenue');
        Route::get('analytics/listings', [AnalyticsController::class, 'listingStats'])
            ->middleware('permission:view_reports')
            ->name('analytics.listings');
        Route::get('analytics/conversions', [AnalyticsController::class, 'conversionStats'])
            ->middleware('permission:view_reports')
            ->name('analytics.conversions');
        Route::get('sellers', [AdminSellerProfileController::class, 'index'])
            ->middleware('permission:verify_sellers')
            ->name('sellers.index');
        Route::post('sellers/{sellerProfile}/approve', [AdminSellerProfileController::class, 'approve'])
            ->middleware('permission:verify_sellers')
            ->name('sellers.approve');
        Route::post('sellers/{sellerProfile}/reject', [AdminSellerProfileController::class, 'reject'])
            ->middleware('permission:verify_sellers')
            ->name('sellers.reject');
        Route::get('listings', [AdminListingController::class, 'index'])
            ->middleware('permission:approve_listings')
            ->name('listings.index');
        Route::post('listings/{listing}/approve', [AdminListingController::class, 'approve'])
            ->middleware('permission:approve_listings')
            ->name('listings.approve');
        Route::post('listings/{listing}/reject', [AdminListingController::class, 'reject'])
            ->middleware('permission:approve_listings')
            ->name('listings.reject');
        Route::get('transactions', [AdminTransactionController::class, 'index'])
            ->middleware('permission:view_reports')
            ->name('transactions.index');
        Route::post('transactions/{transaction}/mark-paid', [AdminTransactionController::class, 'markAsPaid'])
            ->middleware('permission:view_reports')
            ->name('transactions.mark-paid');
        Route::get('plans', [AdminPlanController::class, 'index'])
            ->middleware('permission:manage_roles')
            ->name('plans.index');
        Route::post('plans', [AdminPlanController::class, 'store'])
            ->middleware('permission:manage_roles')
            ->name('plans.store');
        Route::put('plans/{plan}', [AdminPlanController::class, 'update'])
            ->middleware('permission:manage_roles')
            ->name('plans.update');
        Route::get('payments', [AdminPaymentController::class, 'index'])
            ->middleware('permission:view_reports')
            ->name('payments.index');
        Route::post('payments/{payment}/confirm', [AdminPaymentController::class, 'confirm'])
            ->middleware('permission:view_reports')
            ->name('payments.confirm');
        Route::post('payments/{payment}/reject', [AdminPaymentController::class, 'reject'])
            ->middleware('permission:view_reports')
            ->name('payments.reject');
        Route::get('users', [UserController::class, 'index'])
            ->middleware('permission:manage_users')
            ->name('users.index');
        Route::get('landing', [AdminLandingPageController::class, 'edit'])
            ->middleware('permission:manage_landing')
            ->name('landing.edit');
        Route::put('landing', [AdminLandingPageController::class, 'update'])
            ->middleware('permission:manage_landing')
            ->name('landing.update');
        Route::get('landing-ads', [AdminLandingAdController::class, 'index'])
            ->middleware('permission:manage_landing')
            ->name('landing-ads.index');
        Route::post('landing-ads', [AdminLandingAdController::class, 'store'])
            ->middleware('permission:manage_landing')
            ->name('landing-ads.store');
        Route::put('landing-ads/{landingAd}', [AdminLandingAdController::class, 'update'])
            ->middleware('permission:manage_landing')
            ->name('landing-ads.update');
        Route::delete('landing-ads/{landingAd}', [AdminLandingAdController::class, 'destroy'])
            ->middleware('permission:manage_landing')
            ->name('landing-ads.destroy');
        Route::get('locations', [AdminLocationController::class, 'index'])
            ->middleware('permission:manage_locations')
            ->name('locations.index');
        Route::post('locations/regions', [AdminLocationController::class, 'storeRegion'])
            ->middleware('permission:manage_locations')
            ->name('locations.regions.store');
        Route::put('locations/regions/{region}', [AdminLocationController::class, 'updateRegion'])
            ->middleware('permission:manage_locations')
            ->name('locations.regions.update');
        Route::post('locations/provinces', [AdminLocationController::class, 'storeProvince'])
            ->middleware('permission:manage_locations')
            ->name('locations.provinces.store');
        Route::put('locations/provinces/{province}', [AdminLocationController::class, 'updateProvince'])
            ->middleware('permission:manage_locations')
            ->name('locations.provinces.update');
        Route::post('locations/municipalities', [AdminLocationController::class, 'storeMunicipality'])
            ->middleware('permission:manage_locations')
            ->name('locations.municipalities.store');
        Route::put('locations/municipalities/{municipality}', [AdminLocationController::class, 'updateMunicipality'])
            ->middleware('permission:manage_locations')
            ->name('locations.municipalities.update');
        Route::get('categories', [AdminCategoryController::class, 'index'])
            ->middleware('permission:manage_catalog')
            ->name('categories.index');
        Route::post('categories', [AdminCategoryController::class, 'store'])
            ->middleware('permission:manage_catalog')
            ->name('categories.store');
        Route::put('categories/{category}', [AdminCategoryController::class, 'update'])
            ->middleware('permission:manage_catalog')
            ->name('categories.update');
        Route::get('brands', [AdminBrandController::class, 'index'])
            ->middleware('permission:manage_catalog')
            ->name('brands.index');
        Route::post('brands', [AdminBrandController::class, 'store'])
            ->middleware('permission:manage_catalog')
            ->name('brands.store');
        Route::put('brands/{brand}', [AdminBrandController::class, 'update'])
            ->middleware('permission:manage_catalog')
            ->name('brands.update');
        Route::get('dealers', [AdminDealerProfileController::class, 'index'])
            ->middleware('permission:manage_dealers')
            ->name('dealers.index');
        Route::post('dealers/{dealerProfile}/approve', [AdminDealerProfileController::class, 'approve'])
            ->middleware('permission:manage_dealers')
            ->name('dealers.approve');
        Route::post('dealers/{dealerProfile}/reject', [AdminDealerProfileController::class, 'reject'])
            ->middleware('permission:manage_dealers')
            ->name('dealers.reject');
        Route::get('rentals', [AdminRentalController::class, 'index'])
            ->middleware('permission:manage_rentals')
            ->name('rentals.index');
        Route::post('rentals/{rentalUnit}/approve', [AdminRentalController::class, 'approve'])
            ->middleware('permission:manage_rentals')
            ->name('rentals.approve');
        Route::post('rentals/{rentalUnit}/reject', [AdminRentalController::class, 'reject'])
            ->middleware('permission:manage_rentals')
            ->name('rentals.reject');
        Route::get('rental-profiles', [AdminRentalProfileController::class, 'index'])->middleware('permission:manage_rentals')->name('rental-profiles.index');
        Route::post('rental-profiles/{rentalProfile}/approve', [AdminRentalProfileController::class, 'approve'])->middleware('permission:manage_rentals')->name('rental-profiles.approve');
        Route::post('rental-profiles/{rentalProfile}/reject', [AdminRentalProfileController::class, 'reject'])->middleware('permission:manage_rentals')->name('rental-profiles.reject');
        Route::get('financing/partners', [AdminFinancingPartnerController::class, 'index'])
            ->middleware('permission:manage_financing')
            ->name('financing.partners.index');
        Route::post('financing/partners/{financingPartner}/approve', [AdminFinancingPartnerController::class, 'approve'])
            ->middleware('permission:manage_financing')
            ->name('financing.partners.approve');
        Route::post('financing/partners/{financingPartner}/reject', [AdminFinancingPartnerController::class, 'reject'])
            ->middleware('permission:manage_financing')
            ->name('financing.partners.reject');
        Route::get('financing/applications', [AdminFinancingApplicationController::class, 'index'])
            ->middleware('permission:approve_financing')
            ->name('financing.applications.index');
        Route::patch('financing/applications/{financingApplication}/review', [AdminFinancingApplicationController::class, 'review'])
            ->middleware('permission:approve_financing')
            ->name('financing.applications.review');
        Route::get('drone-credentials', [AdminDroneCredentialController::class, 'index'])
            ->middleware('permission:review_drone_credentials')
            ->name('drone-credentials.index');
        Route::post('drone-credentials/{droneCredential}/approve', [AdminDroneCredentialController::class, 'approve'])
            ->middleware('permission:review_drone_credentials')
            ->name('drone-credentials.approve');
        Route::post('drone-credentials/{droneCredential}/reject', [AdminDroneCredentialController::class, 'reject'])
            ->middleware('permission:review_drone_credentials')
            ->name('drone-credentials.reject');
        Route::post('drone-credentials/{droneCredential}/suspend', [AdminDroneCredentialController::class, 'suspend'])
            ->middleware('permission:review_drone_credentials')
            ->name('drone-credentials.suspend');
    });

require __DIR__.'/settings.php';

Route::get('{category}', [SeoController::class, 'category'])
    ->where('category', '^(?!login$|register$|forgot-password$|reset-password$|email$|dashboard$|adm$|seller$|buyer$|listings$|categories$|notifications$|transactions$|payments$|settings$|storage$|up$)[a-z0-9-]+$')
    ->name('seo.category');
Route::get('{category}/{location}', [SeoController::class, 'categoryLocation'])
    ->where([
        'category' => '^(?!login$|register$|forgot-password$|reset-password$|email$|dashboard$|adm$|seller$|buyer$|listings$|categories$|notifications$|transactions$|payments$|settings$|storage$|up$)[a-z0-9-]+$',
        'location' => '[a-z0-9-]+',
    ])
    ->name('seo.category-location');
