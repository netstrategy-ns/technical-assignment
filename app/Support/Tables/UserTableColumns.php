<?php

namespace App\Support\Tables;

class UserTableColumns
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
                'label' => 'ID pubblico',
                'placeholder' => 'Cerca ID pubblico',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'name',
                'label' => 'Nome',
                'placeholder' => 'Cerca nome',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'email',
                'label' => 'Email',
                'placeholder' => 'Cerca email',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'is_admin',
                'label' => 'Admin',
                'placeholder' => null,
                'cast_type' => 'admin_flag',
                'input_type' => 'checkbox',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'created_at',
                'label' => 'Creato il',
                'placeholder' => 'Data creazione',
                'cast_type' => 'datetime',
                'input_type' => 'date',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
            [
                'field_name' => 'deleted_at',
                'label' => 'Cancellato il',
                'placeholder' => 'Data cancellazione',
                'cast_type' => 'datetime',
                'input_type' => 'date',
                'filterable' => true,
                'sortable' => true,
                'default_sort' => null,
            ],
        ];
    }
}
