<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Listing;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SellerAnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $listingIds = Listing::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        $totalListings = $listingIds->count();
        $activeListings = Listing::query()
            ->where('user_id', $user->id)
            ->where('status', Listing::StatusApproved)
            ->notExpired()
            ->count();

        $totalViews = Listing::query()
            ->where('user_id', $user->id)
            ->sum('views_count');

        $totalLeads = Lead::query()
            ->where('seller_id', $user->id)
            ->count();

        $closedLeads = Lead::query()
            ->where('seller_id', $user->id)
            ->where('status', Lead::StatusClosed)
            ->count();

        $totalTransactions = Transaction::query()
            ->whereIn('listing_id', $listingIds)
            ->where('status', Transaction::StatusConfirmed)
            ->count();

        $totalRevenue = (float) Transaction::query()
            ->whereIn('listing_id', $listingIds)
            ->where('status', Transaction::StatusConfirmed)
            ->sum('agreed_price');

        $topListings = Listing::query()
            ->where('user_id', $user->id)
            ->withCount('leads')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get(['id', 'title', 'views_count', 'status', 'price'])
            ->map(fn (Listing $l) => [
                'id' => $l->id,
                'title' => $l->title,
                'views_count' => $l->views_count,
                'leads_count' => $l->leads_count,
                'status' => $l->status,
                'price' => $l->price,
            ]);

        $recentLeads = Lead::query()
            ->where('seller_id', $user->id)
            ->with('listing:id,title', 'buyer:id,name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Lead $lead) => [
                'id' => $lead->id,
                'reference_code' => $lead->reference_code,
                'status' => $lead->status,
                'status_label' => $lead->statusLabel(),
                'listing_title' => $lead->listing?->title,
                'buyer_name' => $lead->buyer?->name,
                'created_at' => $lead->created_at?->toISOString(),
            ]);

        return Inertia::render('seller/analytics/index', [
            'stats' => [
                'total_listings' => $totalListings,
                'active_listings' => $activeListings,
                'total_views' => (int) $totalViews,
                'total_leads' => $totalLeads,
                'closed_leads' => $closedLeads,
                'total_transactions' => $totalTransactions,
                'total_revenue' => $totalRevenue,
                'conversion_rate' => $totalLeads > 0 ? round(($closedLeads / $totalLeads) * 100, 1) : 0,
            ],
            'top_listings' => $topListings,
            'recent_leads' => $recentLeads,
        ]);
    }
}
