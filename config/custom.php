<?php

return [
    'netreg' => env('NETREG', '1000'),
    'kkt' => env('KKT', '2000'),

    'room_reservation_open' => env('ROOM_RESERVATION_OPEN', true),

    'workshop_balance_resident' => env('WORKSHOP_BALANCE_RESIDENT', '0.6'),
    'workshop_balance_extern' => env('WORKSHOP_BALANCE_EXTERN', '0.45'),

    'mr_and_miss_deadline' => env('MR_AND_MISS_DEADLINE'),

    'application_deadline' => env('APPLICATION_DEADLINE'), //registration deadline for new collegists
    'application_extended' => env('APPLICATION_EXTENDED', false), //is the registration deadline extended?

    'semester_evaluation_deadline' => env('SEMESTER_EVALUATION_DEADLINE'),

    // maximum accepted file size in kilobytes (also applies for profile pictures)
    'general_file_size_limit' => env('GENERAL_FILE_SIZE_LIMIT', 10000),
];
