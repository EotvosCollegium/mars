<?php

namespace App\Models\GeneralAssemblies;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\GeneralAssemblies\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GeneralAssembly extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['title', 'opened_at', 'closed_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * @return HasMany The questions that belong to the general_assembly.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * @return bool Whether the general_assembly has been opened (regardless of whether it has been closed since then).
     */
    public function hasBeenOpened(): bool
    {
        return $this->opened_at!=null && $this->opened_at<=now();
    }

    /**
     * @return bool Whether the question is currently open.
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
        return $this->closed_at!=null && $this->closed_at<=now();
    }

    /**
     * @return Collection|User[]|array The users who have attended the general assembly.
     */
    public function attendees(): Collection|array
    {
        $question_number = $this->questions()->count();
        return User::whereIn('id', function ($query) use ($question_number) {
            $query->select('user_id')
                    ->from('question_user')
                    ->join('questions', 'questions.id', '=', 'question_user.question_id')
                    ->where('questions.general_assembly_id', $this->id)
                    ->groupBy('user_id')
                    ->havingRaw('count(*) >= ?-2', [$question_number]);
        })->get();
    }

    /**
     * @param User $user The user to check.
     * @return bool Whether the user has attended the general assembly. (voted for all questions except for max. 2)
     */
    public function isAttended(User $user): bool
    {
        $question_number = $this->questions()->count();
        return $this->select('user_id')
            ->from('question_user')
            ->join('questions', 'questions.id', '=', 'question_user.question_id')
            ->where('questions.general_assembly_id', $this->id)
            ->groupBy('user_id')
            ->havingRaw('count(*) >= ?-2', [$question_number])
            ->having('user_id', $user->id)
            ->exists();
    }

    /**
     * @param User $user The user to check.
     * @return bool Whether the user has attended at least one from the last two general assemblies.
     */
    public static function requirementsPassed(User $user): bool
    {
        $lastAssemblies = GeneralAssembly::all()->sortByDesc('closed_at')->take(2);
        foreach ($lastAssemblies as $assembly) {
            if ($assembly->isAttended($user)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Opens the question.
     * @throws Exception if it has already been opened.
     */
    public function open(): void
    {
        if ($this->isOpen() || $this->isClosed()) {
            throw new \Exception("tried to open general assembly when it has already been opened");
        }
        $this->update(['opened_at'=>now()]);
    }

    /**
     * Closes the question.
     * @throws Exception if it has already been closed or if it is not even open.
     */
    public function close(): void
    {
        if ($this->isClosed()) {
            throw new \Exception("tried to close general assembly when it has already been closed");
        }
        if (!$this->isOpen()) {
            throw new \Exception("tried to close general assembly when it was not open");
        }
        foreach ($this->questions()->get() as $question) {
            if ($question->isOpen()) {
                $question->close();
            }
        }
        $this->update(['closed_at'=>now()]);
    }
}
