<?php

/**
 * Types - https://laravel.com/docs/9.x/migrations#available-column-types
 * Methods: unique, nullable, default
 */
return [
    'table' => 'products',
    'fields' => [
        'id' => [
            'type' => 'bigIncrements',
        ],
        'name' => [
            'type' => 'string',
            'nullable' => false,
            'unique' => true,
        ],
        'description' => [
            'type' => 'text',
            'nullable' => true,
        ],
        'price' => [
            'type' => 'float',
            'nullable' => false,
        ],
        'is_active' => [
            'type' => 'boolean',
            'nullable' => false,
            'default' => true,
        ],
    ]
];
