<?php

namespace App\Support\Tables;

class TicketTableColumns
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
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'ticket_type.name',
                'label' => 'Tipologia',
                'placeholder' => 'Cerca tipologia',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'price',
                'label' => 'Prezzo',
                'placeholder' => 'Prezzo',
                'cast_type' => 'decimal:2',
                'input_type' => 'number',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'max_per_user',
                'label' => 'Max per utente',
                'placeholder' => 'Max',
                'cast_type' => 'integer',
                'input_type' => 'number',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
