<?php

namespace App\Enums;

enum PrinterCancelResult: string {
    case AlreadyCancelled = "already-cancelled";
    case AlreadyCompleted = "already-completed";
    case CannotCancel = "cannot-cancel";
    case Success = "successfully-cancelled";
}