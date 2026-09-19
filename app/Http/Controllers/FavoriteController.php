<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class FavoriteController extends Controller
{
    public function index(Request $request): Response
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['listing' => fn ($q) => $q->with(['category:id,name,slug', 'images', 'boosts'])
                ->where('status', Listing::StatusApproved)])
            ->latest()
            ->get()
            ->filter(fn ($f) => $f->listing !== null)
            ->map(fn (Favorite $f) => $this->serializeListing($f->listing));

        return Inertia::render('buyer/favorites/index', [
            'favorites' => $favorites->values(),
        ]);
    }

    public function toggle(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->isApproved(), 404);

        $user = $request->user();

        $existing = Favorite::query()
            ->where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }

        Favorite::query()->create([
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);

        return response()->json(['favorited' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeListing(Listing $listing): array
    {
        $primaryImage = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first();

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'price' => $listing->price,
            'negotiable' => $listing->negotiable,
            'condition' => $listing->condition,
            'brand' => $listing->brand,
            'model' => $listing->model,
            'category' => $listing->category,
            'image_url' => $primaryImage ? Storage::disk('public')->url($primaryImage->path) : null,
            'created_at' => $listing->created_at?->toISOString(),
        ];
    }
}
