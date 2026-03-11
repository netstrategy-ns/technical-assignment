<?php

namespace App\Support\Tables;

class OrderItemTableColumns
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
                'field_name' => 'order.public_id',
                'label' => 'Ordine',
                'placeholder' => 'Cerca ordine',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'ticket.ticket_type.name',
                'label' => 'Tipologia',
                'placeholder' => 'Cerca tipologia',
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
            [
                'field_name' => 'unit_price',
                'label' => 'Prezzo unitario',
                'placeholder' => 'Prezzo unitario',
                'cast_type' => 'decimal:2',
                'input_type' => 'number',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
