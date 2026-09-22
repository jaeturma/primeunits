<?php

namespace App\Http\Controllers;

use App\Models\DealerProfile;
use App\Models\FinancingApplication;
use App\Models\FinancingPartner;
use App\Models\Listing;
use App\Models\RentalProfile;
use App\Models\RentalUnit;
use App\Models\ResourceAttachment;
use App\Models\SellerProfile;
use App\Support\ServesPrivateDocuments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Authorized access to identity, ownership, and registration documents
 * (government IDs, selfies, permits, OR/CR, business accreditation) that
 * must never be reachable by a guessable public-disk URL — see the
 * `local` disk switch in StoreSellerProfileRequest, DealerProfileController,
 * StoreListingRequest, RentalProfileController, and RentalController.
 *
 * Every method here follows the same rule: the record's owner, or a
 * reviewer holding the permission for that specific review workflow, and
 * no one else. A field not in that model's whitelist 404s rather than
 * exposing an arbitrary column.
 */
class SecureDocumentController extends Controller
{
    use ServesPrivateDocuments;

    private const array SELLER_FIELDS = ['permit_file', 'accreditation_file', 'representative_id_file', 'valid_id_file', 'selfie_file'];

    private const array DEALER_FIELDS = ['accreditation_file'];

    private const array LISTING_FIELDS = ['valid_id_file', 'or_cr_file'];

    private const array RENTAL_PROFILE_FIELDS = ['business_registration_file', 'valid_id_file'];

    private const array RENTAL_UNIT_FIELDS = ['valid_id_file', 'or_cr_file'];

    public function sellerProfile(Request $request, SellerProfile $sellerProfile, string $field): StreamedResponse
    {
        abort_unless(in_array($field, self::SELLER_FIELDS, true), 404);
        $this->authorizeOwnerOrPermission($request, $sellerProfile->user_id, 'verify_sellers');

        return $this->streamPrivateDocument($sellerProfile->{$field});
    }

    public function dealerProfile(Request $request, DealerProfile $dealerProfile, string $field): StreamedResponse
    {
        abort_unless(in_array($field, self::DEALER_FIELDS, true), 404);
        $this->authorizeOwnerOrPermission($request, $dealerProfile->user_id, 'manage_dealers');

        return $this->streamPrivateDocument($dealerProfile->{$field});
    }

    public function listing(Request $request, Listing $listing, string $field): StreamedResponse
    {
        abort_unless(in_array($field, self::LISTING_FIELDS, true), 404);
        $this->authorizeOwnerOrPermission($request, $listing->user_id, 'approve_listings');

        return $this->streamPrivateDocument($listing->{$field});
    }

    public function rentalProfile(Request $request, RentalProfile $rentalProfile, string $field): StreamedResponse
    {
        abort_unless(in_array($field, self::RENTAL_PROFILE_FIELDS, true), 404);
        $this->authorizeOwnerOrPermission($request, $rentalProfile->user_id, 'manage_rentals');

        return $this->streamPrivateDocument($rentalProfile->{$field});
    }

    public function rentalUnit(Request $request, RentalUnit $rentalUnit, string $field): StreamedResponse
    {
        abort_unless(in_array($field, self::RENTAL_UNIT_FIELDS, true), 404);
        $this->authorizeOwnerOrPermission($request, $rentalUnit->user_id, 'manage_rentals');

        return $this->streamPrivateDocument($rentalUnit->{$field});
    }

    public function attachment(Request $request, ResourceAttachment $resourceAttachment): StreamedResponse
    {
        $owner = $resourceAttachment->attachable;
        abort_if(! ($owner instanceof Model) || ! isset($owner->user_id), 404);

        $permission = match ($owner::class) {
            SellerProfile::class => 'verify_sellers',
            DealerProfile::class => 'manage_dealers',
            Listing::class => 'approve_listings',
            RentalUnit::class => 'manage_rentals',
            FinancingApplication::class, FinancingPartner::class => 'approve_financing',
            default => null,
        };

        abort_if($permission === null, 404);
        $this->authorizeOwnerOrPermission($request, (int) $owner->user_id, $permission);

        return $this->streamPrivateDocument($resourceAttachment->path);
    }

    private function authorizeOwnerOrPermission(Request $request, int $ownerId, string $permission): void
    {
        $user = $request->user();

        abort_if($user === null, 403);
        abort_unless($user->id === $ownerId || $user->hasPermission($permission), 403);
    }
}
