<?php

namespace App\Enums;

enum PrintJobStatus: string {
    case QUEUED = 'QUEUED';
    case ERROR = 'ERROR';
    case CANCELLED = 'CANCELLED';
    case SUCCESS = 'SUCCESS';
}