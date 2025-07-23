<?php

return [
    'exports' => [
        'chunk_size' => 1000,
        'pre_calculate_formulas' => false,
        'strict_null_comparison' => false,
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ],
    ],

    'imports' => [
        'read_only' => true,
        'ignore_empty' => false,
        'heading_row' => [
            'formatter' => 'slug',
        ],
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ],
    ],

    'extension_detector' => [
        'xlsx' => 'Xlsx',
        'xlsm' => 'Xlsx',
        'xltx' => 'Xlsx',
        'xltm' => 'Xlsx',
        'xls' => 'Xls',
        'xlt' => 'Xls',
        'ods' => 'Ods',
        'ots' => 'Ods',
        'slk' => 'Slk',
        'xml' => 'Xml',
        'gnumeric' => 'Gnumeric',
        'htm' => 'Html',
        'html' => 'Html',
        'csv' => 'Csv',
        'tsv' => 'Csv',
        'txt' => 'Csv',
    ],

    'value_binder' => [
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'cache' => [
        'driver' => 'memory',
        'batch' => [
            'memory_limit' => 60000,
        ],
    ],

    'transactions' => [
        'handler' => 'db',
    ],

    'temporary_files' => [
        'local_path' => storage_path('app'),
        'remote_disk' => null,
        'remote_prefix' => null,
        'force_resync_remote' => null,
    ],
];
