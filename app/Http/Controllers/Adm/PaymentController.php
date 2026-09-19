<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                Payment::StatusPending,
                Payment::StatusConfirmed,
                Payment::StatusRejected,
            ])],
        ]);

        return Inertia::render('adm/payments/index', [
            'payments' => Payment::query()
                ->with(['user:id,name,email', 'payable'])
                ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
                ->latest()
                ->get()
                ->map(fn (Payment $payment): array => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'method' => $payment->method,
                    'reference_number' => $payment->reference_number,
                    'status' => $payment->status,
                    'proof_url' => $payment->proof_file ? Storage::disk('public')->url($payment->proof_file) : null,
                    'payable_type' => class_basename($payment->payable_type),
                    'user' => $payment->user,
                    'created_at' => $payment->created_at?->toISOString(),
                ]),
            'filters' => [
                'status' => $filters['status'] ?? '',
            ],
            'statuses' => [
                ['value' => '', 'label' => 'All statuses'],
                ['value' => Payment::StatusPending, 'label' => 'Pending'],
                ['value' => Payment::StatusConfirmed, 'label' => 'Confirmed'],
                ['value' => Payment::StatusRejected, 'label' => 'Rejected'],
            ],
        ]);
    }

    public function confirm(Payment $payment, PaymentService $paymentService, NotificationService $notifications): RedirectResponse
    {
        abort_unless($payment->status === Payment::StatusPending, 403);

        $paymentService->confirm($payment);

        $notifications->send(
            user: $payment->user,
            event: 'payment.confirmed',
            title: 'Payment confirmed',
            message: "Your payment of PHP {$payment->amount} has been confirmed.",
            url: '/seller/monetization',
        );

        return back();
    }

    public function reject(Payment $payment, PaymentService $paymentService, NotificationService $notifications): RedirectResponse
    {
        abort_unless($payment->status === Payment::StatusPending, 403);

        $paymentService->reject($payment);

        $notifications->send(
            user: $payment->user,
            event: 'payment.rejected',
            title: 'Payment rejected',
            message: "Your payment of PHP {$payment->amount} was rejected.",
            url: "/payments/{$payment->id}",
        );

        return back();
    }
}
