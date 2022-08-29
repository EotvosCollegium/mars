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
 * @property PersonalInformation $personalInformation
 * @property EducationalInformation $educationalInformation
 * @property FreePages $freePages
 * @property InternetAccess $internetAccess
 * @property Collection|Role[] $roles
 * @property Collection|Semester[] $allSemesters
 * @property Collection|Semester[] $activeSemesters
 * @property Collection|Workshop[] $workshops
 * @property Collection|WifiConnection[] $wifiConnections
 * @method role(Role $role, Workshop|RoleObject|null $object)
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
        'name', 'email', 'password', 'verified', 'room'
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
        if ($this->hasEducationalInformation() && auth()->user()->can('view', $this)) {
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
        if ($this->isCollegist()) {
            return 'hu';
        } else {
            return 'en';
        }
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
     * @return Workshop[]|Collection
     */
    public function roleWorkshops(): array|Collection
    {
        return Workshop::whereIn(
            'id',
            $this->roles()->whereIn('name', [Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR])
            ->pluck('role_users.workshop_id')
        )->get();
    }

    /**
     * Return application committee workshops.
     * @return \Illuminate\Support\Collection
     */
    public function applicationWorkshops(): \Illuminate\Support\Collection
    {
        if ($this->can('viewAllApplications', User::class)) {
            return Workshop::all();
        } else {
            return Workshop::whereIn(
                'id',
                $this->roles()->whereIn('name', [Role::APPLICATION_COMMITTEE_MEMBER, Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR])
                    ->pluck('role_users.workshop_id')
            )
                ->get();
        }
    }

    public function faculties(): BelongsToMany
    {
        return $this->belongsToMany(Faculty::class, 'faculty_users');
    }

    public function importItems(): HasMany
    {
        return $this->hasMany(ImportItem::class);
    }

    public function profilePicture(): HasOne
    {
        return $this->hasOne(File::class);
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
     * @param Role|string $role
     * @param RoleObject|Workshop|null $object
     * @return Builder
     */
    public function scopeRole(Builder $query, Role|string $role, Workshop|RoleObject $object = null): Builder
    {
        if (!$role instanceof Role) {
            $role = Role::firstWhere('name', $role);
            if (!$role) {
                throw new InvalidArgumentException("Role '".$role ?? 'null'."' does not exist.");
            }
        }
        if ($object instanceof RoleObject) {
            return $query->whereHas('roles', function ($q) use ($role, $object) {
                $q->where('role_users.role_id', $role->id)
                    ->where('role_users.object_id', $object->id);
            });
        }
        if ($object instanceof Workshop) {
            return $query->whereHas('roles', function ($q) use ($role, $object) {
                $q->where('role_users.role_id', $role->id)
                    ->where('role_users.workshop_id', $object->id);
            });
        }
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('role_users.role_id', $role->id);
        });
    }

    /**
     * Decides if the user has a role.
     * @param Role|string $role
     * @param integer|string|RoleObject|Workshop|null $object
     * @return bool
     */
    public function hasRole(Role|string $role, int|string|Workshop|RoleObject $object = null): bool
    {
        if (!($role instanceof Role)) {
            $role = Role::firstWhere('name', $role);
        }
        $object = $role->getObject($object);
        $query = $this->roles()->where('role_id', $role->id);
        if ($object instanceof Workshop) {
            $query->where('workshop_id', $object->id);
        } elseif ($object instanceof RoleObject) {
            $query->where('object_id', $object->id);
        }
        return $query->exists();
    }

    /**
     * Attach a role to the user.
     * @param Role $role
     * @param RoleObject|Workshop|null $object
     * @return bool
     */
    public function addRole(Role $role, Workshop|RoleObject $object = null): bool
    {
        if (!$role->isValid($object)) {
            return false;
        }

        if ($role->has_objects) {
            //if adding a collegist role to a collegist
            if ($role->name == Role::COLLEGIST) {
                //just change resident/extern status.
                $this->setCollegist($object->name);
            }
            if ($this->roles()->where('id', $role->id)->wherePivot('object_id', $object->id)->doesntExist()) {
                $this->roles()->attach([
                    $role->id => ['object_id' => $object->id],
                ]);
            }
        } elseif ($role->has_workshops) {
            if ($this->roles()->where('id', $role->id)->wherePivot('workshop_id', $object->id)->doesntExist()) {
                $this->roles()->attach([
                    $role->id => ['workshop_id' => $object->id],
                ]);
            }
        } else {
            if ($this->roles()->where('id', $role->id)->doesntExist()) {
                $this->roles()->attach($role->id);
            }
        }
        return true;
    }

    /**
     * Detach a role from a user. Assumes a valid role-object pair.
     * @param Role $role
     * @param RoleObject|Workshop|null $object
     * @return void
     */
    public function removeRole(Role $role, Workshop|RoleObject $object = null): void
    {
        if ($role->has_objects && isset($object)) {
            $this->roles()->where('id', $role->id)->wherePivot('object_id', $object->id)->detach();
        } elseif ($role->has_workshops && isset($object)) {
            $this->roles()->where('id', $role->id)->wherePivot('workshop_id', $object->id)->detach();
        } else {
            $this->roles()->detach($role->id);
        }
    }

    public static function collegists(): Collection|array
    {
        return Role::collegist()->getUsers();
    }

    public function isCollegist(): bool
    {
        return $this->hasRoleBase(Role::COLLEGIST);
    }

    public function isAdmin(): bool
    {
        return $this->hasRoleBase(Role::SYS_ADMIN);
    }

    /**
     * Checks if the user is a (vice-) president or committee leader.
     * Committee members are not part of the council here.
     */
    public function isInStudentsCouncil(): bool
    {
        $roleObjectIds = RoleObject::whereIn('name', array_merge(Role::STUDENT_COUNCIL_LEADERS, Role::COMMITTEE_LEADERS))->pluck('id');
        return $this->roles()->where('role_id', Role::StudentsCouncil()->id)
            ->whereIn('object_id', $roleObjectIds)
            ->exists();
    }

    /**
     * @return User|null the president
     */
    public static function president(): ?User
    {
        return self::role(Role::StudentsCouncil(), RoleObject::president())->first();
    }

    public function isPresident(): bool
    {
        return $this->hasRole(Role::StudentsCouncil(), Role::PRESIDENT);
    }

    /**
     * @return User|null the director
     */
    public static function director(): ?User
    {
        return self::role(Role::Director())->first();
    }


    public static function printers(): Collection|array
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
     * @param Semester $semester
     * @return bool
     */
    public function isActiveIn(Semester $semester): bool
    {
        return $this->activeSemesters->contains($semester->id);
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
        $this->setCollegist(Role::RESIDENT);
    }

    /**
     * Set the collegist to be extern.
     * Only applies for collegists.
     */
    public function setExtern(): void
    {
        $this->setCollegist(Role::EXTERN);
    }

    /**
     * Set the collegist to be extern or resident.
     * Only applies for collegists.
     */
    private function setCollegist($objectName): void
    {
        $role = Role::collegist();
        $object = $role->getObject($objectName);
        $this->roles()->detach($role->id);
        $this->roles()->attach($role->id, ['object_id' => $object->id]);

        WorkshopBalance::generateBalances(Semester::current()->id);
    }

    /**
     * Returns the collegist's status in the semester.
     *
     * @param $semester id
     * @return string the status. Returns INACTIVE if the user does not have any status in the given semester.
     */
    public function getStatusIn($semester): string
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
        return $query->role(Role::Collegist())->activeIn($semester_id)
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
    public function room(){
        return $this->belongsTo(\App\Molels\Room::class, 'room', 'name');
    }
}
