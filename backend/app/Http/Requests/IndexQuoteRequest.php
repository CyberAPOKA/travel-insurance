<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('filters') && is_string($this->input('filters'))) {
            $decoded = json_decode($this->input('filters'), true);

            if (is_array($decoded)) {
                $this->merge(['filters' => $decoded]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'filters' => ['sometimes', 'nullable', 'array'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
