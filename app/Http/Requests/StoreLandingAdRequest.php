<?php

namespace App\Http\Requests;

use App\Models\LandingAd;
use App\Models\MembershipAccess;
use App\Support\SafeUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLandingAdRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! SafeUrl::isSafeDestination($value)) {
                    $fail('The destination URL must be a same-site path or an http/https link.');
                }
            }],
            'image_url' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'accent_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'show_in_feed' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'target_marketplace_mode' => ['nullable', Rule::in([
                MembershipAccess::LevelRegular,
                MembershipAccess::LevelSilver,
                MembershipAccess::LevelGold,
            ])],
            'review_status' => ['sometimes', Rule::in([
                LandingAd::ReviewPending,
                LandingAd::ReviewApproved,
                LandingAd::ReviewRejected,
            ])],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function adData(): array
    {
        return $this->safe()->except('image_file');
    }
}
