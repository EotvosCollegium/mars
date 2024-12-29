<?php

return [

    'one_sided_cost' => env('PRINT_COST_ONESIDED'),
    'two_sided_cost' => env('PRINT_COST_TWOSIDED'),

    // maximum accepted PDF size in kilobytes
    'pdf_size_limit' => env('PRINT_MAX_FILE_SIZE', 10000),

    'printer_name' => env('PRINTER_NAME', 'ujbela'),

    'stat_additional_args' => env('PRINTER_STAT_ADDITIONAL_ARGS', ''),
    'additional_args' => env('PRINTER_ADDITIONAL_ARGS', ''),
];
