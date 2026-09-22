<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\DealerProfile;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DealerProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $dealers = DealerProfile::query()
            ->with(['user:id,name,email', 'attachments'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('q'), fn ($q) => $q->where('business_name', 'like', '%'.$request->string('q')->toString().'%'))
            ->latest()
            ->paginate(20)
            ->through(fn (DealerProfile $d) => [
                'id' => $d->id,
                'slug' => $d->slug,
                'business_name' => $d->business_name,
                'contact_number' => $d->contact_number,
                'email' => $d->email,
                'status' => $d->status,
                'status_label' => $d->statusLabel(),
                'region' => $d->region,
                'accreditation_number' => $d->accreditation_number,
                'user' => $d->user ? ['id' => $d->user->id, 'name' => $d->user->name, 'email' => $d->user->email] : null,
                'verified_at' => $d->verified_at?->toISOString(),
                'rejected_reason' => $d->rejected_reason,
                'created_at' => $d->created_at?->toISOString(),
                'action_label' => match (true) {
                    $d->status === DealerProfile::StatusPending && $request->user()->hasRole('manager') => 'Validate',
                    $d->status === DealerProfile::StatusManagerValidated && $request->user()->hasRole(['admin', 'superadmin']) => 'Approve',
                    default => null,
                },
                'documents' => collect([
                    $d->accreditation_file ? ['label' => 'Accreditation', 'url' => route('dealers.documents', [$d, 'accreditation_file'])] : null,
                    ...$d->attachments->map(fn ($attachment): array => ['label' => $attachment->name, 'url' => $attachment->url()]),
                ])->filter()->values(),
            ]);

        return Inertia::render('adm/dealers/index', [
            'dealers' => $dealers,
            'filters' => [
                'status' => $request->string('status')->toString(),
                'q' => $request->string('q')->toString(),
            ],
        ]);
    }

    public function approve(Request $request, DealerProfile $dealerProfile): RedirectResponse
    {
        $this->authorize('approve', $dealerProfile);

        if ($request->user()->hasRole('manager') && $dealerProfile->status === DealerProfile::StatusPending) {
            abort_unless($dealerProfile->accreditation_file && $dealerProfile->attachments()->exists(), 422, 'Business registration and supporting documents are required.');
            $dealerProfile->update(['status' => DealerProfile::StatusManagerValidated, 'manager_validated_by' => $request->user()->id, 'manager_validated_at' => now()]);

            return back()->with('success', 'Dealer documents validated and forwarded to admin.');
        }

        abort_unless($request->user()->hasRole(['admin', 'superadmin']) && $dealerProfile->status === DealerProfile::StatusManagerValidated, 403);

        $dealerProfile->update(['status' => DealerProfile::StatusVerified, 'verified_at' => now(), 'approved_by' => $request->user()->id, 'rejected_reason' => null]);

        if (! $dealerProfile->user->hasRole('dealer')) {
            $dealerProfile->user->roles()->attach(
                Role::query()->where('name', 'dealer')->first()?->id
            );
        }

        return back()->with('success', 'Dealer approved.');
    }

    public function reject(Request $request, DealerProfile $dealerProfile): RedirectResponse
    {
        $this->authorize('reject', $dealerProfile);

        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $dealerProfile->update([
            'status' => DealerProfile::StatusRejected,
            'verified_at' => null,
            'rejected_reason' => $request->string('reason')->toString(),
        ]);

        return back()->with('success', 'Dealer rejected.');
    }
}
