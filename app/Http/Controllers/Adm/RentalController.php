<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\RentalUnit;
use App\Services\ApprovalWorkflowService;
use App\Support\ResolvesRentalStockImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RentalController extends Controller
{
    use ResolvesRentalStockImage;

    public function index(Request $request): Response
    {
        $units = RentalUnit::query()
            ->with(['user:id,name,email', 'images', 'attachments'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $search = $request->string('q')->toString();
                $q->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->through(fn (RentalUnit $unit) => [
                'id' => $unit->id,
                'slug' => $unit->slug,
                'name' => $unit->name,
                'rental_type' => $unit->rental_type,
                'rental_type_label' => RentalUnit::rentalTypes()[$unit->rental_type] ?? $unit->rental_type,
                'brand' => $unit->brand,
                'model' => $unit->model,
                'year_model' => $unit->year_model,
                'price_per_day' => $unit->price_per_day,
                'status' => $unit->status,
                'region' => $unit->region,
                'municipality' => $unit->municipality,
                'views_count' => $unit->views_count,
                'image_url' => ($img = $unit->images->firstWhere('is_primary', true) ?? $unit->images->first())
                    ? Storage::disk('public')->url($img->path)
                    : $this->stockImageUrl($unit),
                'provider' => $unit->user ? ['name' => $unit->user->name, 'email' => $unit->user->email] : null,
                'created_at' => $unit->created_at?->toISOString(),
                'action_label' => app(ApprovalWorkflowService::class)->actionLabel($unit, $request->user()),
                'documents' => collect([
                    $unit->valid_id_file ? ['label' => 'Valid ID', 'url' => route('rental-units.documents', [$unit, 'valid_id_file'])] : null,
                    $unit->or_cr_file ? ['label' => 'OR/CR', 'url' => route('rental-units.documents', [$unit, 'or_cr_file'])] : null,
                    ...$unit->attachments->map(fn ($attachment): array => ['label' => $attachment->name, 'url' => $attachment->url()]),
                ])->filter()->values(),
            ]);

        return Inertia::render('adm/rentals/index', [
            'units' => $units,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function approve(Request $request, RentalUnit $rentalUnit, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $workflow->advance($rentalUnit, $request->user());

        return back()->with('success', 'Rental unit moved to the next approval stage.');
    }

    public function reject(Request $request, RentalUnit $rentalUnit): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $rentalUnit->update([
            'status' => RentalUnit::StatusRejected,
            'rejected_reason' => $request->string('reason')->toString(),
        ]);

        return back()->with('success', 'Rental unit rejected.');
    }
}
