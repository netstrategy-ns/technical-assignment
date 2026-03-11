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
                'filterable' => false,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'event_title',
                'label' => 'Evento',
                'placeholder' => 'Cerca evento',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'event_date',
                'label' => 'Data evento',
                'placeholder' => 'Data evento',
                'cast_type' => 'string',
                'input_type' => 'date',
                'filterable' => true,
                'sortable' => false,
                'default_sort' => null,
            ],
            [
                'field_name' => 'ticket_type.name',
                'label' => 'Tipologia',
                'placeholder' => 'Cerca tipologia',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => false,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'price',
                'label' => 'Prezzo Unitario',
                'placeholder' => 'Prezzo Unitario',
                'cast_type' => 'currency:2',
                'input_type' => 'number',
                'filterable' => false,
                'sortable' => false,
                'default_sort' => null,
            ],
            [
                'field_name' => 'availability',
                'label' => 'Disponibilità',
                'placeholder' => null,
                'cast_type' => 'integer',
                'input_type' => 'number',
                'filterable' => false,
                'sortable' => false,
                'default_sort' => null,
            ],
            [
                'field_name' => 'purchased_quantity',
                'label' => 'Acquistati',
                'placeholder' => null,
                'cast_type' => 'integer',
                'input_type' => 'number',
                'filterable' => false,
                'sortable' => false,
                'default_sort' => null,
            ],
            [
                'field_name' => 'max_per_user',
                'label' => 'Max per utente',
                'placeholder' => 'Max',
                'cast_type' => 'integer',
                'input_type' => 'number',
                'filterable' => false,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
