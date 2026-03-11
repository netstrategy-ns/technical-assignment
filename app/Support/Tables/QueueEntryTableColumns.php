<?php

namespace App\Support\Tables;

use App\Enums\QueueEntryStatus;

class QueueEntryTableColumns
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
                'field_name' => 'status',
                'label' => 'Stato',
                'placeholder' => 'Seleziona Stato',
                'cast_type' => 'string',
                'input_type' => 'select',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Tutti']],
                    QueueEntryStatus::selectOptions(),
                ),
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'entered_at',
                'label' => 'Entrata',
                'placeholder' => 'Data ingresso',
                'cast_type' => 'datetime',
                'input_type' => 'date',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'enabled_until',
                'label' => 'Scadenza',
                'placeholder' => 'Data scadenza',
                'cast_type' => 'datetime',
                'input_type' => 'date',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
