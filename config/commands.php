<?php

return [
    'lpstat' => env('LPSTAT_COMMAND', 'lpstat'),
    'cancel' => env('CANCEL_COMMAND', 'cancel'),
    'nmap' => env('NMAP_COMMAND', 'nmap'),
    'pdfinfo' => env('PDFINFO_COMMAND', 'pdfinfo'),
    'pdflatex' => env('PDFLATEX_COMMAND', '/usr/bin/pdflatex'),
    'run_in_debug' => env('RUN_COMMANDS_IN_DEBUG_MODE', false),
];
