<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $lead instanceof Lead
            && $this->user()?->id === $lead->seller_id
            && $this->user()?->sellerProfile?->isVerified() === true
            && ! $lead->transaction;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'agreed_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
        ];
    }
}
