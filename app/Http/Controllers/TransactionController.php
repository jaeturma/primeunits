<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UploadTransactionProofRequest;
use App\Models\CommissionLog;
use App\Models\Lead;
use App\Models\Transaction;
use App\Services\CommissionService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function show(Request $request, Transaction $transaction): Response
    {
        abort_unless($this->canView($request, $transaction), 403);

        $transaction->load(['lead.buyer:id,name,email', 'lead.seller:id,name,email', 'listing.category:id,name,slug', 'commissionLog']);

        return Inertia::render('transactions/show', [
            'transaction' => $this->serializeTransaction($transaction),
        ]);
    }

    public function createFromLead(StoreTransactionRequest $request, Lead $lead, CommissionService $commissionService, NotificationService $notifications): RedirectResponse
    {
        $agreedPrice = (float) $request->input('agreed_price');
        $rate = $commissionService->defaultRate();

        $transaction = DB::transaction(function () use ($lead, $agreedPrice, $rate, $commissionService): Transaction {
            $transaction = Transaction::query()->create([
                'lead_id' => $lead->id,
                'listing_id' => $lead->listing_id,
                'agreed_price' => $agreedPrice,
                'commission_rate' => $rate,
                'commission_amount' => $commissionService->amount($agreedPrice, $rate),
                'status' => Transaction::StatusPending,
                'buyer_confirmed' => false,
                'seller_confirmed' => false,
            ]);

            $lead->update([
                'status' => Lead::StatusClosed,
                'closed_at' => now(),
            ]);

            return $transaction;
        });

        $notifications->send(
            user: $lead->buyer,
            event: 'transaction.created',
            title: 'Transaction created',
            message: "A transaction was created for inquiry {$lead->reference_code}.",
            url: "/transactions/{$transaction->id}",
        );

        return to_route('transactions.show', $transaction);
    }

    public function confirmByBuyer(Request $request, Transaction $transaction, NotificationService $notifications): RedirectResponse
    {
        abort_unless($request->user()?->id === $transaction->lead->buyer_id, 403);

        $this->confirm($transaction, 'buyer_confirmed');

        $notifications->send(
            user: $transaction->lead->seller,
            event: 'transaction.buyer_confirmed',
            title: 'Buyer confirmed transaction',
            message: "Buyer confirmed transaction {$transaction->lead->reference_code}.",
            url: "/transactions/{$transaction->id}",
        );

        return back();
    }

    public function confirmBySeller(Request $request, Transaction $transaction, NotificationService $notifications): RedirectResponse
    {
        abort_unless($request->user()?->id === $transaction->lead->seller_id, 403);

        $this->confirm($transaction, 'seller_confirmed');

        $notifications->send(
            user: $transaction->lead->buyer,
            event: 'transaction.seller_confirmed',
            title: 'Seller confirmed transaction',
            message: "Seller confirmed transaction {$transaction->lead->reference_code}.",
            url: "/transactions/{$transaction->id}",
        );

        return back();
    }

    public function uploadProof(UploadTransactionProofRequest $request, Transaction $transaction, NotificationService $notifications): RedirectResponse
    {
        $transaction->update([
            'proof_file' => $request->file('proof_file')->store('transactions/proofs', 'public'),
        ]);

        $recipient = $request->user()->id === $transaction->lead->buyer_id
            ? $transaction->lead->seller
            : $transaction->lead->buyer;

        $notifications->send(
            user: $recipient,
            event: 'transaction.proof_uploaded',
            title: 'Transaction proof uploaded',
            message: "Proof was uploaded for transaction {$transaction->lead->reference_code}.",
            url: "/transactions/{$transaction->id}",
        );

        return back();
    }

    private function confirm(Transaction $transaction, string $column): void
    {
        DB::transaction(function () use ($transaction, $column): void {
            $transaction->update([$column => true]);
            $transaction->refresh();

            if ($transaction->buyer_confirmed && $transaction->seller_confirmed) {
                $transaction->update([
                    'status' => Transaction::StatusConfirmed,
                    'confirmed_at' => now(),
                ]);

                $transaction->commissionLog()->firstOrCreate([], [
                    'amount' => $transaction->commission_amount,
                    'status' => CommissionLog::StatusUnpaid,
                ]);
            }
        });
    }

    private function canView(Request $request, Transaction $transaction): bool
    {
        $user = $request->user();

        return $user?->id === $transaction->lead->buyer_id
            || $user?->id === $transaction->lead->seller_id
            || $user?->hasRole(['superadmin', 'admin']) === true;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeTransaction(Transaction $transaction): array
    {
        return [
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
                'id' => $transaction->lead->id,
                'reference_code' => $transaction->lead->reference_code,
                'buyer' => $transaction->lead->buyer,
                'seller' => $transaction->lead->seller,
            ],
            'listing' => [
                'id' => $transaction->listing->id,
                'title' => $transaction->listing->title,
                'category' => $transaction->listing->category,
            ],
            'commission_log' => $transaction->commissionLog,
        ];
    }
}
