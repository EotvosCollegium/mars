<?php

namespace App\Models\PeriodicEvents;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait HasPeriodicEvent
{
    protected bool $connectedToSemester = false;
    protected bool $hasStartDate = false;
    protected bool $hasShowUntil = false;

    public final function periodicEvent(): ?PeriodicEvent {
        return PeriodicEvent::where('event_model', self::class)
            ->where(function($query) {
                $query
                    ->orWhere('extended_end_date', '>=', now())
                    ->orWhere(function($query) {
                        $query
                            ->whereNull('extended_end_date')
                            ->where('end_date', '>=', now());
                    })
                    ->orWhere('show_until', '>=', now());
            })
            ->orderBy('start_date', 'desc')
            ->first();
    }

    /**
     * Handle authorization to change the PeriodicEvent.
     */
    public abstract function authorizeChangePeriodicEvent(): void;

    /**
     * Handle periodic event start event.
     */
    public function handlePeriodicEventStart(): void
    {
        // Do nothing by default
    }

    /**
     * Handle periodic event end event.
     */
    public function handlePeriodicEventEnd(): void
    {
        // Do nothing by default
    }

    /**
     * Check if the PeriodicEvent is currently active or not.
     * start date <= now <= (extended) end date
     * @return bool
     */
    public final function isActive(): bool
    {
        return $this->periodicEvent()?->isActive() ?? false;
    }

    /**
     * @return bool if the end date has been extended or not
     */
    public final function isExtended(): bool
    {
        return $this->periodicEvent()?->isExtended() ?? false;
    }

    /**
     * @return Carbon|null the start date of the current PeriodicEvent
     */
    public final function getStartDate(): ?Carbon
    {
        if(!$this->periodicEvent()) return null;
        return Carbon::parse($this->periodicEvent()->start_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    public final function getEndDate(): ?Carbon
    {
        if(!$this->periodicEvent()) return null;
        return Carbon::parse($this->periodicEvent()->real_end_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    public final function getDeadline(): ?Carbon
    {
        return $this->getEndDate();
    }

    /**
     * @return Semester|BelongsTo|null the semester connected to the current PeriodicEvent
     */
    public final function semester(): Semester|BelongsTo|null
    {
        return $this->periodicEvent()?->semester()->first();
    }

    /**
     * Create or update the current PeriodicEvent connected to the model.
     * @throws AuthorizationException
     */
    public function storeOrUpdatePeriodicEvent(Request $request): RedirectResponse
    {
        $this->authorizeChangePeriodicEvent();
        $request->validate([
            'semester_id' => ['exists:semesters,id', Rule::requiredIf(fn () => $this->connectedToSemester)],
            'start_date' => ['date', Rule::requiredIf(fn () => $this->hasStartDate)],
            'end_date' => 'required|date|after:now|after:start_date',
            'extended_end_date' => 'nullable|date|after:end_date',
            'show_until' => ['date', Rule::requiredIf(fn () => $this->hasShowUntil)],
        ]);
        $event = $this->periodicEvent();
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
                'event_model' => self::class,
                'semester_id' => $request->get('semester_id'),
                'start_date' => $request->get('start_date') ?? now(),
                'end_date' => $request->get('end_date'),
                'extended_end_date' => $request->get('extended_end_date'),
                'show_until' => $request->get('show_until'),
            ]);
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }


}

