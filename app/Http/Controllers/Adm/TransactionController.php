<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\CommissionLog;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                Transaction::StatusPending,
                Transaction::StatusConfirmed,
                Transaction::StatusDisputed,
            ])],
        ]);

        $transactions = Transaction::query()
            ->with(['lead.buyer:id,name,email', 'lead.seller:id,name,email', 'listing:id,title', 'commissionLog'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'id' => $transaction->id,
                'agreed_price' => $transaction->agreed_price,
                'commission_rate' => $transaction->commission_rate,
                'commission_amount' => $transaction->commission_amount,
                'status' => $transaction->status,
                'status_label' => $transaction->statusLabel(),
                'buyer_confirmed' => $transaction->buyer_confirmed,
                'seller_confirmed' => $transaction->seller_confirmed,
                'confirmed_at' => $transaction->confirmed_at?->toISOString(),
                'proof_url' => $transaction->proof_file ? Storage::disk('public')->url($transaction->proof_file) : null,
                'lead' => [
                    'reference_code' => $transaction->lead->reference_code,
                    'buyer' => $transaction->lead->buyer,
                    'seller' => $transaction->lead->seller,
                ],
                'listing' => $transaction->listing,
                'commission_log' => $transaction->commissionLog,
            ]);

        return Inertia::render('adm/transactions/index', [
            'transactions' => $transactions,
            'filters' => [
                'status' => $filters['status'] ?? '',
            ],
            'statuses' => [
                ['value' => '', 'label' => 'All statuses'],
                ['value' => Transaction::StatusPending, 'label' => 'Pending'],
                ['value' => Transaction::StatusConfirmed, 'label' => 'Confirmed'],
                ['value' => Transaction::StatusDisputed, 'label' => 'Disputed'],
            ],
        ]);
    }

    public function markAsPaid(Transaction $transaction): RedirectResponse
    {
        abort_unless($transaction->commissionLog, 404);

        $transaction->commissionLog->update([
            'status' => CommissionLog::StatusPaid,
            'paid_at' => now(),
        ]);

        return back();
    }
}
