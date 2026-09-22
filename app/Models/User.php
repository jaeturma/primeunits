<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'username', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * @return HasOne<SellerProfile, $this>
     */
    public function sellerProfile(): HasOne
    {
        return $this->hasOne(SellerProfile::class);
    }

    /**
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function buyerLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'buyer_id');
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function sellerLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'seller_id');
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasOne<NotificationPreference, $this>
     */
    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * @return HasOne<DealerProfile, $this>
     */
    public function dealerProfile(): HasOne
    {
        return $this->hasOne(DealerProfile::class);
    }

    /**
     * @return HasOne<MembershipAccess, $this>
     */
    public function membershipAccess(): HasOne
    {
        return $this->hasOne(MembershipAccess::class);
    }

    /**
     * @return HasMany<MembershipApplication, $this>
     */
    public function membershipApplications(): HasMany
    {
        return $this->hasMany(MembershipApplication::class);
    }

    /**
     * The user's membership access record, lazily created with a safe
     * "no access" default (none/none/none) the first time it's needed —
     * mirrors notificationPreferenceOrDefault() below. Never grants
     * access itself; see MembershipAccessService for the only paths that
     * raise buyer_access_level/seller_access_level.
     */
    public function membershipAccessOrDefault(): MembershipAccess
    {
        return $this->membershipAccess()->firstOrCreate([], [
            'identity_verification_level' => MembershipAccess::IdentityNone,
            'buyer_access_level' => MembershipAccess::LevelNone,
            'seller_access_level' => MembershipAccess::LevelNone,
        ]);
    }

    /**
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * @return HasMany<RentalUnit, $this>
     */
    public function rentalUnits(): HasMany
    {
        return $this->hasMany(RentalUnit::class);
    }

    /** @return HasOne<RentalProfile, $this> */
    public function rentalProfile(): HasOne
    {
        return $this->hasOne(RentalProfile::class);
    }

    /**
     * @return HasMany<RentalBooking, $this>
     */
    public function rentalBookings(): HasMany
    {
        return $this->hasMany(RentalBooking::class, 'renter_id');
    }

    /**
     * @return HasMany<DronePilotCredential, $this>
     */
    public function droneCredentials(): HasMany
    {
        return $this->hasMany(DronePilotCredential::class);
    }

    public function latestDroneCredential(): ?DronePilotCredential
    {
        return $this->droneCredentials()->latest()->first();
    }

    public function hasVerifiedDroneCredential(): bool
    {
        return $this->latestDroneCredential()?->isActiveAndUnexpired() ?? false;
    }

    /**
     * @return HasOne<FinancingPartner, $this>
     */
    public function financingPartner(): HasOne
    {
        return $this->hasOne(FinancingPartner::class);
    }

    /**
     * @return HasMany<FinancingApplication, $this>
     */
    public function financingApplications(): HasMany
    {
        return $this->hasMany(FinancingApplication::class);
    }

    public function notificationPreferenceOrDefault(): NotificationPreference
    {
        return $this->notificationPreference()->firstOrCreate([], [
            'database_enabled' => true,
            'email_enabled' => false,
            'sms_enabled' => false,
        ]);
    }

    /**
     * @return Collection<int, Permission>
     */
    public function permissions(): Collection
    {
        $this->loadMissing('roles.permissions');

        return $this->roles
            ->flatMap(fn (Role $role): Collection => $role->permissions)
            ->unique('id')
            ->values();
    }

    public function hasRole(string|array $role): bool
    {
        $roles = (array) $role;

        $this->loadMissing('roles');

        return $this->roles->contains(fn (Role $assignedRole): bool => in_array($assignedRole->name, $roles, true));
    }

    public function hasPermission(string|array $permission): bool
    {
        $permissions = (array) $permission;

        return $this->permissions()->contains(fn (Permission $assignedPermission): bool => in_array($assignedPermission->name, $permissions, true));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
