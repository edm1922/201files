<?php

use App\Models\Document;
use App\Models\Employee;

return [

    'driver' => env('SCOUT_DRIVER', 'collection'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', false),

    'after_commit' => false,

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
        'index-settings' => [],
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            Document::class => [
                'filterableAttributes' => [
                    'department_id',
                    'document_folder_id',
                    'status',
                ],
                'searchableAttributes' => [
                    'original_filename',
                    'folder_name',
                    'folder_code',
                    'department_name',
                    'department_code',
                ],
                'sortableAttributes' => [
                    'updated_at',
                ],
            ],
            Employee::class => [
                'filterableAttributes' => [
                    'status',
                ],
                'searchableAttributes' => [
                    'last_name',
                    'first_name',
                    'middle_name',
                    'full_name',
                    'system_id',
                    'barcode_id',
                    'folder_code',
                ],
                'sortableAttributes' => [
                    'updated_at',
                ],
            ],
        ],
    ],

    'typesense' => [
        'client-settings' => [
            'api_key' => env('TYPESENSE_API_KEY', 'xyz'),
            'nodes' => [
                [
                    'host' => env('TYPESENSE_HOST', 'localhost'),
                    'port' => env('TYPESENSE_PORT', '8108'),
                    'path' => env('TYPESENSE_PATH', ''),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],
            'nearest_node' => [
                'host' => env('TYPESENSE_HOST', 'localhost'),
                'port' => env('TYPESENSE_PORT', '8108'),
                'path' => env('TYPESENSE_PATH', ''),
                'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
            ],
            'connection_timeout_seconds' => env('TYPESENSE_CONNECTION_TIMEOUT_SECONDS', 2),
            'healthcheck_interval_seconds' => env('TYPESENSE_HEALTHCHECK_INTERVAL_SECONDS', 30),
            'num_retries' => env('TYPESENSE_NUM_RETRIES', 3),
            'retry_interval_seconds' => env('TYPESENSE_RETRY_INTERVAL_SECONDS', 1),
        ],
        'model-settings' => [],
        'import_action' => env('TYPESENSE_IMPORT_ACTION', 'upsert'),
    ],

];
