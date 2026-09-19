<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FinancingApplication extends Model
{
    public const StatusSubmitted = 'submitted';
    public const StatusUnderReview = 'under_review';
    public const StatusApproved = 'approved';
    public const StatusRejected = 'rejected';

    public const EmploymentEmployed = 'employed';
    public const EmploymentSelfEmployed = 'self_employed';
    public const EmploymentBusinessOwner = 'business_owner';
    public const EmploymentOthers = 'others';

    protected $fillable = [
        'user_id', 'listing_id', 'financing_partner_id', 'financing_product_id',
        'reference_code', 'full_name', 'contact_number', 'employment_type',
        'monthly_income', 'unit_price', 'requested_amount', 'down_payment',
        'preferred_term_months', 'notes', 'status', 'submitted_at', 'reviewed_at', 'reviewer_notes',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'monthly_income' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'requested_amount' => 'decimal:2',
            'down_payment' => 'decimal:2',
            'preferred_term_months' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function financingPartner(): BelongsTo
    {
        return $this->belongsTo(FinancingPartner::class);
    }

    public function financingProduct(): BelongsTo
    {
        return $this->belongsTo(FinancingProduct::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(ResourceAttachment::class, 'attachable')->latest();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusUnderReview => 'Under Review',
            self::StatusApproved => 'Approved',
            self::StatusRejected => 'Rejected',
            default => 'Submitted',
        };
    }

    /** @return array<string,string> */
    public static function employmentTypes(): array
    {
        return [
            self::EmploymentEmployed => 'Employed',
            self::EmploymentSelfEmployed => 'Self-Employed',
            self::EmploymentBusinessOwner => 'Business Owner',
            self::EmploymentOthers => 'Others',
        ];
    }

    public function estimatedMonthlyPayment(): float|null
    {
        if (! $this->requested_amount || ! $this->preferred_term_months) {
            return null;
        }

        $partner = $this->financingProduct ?? null;
        $rate = $partner ? ((float) $partner->interest_rate_min / 100 / 12) : 0;

        if ($rate <= 0) {
            return (float) $this->requested_amount / $this->preferred_term_months;
        }

        $n = $this->preferred_term_months;
        $p = (float) $this->requested_amount;

        return round($p * ($rate * pow(1 + $rate, $n)) / (pow(1 + $rate, $n) - 1), 2);
    }
}
