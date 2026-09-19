<?php

namespace App\Services;

use App\Models\CommissionLog;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, location?: string|null}  $filters
     * @return array<string, int|float>
     */
    public function dashboardSummary(array $filters = []): array
    {
        $totalLeads = $this->leadQuery($filters)->count();
        $totalTransactions = $this->transactionQuery($filters)->count();

        return [
            'total_users' => $this->dateFilter(User::query(), 'users.created_at', $filters)->count(),
            'total_sellers' => $this->dateFilter(User::query()->whereHas('roles', fn (EloquentBuilder $query): EloquentBuilder => $query->where('name', 'seller')), 'users.created_at', $filters)->count(),
            'total_buyers' => $this->dateFilter(User::query()->whereHas('roles', fn (EloquentBuilder $query): EloquentBuilder => $query->where('name', 'buyer')), 'users.created_at', $filters)->count(),
            'total_listings' => $this->listingQuery($filters)->count(),
            'active_listings' => $this->listingQuery($filters)->where('status', Listing::StatusApproved)->count(),
            'total_leads' => $totalLeads,
            'total_transactions' => $totalTransactions,
            'conversion_rate' => $this->percentage($totalTransactions, $totalLeads),
        ];
    }

    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, location?: string|null}  $filters
     * @return array<string, float>
     */
    public function revenueReport(array $filters = []): array
    {
        $commissionQuery = $this->commissionQuery($filters);

        return [
            'total_commission' => round((float) (clone $commissionQuery)->sum('commission_logs.amount'), 2),
            'total_paid_commission' => round((float) (clone $commissionQuery)->where('commission_logs.status', CommissionLog::StatusPaid)->sum('commission_logs.amount'), 2),
            'pending_commission' => round((float) (clone $commissionQuery)->where('commission_logs.status', CommissionLog::StatusUnpaid)->sum('commission_logs.amount'), 2),
            'total_payments_received' => round((float) $this->dateFilter(Payment::query(), 'payments.created_at', $filters)
                ->where('status', Payment::StatusConfirmed)
                ->sum('amount'), 2),
        ];
    }

    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, location?: string|null}  $filters
     * @return array{listings_per_category: list<array{id: int, name: string, total: int}>, top_listings: list<array{id: int, title: string, leads_count: int}>, top_sellers: list<array{id: int, name: string, closed_transactions: int, closed_value: float}>}
     */
    public function listingStats(array $filters = []): array
    {
        $listingsPerCategory = $this->listingQuery($filters)
            ->join('categories', 'categories.id', '=', 'listings.category_id')
            ->select('categories.id', 'categories.name', DB::raw('COUNT(*) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        $topListings = $this->listingQuery($filters)
            ->leftJoin('leads', function (mixed $join) use ($filters): void {
                $join->on('leads.listing_id', '=', 'listings.id');
                $this->dateFilter($join, 'leads.created_at', $filters);
            })
            ->select('listings.id', 'listings.title', DB::raw('COUNT(leads.id) as leads_count'))
            ->groupBy('listings.id', 'listings.title')
            ->orderByDesc('leads_count')
            ->orderBy('listings.title')
            ->limit(5)
            ->get()
            ->map(fn (object $row): array => [
                'id' => (int) $row->id,
                'title' => (string) $row->title,
                'leads_count' => (int) $row->leads_count,
            ])
            ->all();

        $topSellers = $this->transactionQuery($filters)
            ->where('transactions.status', Transaction::StatusConfirmed)
            ->join('users', 'users.id', '=', 'listings.user_id')
            ->select('users.id', 'users.name', DB::raw('COUNT(transactions.id) as closed_transactions'), DB::raw('COALESCE(SUM(transactions.agreed_price), 0) as closed_value'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('closed_transactions')
            ->orderByDesc('closed_value')
            ->limit(5)
            ->get()
            ->map(fn (object $row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'closed_transactions' => (int) $row->closed_transactions,
                'closed_value' => round((float) $row->closed_value, 2),
            ])
            ->all();

        return [
            'listings_per_category' => $listingsPerCategory,
            'top_listings' => $topListings,
            'top_sellers' => $topSellers,
        ];
    }

    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, location?: string|null}  $filters
     * @return array<string, int|float>
     */
    public function conversionStats(array $filters = []): array
    {
        $totalLeads = $this->leadQuery($filters)->count();
        $totalTransactions = $this->transactionQuery($filters)->count();
        $averageSeconds = (float) $this->transactionQuery($filters)
            ->join('leads', 'leads.id', '=', 'transactions.lead_id')
            ->whereNotNull('transactions.created_at')
            ->avg(DB::raw($this->averageTimeToCloseExpression()));

        return [
            'total_leads' => $totalLeads,
            'total_transactions' => $totalTransactions,
            'leads_to_transactions_rate' => $this->percentage($totalTransactions, $totalLeads),
            'average_time_to_close_hours' => round($averageSeconds / 3600, 1),
        ];
    }

    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, location?: string|null}  $filters
     * @return EloquentBuilder<Listing>
     */
    private function listingQuery(array $filters): EloquentBuilder
    {
        $query = Listing::query();

        $this->dateFilter($query, 'listings.created_at', $filters);
        $this->listingFilter($query, $filters);

        return $query;
    }

    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, location?: string|null}  $filters
     * @return EloquentBuilder<Lead>
     */
    private function leadQuery(array $filters): EloquentBuilder
    {
        $query = Lead::query()
            ->join('listings', 'listings.id', '=', 'leads.listing_id');

        $this->dateFilter($query, 'leads.created_at', $filters);
        $this->listingFilter($query, $filters);

        return $query;
    }

    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, location?: string|null}  $filters
     * @return EloquentBuilder<Transaction>
     */
    private function transactionQuery(array $filters): EloquentBuilder
    {
        $query = Transaction::query()
            ->join('listings', 'listings.id', '=', 'transactions.listing_id');

        $this->dateFilter($query, 'transactions.created_at', $filters);
        $this->listingFilter($query, $filters);

        return $query;
    }

    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, location?: string|null}  $filters
     * @return EloquentBuilder<CommissionLog>
     */
    private function commissionQuery(array $filters): EloquentBuilder
    {
        $query = CommissionLog::query()
            ->join('transactions', 'transactions.id', '=', 'commission_logs.transaction_id')
            ->join('listings', 'listings.id', '=', 'transactions.listing_id');

        $this->dateFilter($query, 'commission_logs.created_at', $filters);
        $this->listingFilter($query, $filters);

        return $query;
    }

    /**
     * @param  EloquentBuilder<object>|QueryBuilder|mixed  $query
     * @param  array{from?: string|null, to?: string|null}  $filters
     * @return EloquentBuilder<object>|QueryBuilder|mixed
     */
    private function dateFilter(mixed $query, string $column, array $filters): mixed
    {
        if (! empty($filters['from'])) {
            $query->whereDate($column, '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate($column, '<=', $filters['to']);
        }

        return $query;
    }

    /**
     * @param  EloquentBuilder<object>|QueryBuilder  $query
     * @param  array{category_id?: int|null, location?: string|null}  $filters
     */
    private function listingFilter(EloquentBuilder|QueryBuilder $query, array $filters): void
    {
        if (! empty($filters['category_id'])) {
            $query->where('listings.category_id', $filters['category_id']);
        }

        if (! empty($filters['location'])) {
            $query->where(function (EloquentBuilder|QueryBuilder $query) use ($filters): void {
                $query->where('listings.region', $filters['location'])
                    ->orWhere('listings.province', $filters['location']);
            });
        }
    }

    private function averageTimeToCloseExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%s', COALESCE(transactions.confirmed_at, transactions.created_at)) - strftime('%s', leads.created_at)",
            'pgsql' => 'EXTRACT(EPOCH FROM (COALESCE(transactions.confirmed_at, transactions.created_at) - leads.created_at))',
            default => 'TIMESTAMPDIFF(SECOND, leads.created_at, COALESCE(transactions.confirmed_at, transactions.created_at))',
        };
    }

    private function percentage(int $part, int $whole): float
    {
        if ($whole === 0) {
            return 0.0;
        }

        return round(($part / $whole) * 100, 1);
    }
}
