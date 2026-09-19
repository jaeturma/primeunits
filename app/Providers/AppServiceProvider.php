<?php

namespace App\Providers;

use App\Models\DealerProfile;
use App\Models\FinancingApplication;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\RentalUnit;
use App\Models\SellerProfile;
use App\Models\Transaction;
use App\Policies\DealerProfilePolicy;
use App\Policies\FinancingApplicationPolicy;
use App\Policies\LeadPolicy;
use App\Policies\ListingPolicy;
use App\Policies\RentalUnitPolicy;
use App\Policies\SellerProfilePolicy;
use App\Policies\TransactionPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePolicies();
        $this->configureDefaults();
    }

    protected function configurePolicies(): void
    {
        Gate::policy(Listing::class, ListingPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(SellerProfile::class, SellerProfilePolicy::class);
        Gate::policy(DealerProfile::class, DealerProfilePolicy::class);
        Gate::policy(RentalUnit::class, RentalUnitPolicy::class);
        Gate::policy(FinancingApplication::class, FinancingApplicationPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
