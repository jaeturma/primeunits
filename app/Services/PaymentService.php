<?php

namespace App\Services;

use App\Models\ListingBoost;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function confirm(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment): Payment {
            $payment->update([
                'status' => Payment::StatusConfirmed,
                'paid_at' => now(),
            ]);

            $payable = $payment->payable;
            $durationDays = max(1, (int) ($payable->plan->duration_days ?? 1));

            if ($payable instanceof ListingBoost) {
                $payable->update([
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($durationDays),
                    'is_active' => true,
                ]);
            }

            if ($payable instanceof Subscription) {
                $payable->update([
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($durationDays),
                    'status' => Subscription::StatusActive,
                ]);
            }

            return $payment->refresh();
        });
    }

    public function reject(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment): Payment {
            $payment->update([
                'status' => Payment::StatusRejected,
            ]);

            $payable = $payment->payable;

            if ($payable instanceof ListingBoost) {
                $payable->update(['is_active' => false]);
            }

            if ($payable instanceof Subscription) {
                $payable->update(['status' => Subscription::StatusCancelled]);
            }

            return $payment->refresh();
        });
    }
}
