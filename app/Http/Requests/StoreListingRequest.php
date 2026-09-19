<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\CategorySpecField;
use App\Models\Listing;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Http\UploadedFile;

class StoreListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $listing = $this->route('listing');

        return ($user?->sellerProfile?->isVerified() === true || $user?->dealerProfile?->isVerified() === true)
            && (! $listing || $listing->user_id === $user->id);
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'negotiable' => ['boolean'],
            'condition' => ['required', Rule::in([
                Listing::ConditionBrandNew,
                Listing::ConditionUsed,
                Listing::ConditionSurplus,
            ])],
            'year_model' => ['nullable', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'specs' => ['array'],
            'images' => ['array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'attachments' => ['array', 'max:10'],
            'attachments.*' => ['file', 'mimes:pdf', 'max:5120'],
            'valid_id_file' => [Rule::requiredIf(fn (): bool => ! $this->hasApprovedBusinessProfile()), 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'or_cr_file' => [Rule::requiredIf(fn (): bool => ! $this->hasApprovedBusinessProfile()), 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $categoryId = $this->integer('category_id');

                if (! $categoryId) {
                    return;
                }

                $fields = CategorySpecField::query()
                    ->where('category_id', $categoryId)
                    ->get();
                $submittedSpecs = (array) $this->input('specs', []);

                foreach ($submittedSpecs as $fieldId => $value) {
                    if (! $fields->contains('id', (int) $fieldId)) {
                        $validator->errors()->add("specs.{$fieldId}", 'This specification does not belong to the selected category.');
                    }
                }

                foreach ($fields as $field) {
                    $value = $submittedSpecs[$field->id] ?? null;

                    if ($field->required && blank($value)) {
                        $validator->errors()->add("specs.{$field->id}", "{$field->label} is required.");
                    }

                    if (! blank($value) && $field->type === 'number' && ! is_numeric($value)) {
                        $validator->errors()->add("specs.{$field->id}", "{$field->label} must be a number.");
                    }

                    if (! blank($value) && $field->type === 'select' && ! in_array($value, $field->options ?? [], true)) {
                        $validator->errors()->add("specs.{$field->id}", "{$field->label} is not a valid option.");
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function listingData(): array
    {
        return $this->safe()->except(['specs', 'images', 'attachments', 'valid_id_file', 'or_cr_file']);
    }

    /** @return array<string, string> */
    public function storedIdentityDocuments(): array
    {
        return collect(['valid_id_file', 'or_cr_file'])
            ->mapWithKeys(function (string $field): array {
                $file = $this->file($field);
                return $file instanceof UploadedFile ? [$field => $file->store('listings/identity', 'public')] : [];
            })->all();
    }

    private function hasApprovedBusinessProfile(): bool
    {
        $user = $this->user();
        return $user?->sellerProfile?->isVerified() === true || $user?->dealerProfile?->isVerified() === true;
    }

    /**
     * @return array<int, string>
     */
    public function storedImages(): array
    {
        $paths = [];

        foreach ($this->file('images', []) as $image) {
            $paths[] = $image->store('listings/images', 'public');
        }

        return $paths;
    }

    /**
     * @return array<int, string>
     */
    public function specsForCategory(Category $category): array
    {
        $specs = (array) $this->input('specs', []);

        return $category->specFields
            ->mapWithKeys(fn (CategorySpecField $field): array => [
                $field->id => (string) ($specs[$field->id] ?? ''),
            ])
            ->filter(fn (string $value): bool => $value !== '')
            ->all();
    }
}
