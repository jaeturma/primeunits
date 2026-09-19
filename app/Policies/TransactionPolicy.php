<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->lead->buyer_id
            || $user->id === $transaction->lead->seller_id
            || $user->hasPermission('view_reports');
    }

    public function create(User $user): bool
    {
        return $user->sellerProfile?->isVerified() === true
            || $user->dealerProfile?->isVerified() === true;
    }

    public function confirmBuyer(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->lead->buyer_id && ! $transaction->buyer_confirmed;
    }

    public function confirmSeller(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->lead->seller_id && ! $transaction->seller_confirmed;
    }

    public function uploadProof(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->lead->buyer_id
            || $user->id === $transaction->lead->seller_id;
    }
}
