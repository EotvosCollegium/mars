<?php

namespace App\Models;

use App\Mail\Invitation;
use App\Utils\NotificationCounter;
use Carbon\Carbon;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * @property int $id
 * @property string $name
 * @property string $password
 * @property string $remember_token
 * @property bool $verified
 * @property EducationalInformation $educationalInformation
 * @property FreePages $freePages
 * @property InternetAccess $internetAccess
 * @property Collection|Role[] $roles
 * @property Collection|Semester[] $allSemesters
 * @property Collection|Semester[] $activeSemesters
 * @property Collection|Workshop[] $workshops
 * @property Collection|WifiConnection[] $wifiConnections
 */
class User extends Authenticatable implements HasLocalePreference
{
    use NotificationCounter;
    use Notifiable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'verified',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function booted()
    {
        // By default, unverified users will be excluded.
        // You can use `withoutGlobalScope('verified')` to include them.
        static::addGlobalScope('verified', function (Builder $builder) {
            // This condition prevents side-effects for unverified users.
            if (Auth::hasUser() && Auth::user()->verified) {
                $builder->where('verified', true);
            }
        });
    }

    /**
     * Getter for a unique identifier (name + neptun code, if applicable and if the user has the right to view it).
     * @return string
     */
    public function getUniqueNameAttribute(): string
    {
        if ($this->hasEducationalInformation() && auth()->user()->can('viewEducationalInformation', $this)) {
            return $this->name.' ('.$this->educationalInformation->neptun.')';
        } else {
            return $this->name;
        }
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale(): string
    {
        //TODO store preferred locale for each user
        if($this->isCollegist())
            return 'hu';
        else return 'en';
    }

    /* Printing related getters */

    public function printAccount(): HasOne
    {
        return $this->hasOne('App\Models\PrintAccount');
    }

    public function freePages(): HasMany
    {
        return $this->hasMany('App\Models\FreePages');
    }

    public function sumOfActiveFreePages(): int
    {
        return $this->freePages
            ->where('deadline', '>', Carbon::now())
            ->sum('amount');
    }

    public function printHistory(): HasMany
    {
        return $this->hasMany('App\Models\PrintAccountHistory');
    }

    public function printJobs(): HasMany
    {
        return $this->hasMany('App\Models\PrintJob');
    }

    public function numberOfPrintedDocuments(): int
    {
        return $this->hasMany('App\Models\PrintAccountHistory')
            ->where('balance_change', '<', 0)
            ->orWhere('free_page_change', '<', 0)
            ->count();
    }

    public function spentBalance(): int
    {
        return abs($this->hasMany('App\Models\PrintAccountHistory')
            ->where('balance_change', '<', 0)
            ->sum('balance_change'));
    }

    public function spentFreePages(): int
    {
        return abs($this->hasMany('App\Models\PrintAccountHistory')
            ->where('free_page_change', '<', 0)
            ->sum('free_page_change'));
    }

    /* Internet module related getters */

    public function internetAccess(): HasOne
    {
        return $this->hasOne('App\Models\InternetAccess');
    }

    public function macAddresses(): HasMany
    {
        return $this->hasMany('App\Models\MacAddress');
    }

    public function wifiConnections(): HasManyThrough
    {
        return $this->hasManyThrough(
            'App\Models\WifiConnection',
            'App\Models\InternetAccess',
            'user_id', // Foreign key on InternetAccess table...
            'wifi_username', // Foreign key on WifiConnection table...
            'id', // Local key on Users table...
            'wifi_username' // Local key on InternetAccess table...
        );
    }

    public function getReachedWifiConnectionLimitAttribute(): bool
    {
        return $this->internetAccess->reachedWifiConnectionLimit();
    }

    /* Basic information of the user */

    public function setVerified(): void
    {
        $this->update([
            'verified' => true,
        ]);
    }

    public function personalInformation(): HasOne
    {
        return $this->hasOne(PersonalInformation::class);
    }

    public function hasPersonalInformation(): bool
    {
        return isset($this->personalInformation);
    }

    public function educationalInformation(): HasOne
    {
        return $this->hasOne(EducationalInformation::class);
    }

    public function hasEducationalInformation(): bool
    {
        return isset($this->educationalInformation);
    }

    public function application(): HasOne
    {
        return $this->hasOne(ApplicationForm::class);
    }

    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class, 'workshop_users');
    }

    /**
     * Return workshop administrators/leaders' workshops.
     * @return \Illuminate\Support\Collection
     */
    public function roleWorkshops(): \Illuminate\Support\Collection
    {
        return $this->roles()->whereIn('name', [Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR])
            ->with('workshops')->pluck('workshop');
    }

    /**
     * Return application committee workshops.
     * @return \Illuminate\Support\Collection
     */
    public function applicationWorkshops(): \Illuminate\Support\Collection
    {
        return $this->roles()->where('name', Role::APPLICATION_COMMITTEE_MEMBER)
            ->with('workshops')->pluck('workshop');
    }

    public function faculties()
    {
        return $this->belongsToMany(Faculty::class, 'faculty_users');
    }

    public function importItems(): HasMany
    {
        return $this->hasMany('App\Models\ImportItem');
    }

    public function profilePicture(): HasOne
    {
        return $this->hasOne('App\Models\File');
    }

    /* Role related getters */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_users')
            ->withPivot(['object_id', 'workshop_id'])->using(RoleUser::class);
    }

    /**
     * Decides if the user has any base role of the given roles.
     *
     * @param  array  $roleNames  the roles' name
     * @return bool
     */
    public function hasAnyRoleBase(array $roleNames): bool
    {
        $roles = Role::whereIn('name', $roleNames)->pluck('id');
        return $this->roles()->whereIn('id', $roles)->count() > 0;
    }

    /**
     * Decides if the user has a base role.
     */
    public function hasRoleBase(string $roleName): bool
    {
        return $this->hasAnyRoleBase([$roleName]);
    }


    /**
     * Scope a query to only include users with the given role.
     *
     * @param Builder $query
     * @param string $roleName
     * @param string|Workshop|null $object
     * @return Builder
     */
    public function scopeRole(Builder $query, string $roleName, $object = null) : Builder
    {
        $role = Role::where('name', $roleName)->first();
        if(!$role)
            throw new InvalidArgumentException($roleName . " role does not exist.");
        if($role->has_objects) {
            $object = $role->getObject($object);
            return $query->whereHas('roles', function ($q) use ($role, $object) {
                $q->where('role_users.role_id', $role->id)
                    ->where('role_users.object_id', $object->id);
            });
        }
        if($role->has_workshops)
        {
            if(!($object instanceof Workshop))
                throw new InvalidArgumentException("Role object must be a Workshop instance for the " . $roleName . " role.");
            return $query->whereHas('roles', function ($q) use ($role, $object) {
                $q->where('role_users.role_id', $role->id)
                    ->where('role_users.workshop_id', $object->id);
            });
        }
        if(isset($object)) {
            throw new InvalidArgumentException($roleName . " role does have an object.");
        }
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('role_users.role_id', $role->id);
        });
    }

    /**
     * Decides if the user has a role.
     * @param string $roleName the role's name
     * @param string|Workshop|null $object workshop or object name
     * @return bool
     */
    public function hasRole(string $roleName, $object = null): bool
    {
        return $this->role($roleName, $object)->count() > 0;
    }


    public static function collegists()
    {
        return Role::collegist()->getUsers();
    }

    public function isCollegist(): bool
    {
        return $this->hasRoleBase(Role::COLLEGIST);
    }

    public function isInStudentsCouncil(): bool
    {
        return $this->hasRoleBase(Role::STUDENT_COUNCIL);
    }

    /**
     * @return User|null the president
     */
    public static function president()
    {
        return self::role(Role::STUDENT_COUNCIL, Role::PRESIDENT)->first();
    }

    /**
     * @return User|null the director
     */
    public static function director()
    {
        return Role::getUsers(Role::DIRECTOR)->first();
    }


    public static function printers()
    {
        return Role::firstWhere('name', Role::PRINTER)->getUsers();
    }

    /* Semester related getters */

    /**
     * Returns the semesters where the user has any status.
     */
    public function allSemesters(): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'semester_status')->withPivot(['status', 'verified', 'comment'])->using(SemesterStatus::class);
    }

    /**
     * Returns the semesters where the user has the given status.
     */
    public function semestersWhere($status): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'semester_status')
            ->wherePivot('status', '=', $status)
            ->withPivot('verified', 'comment', 'status')
            ->using(SemesterStatus::class);
    }

    /**
     * Returns the semesters where the user has the given status.
     */
    public function activeSemesters(): BelongsToMany
    {
        return $this->semestersWhere(SemesterStatus::ACTIVE);
    }

    /**
     * Decides if the user has any status in the semester.
     *
     * @param int $semester  semester id
     * @return bool
     */
    public function isInSemester(int $semester): bool
    {
        return $this->allSemesters->contains($semester);
    }

    /**
     * Decides if the user is active in the semester.
     *
     * @param int $semester  semester id
     * @return bool
     */
    public function isActiveIn(int $semester): bool
    {
        return $this->activeSemesters->contains($semester);
    }

    /**
     * Scope a query to only include active users in the given semester.
     *
     * @param Builder $query
     * @param int $semester_id
     * @return Builder
     */
    public function scopeActiveIn(Builder $query, int $semester_id): Builder
    {
        return $query->whereHas('activeSemesters', function ($q) use ($semester_id) {
            $q->where('id', $semester_id);
        });
    }

    /**
     * Decides if the user is active in the current semester.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActiveIn(Semester::current());
    }

    /**
     * Decides if the user is a resident collegist currently.
     *
     * @return bool
     */
    public function isResident(): bool
    {
        return $this->hasRole(Role::COLLEGIST, Role::RESIDENT);
    }

    /**
     * Decides if the user is an extern collegist currently.
     *
     * @return bool
     */
    public function isExtern(): bool
    {
        return $this->hasRole(Role::COLLEGIST, Role::EXTERN);
    }

    /**
     * Set the collegist to be resident.
     * Only applies for collegists.
     */
    public function setResident(): void
    {
        $this->setCollegistRole(Role::RESIDENT);
    }

    /**
     * Set the collegist to be extern.
     * Only applies for collegists.
     */
    public function setExtern()
    {
        $this->setCollegistRole(Role::EXTERN);
    }

    /**
     * Set the collegist to be extern or resident.
     * Only applies for collegists.
     */
    private function setCollegistRole($objectName)
    {
        if ($this->isCollegist()) {
            $role = Role::collegist();
            $object = $role->getObject($objectName);
            $this->roles()->detach($role->id);
            $this->roles()->attach($role->id, ['object_id' => $object->id]);
        }
    }

    /**
     * Returns the collegist's status in the semester.
     *
     * @param int $semester id
     * @return string the status. Returns INACTIVE if the user does not have any status in the given semester.
     */
    public function getStatusIn(int $semester): string
    {
        $semesters = $this->allSemesters;
        if (! $semesters->contains($semester)) {
            return SemesterStatus::INACTIVE;
        }

        return $semesters->find($semester)->pivot->status;
    }

    /**
     * Returns the collegist's status in the current semester.
     *
     * @return string the status. Returns INACTIVE if the user does not have any status.
     */
    public function getStatus(): string
    {
        return $this->getStatusIn(Semester::current()->id);
    }

    /**
     * Sets the collegist's status for a semester.
     *
     * @param Semester the semester.
     * @param string the status
     * @param string optional comment
     * @return User the modified user
     */
    public function setStatusFor(Semester $semester, $status, $comment = null): User
    {
        $this->allSemesters()->syncWithoutDetaching([
            $semester->id => [
                'status' => $status,
                'comment' => $comment,
            ],
        ]);

        return $this;
    }

    /**
     * Sets the collegist's status for the current semester.
     *
     * @param string the status
     * @param string optional comment
     * @return User the modified user
     */
    public function setStatus($status, $comment = null): User
    {
        return $this->setStatusFor(Semester::current(), $status, $comment);
    }

    /**
     * Verify the collegist's status for the semester.
     *
     * @param Semester the semester
     * @return User the modified user
     */
    public function verify($semester): User
    {
        $this->allSemesters()->syncWithoutDetaching([
            $semester->id => [
                'verify' => true,
            ],
        ]);

        return $this;
    }

    public function sendPasswordSetNotification($token)
    {
        Mail::to($this)->queue(new Invitation($this, $token));
    }

    public function transactions_payed(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payer_id');
    }

    public function transactions_received(): HasMany
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
    }

    /**
     * Scope a query to only include users who has to pay kkt or netreg in the given semester.
     *
     * @param Builder $query
     * @param int $semester_id
     * @return Builder
     */
    public function scopeHasToPayKKTNetregInSemester(Builder $query, int $semester_id): Builder
    {
        return $query->role(Role::COLLEGIST)->activeIn($semester_id)
            ->whereDoesntHave('transactions_payed', function ($query) use ($semester_id) {
                $query->where('semester_id', $semester_id);
                $query->whereIn('payment_type_id', [PaymentType::kkt()->id, PaymentType::netreg()->id]);
            });
    }

    /**
     * Returns the payed kkt amount in the semester. 0 if has not payed kkt.
     */
    public function payedKKTInSemester(Semester $semester): int
    {
        $transaction = $this->transactions_payed()
            ->where('payment_type_id', PaymentType::kkt()->id)
            ->where('semester_id', $semester->id)
            ->get();

        return $transaction ? $transaction->amount : 0;
    }

    /**
     * Returns the payed kkt amount in the current semester. 0 if has not payed kkt.
     */
    public function payedKKT(): int
    {
        return $this->payedKKTInSemester(Semester::current());
    }

    public static function notifications(): int
    {
        return self::withoutGlobalScope('verified')->where('verified', false)->count();
    }

    /**
     * Mr and Miss functions.
     */
    public function mrAndMissVotesGiven(): HasMany
    {
        return $this->hasMany('App\Models\MrAndMissVote', 'voter');
    }

    public function mrAndMissVotesGot(): HasMany
    {
        return $this->hasMany('App\Models\MrAndMissVote', 'votee');
    }

    public function votedFor($category): array
    {
        $votes = $this->mrAndMissVotesGiven()
        ->where('category', $category->id)
        ->where('semester', Semester::current()->id);
        if ($votes->count() > 0) {
            return ['voted' => true, 'vote' => $votes->first()];
        }

        return ['voted' => false];
    }
}
