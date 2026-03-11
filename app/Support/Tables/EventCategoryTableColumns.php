<?php

namespace App\Support\Tables;

class EventCategoryTableColumns
{
    public static function columns(): array
    {
        return [
            [
                'field_name' => 'name',
                'label' => 'Nome',
                'placeholder' => 'Cerca per nome',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
