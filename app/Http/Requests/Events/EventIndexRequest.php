<?php

namespace App\Http\Requests\Events;

use App\Concerns\NormalizeQueryFilters;
use Illuminate\Foundation\Http\FormRequest;

class EventIndexRequest extends FormRequest
{
    use NormalizeQueryFilters;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'available_tickets' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:date_asc,date_desc,featured_first'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    // Definisce i campi filtrabili e li pulisce con il trait NormalizeQueryFilters
    public function filters(): array
    {
        return [
            'search' => $this->normalizeText($this->input('search')),
            'category' => $this->normalizeText($this->input('category')),
            'location' => $this->normalizeText($this->input('location')),
            'start_date' => $this->normalizeDate($this->input('start_date'), startOfDay: true),
            'end_date' => $this->normalizeDate($this->input('end_date'), startOfDay: false),
            'available_tickets' => $this->boolean('available_tickets'),
            'featured' => $this->boolean('featured'),
            'sort' => $this->normalizeSort('sort', ['date_asc', 'date_desc', 'featured_first'], 'date_asc'),
            'per_page' => $this->normalizePerPage('per_page', 24, 1, 100),
        ];
    }

}
