<?php

namespace App\Support\Tables;

class OrderTableColumns
{
    public static function columns(): array
    {
        return [
            [
                'field_name' => 'id',
                'label' => 'ID',
                'placeholder' => null,
                'cast_type' => 'integer',
                'input_type' => 'number',
                'filterable' => false,
                'sortable' => true,
                'default_sort' => 'desc',
            ],
            [
                'field_name' => 'public_id',
                'label' => 'Codice pubblico',
                'placeholder' => 'Cerca codice ordine',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'user.name',
                'label' => 'Utente',
                'placeholder' => 'Cerca utente',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'status',
                'label' => 'Stato',
                'placeholder' => 'Stato',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'total_amount',
                'label' => 'Totale',
                'placeholder' => 'Totale',
                'cast_type' => 'decimal:2',
                'input_type' => 'number',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'created_at',
                'label' => 'Data ordine',
                'placeholder' => 'Data',
                'cast_type' => 'datetime',
                'input_type' => 'date',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
