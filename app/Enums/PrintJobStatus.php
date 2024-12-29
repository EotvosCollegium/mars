<?php

namespace App\Enums;

enum PrintJobStatus: string {
    case QUEUED = 'QUEUED';
    //case ERROR = 'ERROR'; //There are some older print jobs that have this status, but it is not used anymore
    case CANCELLED = 'CANCELLED';
    case SUCCESS = 'SUCCESS';
    // case REIMBURSED = 'REIMBURSED';
}