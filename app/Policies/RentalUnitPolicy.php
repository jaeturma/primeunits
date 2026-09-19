<?php

namespace App\Policies;

use App\Models\RentalUnit;
use App\Models\User;

class RentalUnitPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, RentalUnit $rentalUnit): bool
    {
        if ($rentalUnit->isApproved()) {
            return true;
        }

        return $user?->id === $rentalUnit->user_id || $user?->hasPermission('approve_listings') === true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('rental_provider');
    }

    public function update(User $user, RentalUnit $rentalUnit): bool
    {
        return $user->id === $rentalUnit->user_id;
    }

    public function delete(User $user, RentalUnit $rentalUnit): bool
    {
        return $user->id === $rentalUnit->user_id || $user->hasPermission('approve_listings');
    }

    public function approve(User $user, RentalUnit $rentalUnit): bool
    {
        return $user->hasPermission('approve_listings');
    }
}
