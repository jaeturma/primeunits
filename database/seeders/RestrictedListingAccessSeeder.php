<?php

namespace Database\Seeders;

use App\Models\ConfidentialityAcknowledgement;
use App\Models\ConfidentialityNotice;
use App\Models\Listing;
use App\Models\ListingAccessRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: an approved, a pending, and a revoked listing
 * access request, demonstrating the invitation-only / restricted
 * listing access request lifecycle.
 *
 * Requires MembershipDemoUserSeeder, PremiumListingSeeder, and
 * ConfidentialityNoticeSeeder to have run first.
 */
class RestrictedListingAccessSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([MembershipDemoUserSeeder::class, PremiumListingSeeder::class, ConfidentialityNoticeSeeder::class]);

        $goldBuyer = User::query()->where('email', 'gold.buyer@primeunits.test')->firstOrFail();
        $goldPending = User::query()->where('email', 'gold.pending@primeunits.test')->firstOrFail();
        $goldExpired = User::query()->where('email', 'gold.expired@primeunits.test')->firstOrFail();

        $privateJet = Listing::query()->where('title', 'Private Jet — Price on Request')->firstOrFail();
        $motorYacht = Listing::query()->where('title', 'Motor Yacht, Turnkey Ready')->firstOrFail();

        $notice = ConfidentialityNotice::active();

        $this->grantedRequest($privateJet, $goldBuyer, ListingAccessRequest::StatusApproved, $notice, expiresInDays: 30);
        $this->grantedRequest($privateJet, $goldPending, ListingAccessRequest::StatusPending, $notice, expiresInDays: null);
        $this->grantedRequest($motorYacht, $goldExpired, ListingAccessRequest::StatusRevoked, $notice, expiresInDays: null);
    }

    private function grantedRequest(Listing $listing, User $user, string $status, ?ConfidentialityNotice $notice, ?int $expiresInDays): void
    {
        $accessRequest = ListingAccessRequest::query()->updateOrCreate(
            ['listing_id' => $listing->id, 'user_id' => $user->id],
            [
                'status' => $status,
                'message' => 'Demo access request for a restricted Gold listing.',
                'reviewer_id' => $status !== ListingAccessRequest::StatusPending ? $listing->user_id : null,
                'reviewed_at' => $status !== ListingAccessRequest::StatusPending ? now()->subDays(3) : null,
                'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
            ],
        );

        if ($status === ListingAccessRequest::StatusPending) {
            return;
        }

        ConfidentialityAcknowledgement::query()->updateOrCreate(
            ['listing_access_request_id' => $accessRequest->id, 'user_id' => $user->id],
            [
                'confidentiality_notice_id' => $notice?->id,
                'notice_version' => $notice?->version ?? 'unversioned',
                'notice_text' => $notice?->body ?? ConfidentialityNotice::DEFAULT_NOTICE_TEXT,
                'acknowledged_at' => now()->subDays(4),
                'ip_address' => '127.0.0.1',
            ],
        );
    }
}
