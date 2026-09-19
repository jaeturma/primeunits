<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSellerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $profile = $user?->sellerProfile;

        return $user?->hasRole('seller') === true
            && (! $profile || $profile->isRejected());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seller_type' => ['required', Rule::in(['individual', 'business'])],
            'business_name' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string', 'max:2000'],
            'permit_number' => ['nullable', 'string', 'max:255'],
            'permit_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'accreditation' => ['nullable', 'string', 'max:255'],
            'accreditation_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'representative_name' => ['nullable', 'string', 'max:255'],
            'representative_contact' => ['nullable', 'string', 'max:50'],
            'representative_id_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'valid_id_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'selfie_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'attachments' => ['array', 'max:10'],
            'attachments.*' => ['file', 'mimes:pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        return $this->safe()->except([
            'permit_file',
            'accreditation_file',
            'representative_id_file',
            'valid_id_file',
            'selfie_file',
            'attachments',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function storedFiles(): array
    {
        $paths = [];

        foreach ($this->fileDirectories() as $field => $directory) {
            if ($this->hasFile($field)) {
                $paths[$field] = $this->file($field)->store($directory, 'public');
            }
        }

        return $paths;
    }

    /**
     * @return array<string, string>
     */
    private function fileDirectories(): array
    {
        return [
            'permit_file' => 'sellers/permits',
            'accreditation_file' => 'sellers/accreditations',
            'representative_id_file' => 'sellers/ids',
            'valid_id_file' => 'sellers/ids',
            'selfie_file' => 'sellers/ids',
        ];
    }
}
