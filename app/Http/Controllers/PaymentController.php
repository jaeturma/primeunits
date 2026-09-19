<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function show(Request $request, Payment $payment): Response
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        $payment->load('payable');

        return Inertia::render('payments/show', [
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'reference_number' => $payment->reference_number,
                'status' => $payment->status,
                'proof_url' => $payment->proof_file ? Storage::disk('public')->url($payment->proof_file) : null,
                'payable_type' => class_basename($payment->payable_type),
            ],
            'methods' => [
                ['value' => Payment::MethodCash, 'label' => 'Cash'],
                ['value' => Payment::MethodGcash, 'label' => 'GCash'],
                ['value' => Payment::MethodBankTransfer, 'label' => 'Bank Transfer'],
            ],
        ]);
    }

    public function store(StorePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('proof_file')) {
            $data['proof_file'] = $request->file('proof_file')->store('payments/proofs', 'public');
        }

        $payment->update($data);

        return back();
    }
}
