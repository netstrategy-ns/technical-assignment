<?php

namespace App\Support\Tables;

use App\Enums\OrderStatusEnum;

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
                'field_name' => 'user.email',
                'label' => 'Email utente',
                'placeholder' => 'Cerca email utente',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'event_titles',
                'label' => 'Eventi',
                'placeholder' => null,
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => false,
                'sortable' => false,
                'default_sort' => null,
            ],
            [
                'field_name' => 'event_categories',
                'label' => 'Categorie',
                'placeholder' => null,
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => false,
                'sortable' => false,
                'default_sort' => null,
            ],
            [
                'field_name' => 'status',
                'label' => 'Stato',
                'placeholder' => 'Seleziona Stato',
                'cast_type' => 'string',
                'input_type' => 'select',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Tutti']],
                    OrderStatusEnum::selectOptions(),
                ),
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
                'filterable' => false,
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
