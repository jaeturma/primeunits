<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLandingPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_landing') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hero_badge' => ['required', 'string', 'max:255'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['required', 'string', 'max:1000'],
            'search_title' => ['required', 'string', 'max:255'],
            'featured_title' => ['required', 'string', 'max:255'],
            'featured_subtitle' => ['required', 'string', 'max:255'],
            'results_title' => ['required', 'string', 'max:255'],
            'results_subtitle' => ['required', 'string', 'max:255'],
            'budget_title' => ['required', 'string', 'max:255'],
            'seller_cta_title' => ['required', 'string', 'max:255'],
            'seller_cta_body' => ['required', 'string', 'max:1000'],
            'seller_cta_button' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
