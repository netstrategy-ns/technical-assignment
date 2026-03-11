<?php

namespace App\Support\Tables;

class TicketTypeQuotaTableColumns
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
                'field_name' => 'event.title',
                'label' => 'Evento',
                'placeholder' => 'Cerca evento',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'quantity',
                'label' => 'Quantità',
                'placeholder' => 'Quantità',
                'cast_type' => 'integer',
                'input_type' => 'number',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
