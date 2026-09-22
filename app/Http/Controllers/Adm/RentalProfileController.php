<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\RentalProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RentalProfileController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('adm/rental-profiles/index', [
            'profiles' => RentalProfile::query()->with('user:id,name,email')->latest()->paginate(20)->through(fn (RentalProfile $profile): array => [
                'id' => $profile->id,
                'business_name' => $profile->business_name,
                'status' => $profile->status,
                'user' => $profile->user,
                'business_registration_url' => $profile->business_registration_file ? route('rental-profiles.documents', [$profile, 'business_registration_file']) : null,
                'valid_id_url' => $profile->valid_id_file ? route('rental-profiles.documents', [$profile, 'valid_id_file']) : null,
            ]),
        ]);
    }

    public function approve(Request $request, RentalProfile $rentalProfile): RedirectResponse
    {
        if ($request->user()->hasRole('manager') && $rentalProfile->status === RentalProfile::StatusPending) {
            $rentalProfile->update(['status' => RentalProfile::StatusManagerValidated, 'manager_validated_by' => $request->user()->id, 'manager_validated_at' => now()]);
        } elseif ($request->user()->hasRole(['admin', 'superadmin']) && $rentalProfile->status === RentalProfile::StatusManagerValidated) {
            $rentalProfile->update(['status' => RentalProfile::StatusApproved, 'approved_by' => $request->user()->id, 'approved_at' => now()]);
        } else {
            abort(403, 'This application is not ready for your approval stage.');
        }

        return back();
    }

    public function reject(Request $request, RentalProfile $rentalProfile): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $rentalProfile->update(['status' => RentalProfile::StatusRejected, 'rejected_reason' => $request->string('reason')->toString()]);

        return back();
    }
}
