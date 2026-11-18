<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shape checks only. The studio's booking rules (times, lengths, overlap)
 * live in App\Actions\HoldBooking.
 */
class StoreBookingRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'bay_id' => ['required', 'integer', 'exists:bays,id'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i:s\Z'],
            'minutes' => ['required', 'integer'],
            'players' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9 ()+.\-]{7,20}$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a phone number we can text your door code to.',
        ];
    }
}
