<?php

namespace App\Models\PeriodicEvents;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

abstract class PeriodicEventController extends Controller
{
    protected const connectedToSemester = false;
    protected const hasStartDate = false;
    protected const hasShowUntil = false;

    /**
     * Return the current PeriodicEvent connected to the model.
     * Returns the newest event in the database based on start_date (even if it may not be active).
     * @return PeriodicEvent|null
     */
    public static final function periodicEvent(): ?PeriodicEvent
    {
        return PeriodicEvent::where('event_model', static::class)
            ->where(function($query) {
                $query->orWhere(function($query) {
                    $query->whereNotNull('extended_end_date')
                        ->where('extended_end_date', '>=', now());
                })->orWhere(function($query) {
                    $query->whereNull('extended_end_date')
                        ->where('end_date', '>=', now());
                })->orWhere(function($query) {
                    $query->whereNotNull('show_until')
                        ->where('show_until', '>=', now());
                });
            })
            ->orderBy('start_date', 'desc')
            ->first();
    }

    /**
     * Check if the PeriodicEvent is currently active or not.
     * start date <= now <= (extended) end date
     * @return bool
     */
    public static final function isActive(): bool
    {
        return self::periodicEvent()?->isActive() ?? false;
    }

    /**
     * @return bool if the end date has been extended or not
     */
    public static final function isExtended(): bool
    {
        return self::periodicEvent()?->isExtended() ?? false;
    }

    /**
     * @return Carbon|null the start date of the current PeriodicEvent
     */
    public static final function getStartDate(): ?Carbon
    {
        if(!self::periodicEvent()) return null;
        return Carbon::parse(self::periodicEvent()->start_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    public static final function getEndDate(): ?Carbon
    {
        if(!self::periodicEvent()) return null;
        return Carbon::parse(self::periodicEvent()->real_end_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    public static final function getDeadline(): ?Carbon
    {
        return self::getEndDate();
    }

    /**
     * Return the semester the current PeriodicEvent is connected to.
     */
    public static final function connectedSemester(): ?Semester
    {
        return self::periodicEvent()?->semester;
    }

    /**
     * Create or update the current PeriodicEvent connected to the model.
     * @throws AuthorizationException
     */
    public final function storeOrUpdatePeriodicEvent(Request $request): RedirectResponse
    {
        //$this->authorize('create', [PeriodicEvent::class, static::class]);
        //TODO
        $request->validate([
            'semester_id' => ['exists:semesters,id', Rule::requiredIf(fn () => $this::connectedToSemester)],
            'start_date' => ['date', Rule::requiredIf(fn () => $this::hasStartDate)],
            'end_date' => 'required|date|after:now|after:start_date',
            'extended_end_date' => 'nullable|date|after:end_date',
            'show_until' => ['date', Rule::requiredIf(fn () => $this::hasShowUntil)],
        ]);
        $event = self::periodicEvent();
        if($event) {
            $event->update([
                'start_date' => $request->get('start_date') ?? $event->start_date,
                'end_date' => $request->get('end_date'),
                'extended_end_date' => $request->get('extended_end_date') ?? $event->extended_end_date,
                'show_until' => $request->get('show_until') ?? $event->show_until,
            ]);
            //TODO reset _handled fields
        } else {
            PeriodicEvent::create([
                'event_model' => static::class,
                'semester_id' => $request->get('semester_id'),
                'start_date' => $request->get('start_date') ?? now(),
                'end_date' => $request->get('end_date'),
                'extended_end_date' => $request->get('extended_end_date'),
                'show_until' => $request->get('show_until'),
            ]);
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }


    /**
     * Handle PeriodicEvent start.
     */
    public static function handleStart(): void
    {
        // do nothing by default
    }

    /**
     * Handle PeriodicEvent end / deadline.
     */
    public static function handleEnd(): void
    {
        // do nothing by default
    }

    //TODO reminders
}
