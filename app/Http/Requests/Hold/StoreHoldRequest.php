<?php

namespace App\Http\Requests\Hold;

use Illuminate\Foundation\Http\FormRequest;

class StoreHoldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }
}
