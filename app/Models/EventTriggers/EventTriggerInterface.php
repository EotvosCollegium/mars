<?php

namespace App\Models\EventTriggers;

use Carbon\Carbon;

interface EventTriggerInterface
{
    // After execution, set next date.
    public function nextDate(): Carbon;

    // Handle event.
    public function handle();

    // Send daily reminders from this until the event.
    public function remindBeforeDays(): ?int;

    // Handle reminder.
    public function handleReminder(): void;
}
