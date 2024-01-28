<?php

namespace App\Models\EventTriggers;

use Carbon\Carbon;

interface EventTriggerInterface
{
    public function nextDate(): Carbon;

    public function handle();

    public function remindBeforeDays(): ?int;

    public function handleReminder(): void;
}
