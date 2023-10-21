<?php

namespace App\Models\GeneralAssemblies;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PresenceCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'general_assembly_id',
        'opened_at',
        'closed_at',
        'note',
    ];

    /**
     * @return BelongsTo The GeneralAssembly this presence check belongs to.
     */
    public function generalAssembly(): BelongsTo
    {
        return $this->belongsTo(GeneralAssembly::class);
    }

    /**
     * @return BelongsToMany The users that checked their presence.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return bool Whether the question has already been opened once (regardless of whether it has been closed since then).
     */
    public function hasBeenOpened(): bool
    {
        return $this->opened_at != null && $this->opened_at <= now();
    }

    /**
     * @return bool Whether the question is currently open.*
     */
    public function isOpen(): bool
    {
        return $this->hasBeenOpened() &&
                !$this->isClosed();
    }
    /**
     * @return bool Whether the question has been closed.
     */
    public function isClosed(): bool
    {
        return $this->closed_at != null && $this->closed_at <= now();
    }

    /**
     * @param User $user The user to check.
     * @return bool Whether the user has already checked their presence.
     */
    public function signedPresence(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Votes for a list of given options in the name of the user.
     * @param User $user
     * @throws Exception if presenceCheck is not open.
     */
    public function signPresence(User $user): void
    {
        if (!$this->isOpen()) {
            throw new \Exception("question not open");
        }

        $this->users()->attach($user->id);
    }

    /**
     * Closes the presence check.
     * @throws Exception if presenceCheck is already closed.
     * @throws Exception if presenceCheck is not open.
     */
    public function close(): void
    {
        if ($this->isClosed()) {
            throw new \Exception("tried to close general assembly when it has already been closed");
        }
        if (!$this->isOpen()) {
            throw new \Exception("tried to close general assembly when it was not open");
        }
        $this->update(['closed_at' => now()]);
    }

    /**
     * @return string The title of the presence check.
     */
    public function getTitleAttribute(): string
    {
        $number = $this->generalAssembly->presenceChecks()->where('opened_at', '<=', $this->opened_at)->count();
        return __('voting.presence_check_title', [
            'number' => $number,
        ]) . ($this->note ? ' (' . $this->note . ')' : '');
    }
}
