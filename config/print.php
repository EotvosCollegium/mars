<?php

return [

    'cost' => [
        'one_sided' => env('PRINT_COST_ONESIDED'),
        'two_sided' => env('PRINT_COST_TWOSIDED'),
    ],

    // Maximum accepted PDF size in byte.
    'pdf_size_limit' => env('PRINT_MAX_FILE_SIZE', 10000000),

    'printer_name' => env('PRINTER_NAME', 'ujbela'),

    'stat_additional_args' => env('PRINTER_STAT_ADDITIONAL_ARGS', ''),
    'additional_args' => env('PRINTER_ADDITIONAL_ARGS', ''),
];
