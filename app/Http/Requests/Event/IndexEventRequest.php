<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class IndexEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:100'],
            'city' => ['sometimes', 'string', 'max:100'],
            'featured' => ['sometimes', 'boolean'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date'],
            'order' => [
                'sometimes',
                'string',
                'in:starts_at_asc,starts_at_desc,created_at_desc,featured_first',
            ],
        ];
    }
}
