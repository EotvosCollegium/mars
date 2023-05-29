<?php

namespace App\Models\EventTriggers;

use Carbon\Carbon;

interface EventTriggerInterface
{
    public function nextDate(): Carbon;
    public function handle();
}
