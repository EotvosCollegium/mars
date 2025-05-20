<?php

return [


    // maximum accepted PDF size in kilobytes
    'pdf_size_limit' => env('PRINT_MAX_FILE_SIZE', 10000),

    'cups_address' => env('CUPS_ADDRESS', '127.0.0.1:631'),
];
