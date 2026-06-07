<?php

namespace App\Http\Requests;

use App\Enums\AddOn;
use App\Enums\DestinationZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'destination' => ['required', Rule::enum(DestinationZone::class)],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'travelers' => ['required', 'array', 'min:1'],
            'travelers.*.name' => ['required', 'string'],
            'travelers.*.birth_date' => ['required', 'date'],
            'travelers.*.add_ons' => ['sometimes', 'array'],
            'travelers.*.add_ons.*' => [Rule::enum(AddOn::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return trans('quotes.attributes');
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return trans('quotes.messages');
    }
}
