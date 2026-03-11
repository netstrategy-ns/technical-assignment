<?php

namespace App\Support\Tables;

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
                'field_name' => 'entered_at',
                'label' => 'Entrata',
                'placeholder' => 'Data ingresso',
                'cast_type' => 'datetime',
                'input_type' => 'datetime-local',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'enabled_at',
                'label' => 'Abilitato',
                'placeholder' => 'Data abilitazione',
                'cast_type' => 'datetime',
                'input_type' => 'datetime-local',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
