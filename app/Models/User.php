<?php

namespace App\Models;

use App\Mail\Invitation;
use App\Utils\NotificationCounter;
use Carbon\Carbon;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * @property int $id
 * @property string $name
 * @property string $unique_name
 * @property string $password
 * @property string $remember_token
 * @property bool $reached_wifi_connection_limit
 * @property bool $verified
 * @property Role[]|Collection $roles
 * @property PersonalInformation|null $personalInformation
 * @property EducationalInformation|null $educationalInformation
 * @property File|null $profilePicture
 * @property ApplicationForm|null $application
 * @property Workshops[]|Collection $workshops
 * @property Faculty[]|Collection $faculties
 * @property ImportItem[]|Collection $importItems
 * @property Room|null $room
 * @property PrintAccount|null $printAccount
 * @property FreePages[]|Collection $freePages
 * @property PrintAccountHistory[]|Collection $printHistory
 * @property PrintJob[]|Collection $printJobs
 * @property InternetAccess|null $internetAccess
 * @property MacAddresses[]|Collection $macAddresses
 * @property WifiConnection[]|Collection $wifiConnections
 * @property Semester[]|Collection $semesterStatuses
 * @property Transaction[]|Collection $transactionsPaid
 * @property Transaction[]|Collection $transactionsReceived
 * @property MrAndMissVote[]|Collection $mrAndMissVotesGiven
 * @property MrAndMissVote[]|Collection $mrAndMissVotesGot
 * @property CommunityService[]|Collection $communityServiceRequests
 * @property CommunityService[]|Collection $communityServiceApprovals
 * @method role(Role $role, Workshop|RoleObject|string|null $object)
 * @method collegist()
 * @method active()
 * @method resident()
 * @method extern()
 * @method currentTenant()
 * @method hasToPayKKTNetregInSemester(int $semester_id)
 * @method semestersWhere(string $status)
 */
class User extends Authenticatable implements HasLocalePreference
{
    use NotificationCounter;
    use Notifiable;
    use HasFactory;

    /**
     * The "booting" method of the model.
     * Creates a print account and internet access for the user.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->printAccount()->create();
            $user->internetAccess()->create();
            $user->internetAccess->setWifiCredentials();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Getter for a unique_name attribute (name + neptun code, if applicable and if the user has the right to view it).
     *
     * @return Attribute
     */
    public function uniqueName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->hasEducationalInformation() && user()->can('view', $this)) {
                    return $this->name . ' (' . $this->educationalInformation->neptun . ')';
                }
                return $this->name;
            }
        );
    }

    /**
     * Get the reached_wifi_connection_limit attribute.
     *
     * @return Attribute
     */
    public function reachedWifiConnectionLimit(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->internetAccess->reachedWifiConnectionLimit()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * The user's roles. The relation uses a RoleUser pivot class which includes role objects and workshops.
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_users')
            ->withPivot(['object_id', 'workshop_id'])->using(RoleUser::class);
    }

    /**
     * The user's personal information. Every registered user should have one.
     * @return HasOne
     */
    public function personalInformation(): HasOne
    {
        return $this->hasOne(PersonalInformation::class);
    }

    /**
     * The user's educational information. Only collegists should have one.
     * @return HasOne
     */
    public function educationalInformation(): HasOne
    {
        return $this->hasOne(EducationalInformation::class);
    }

    /**
     * The user's profile picture.
     * @return HasOne
     */
    public function profilePicture(): HasOne
    {
        return $this->hasOne(File::class);
    }

    /**
     * Former collegist's application form.
     * @return HasOne
     */
    public function application(): HasOne
    {
        return $this->hasOne(ApplicationForm::class);
    }

    /**
     * The workshops where the user is a member.
     * @return BelongsToMany
     */
    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class, 'workshop_users');
    }

    /**
    * The workshops where the user is a leader or administrator.
    * @return HasManyThrough
    */
    public function roleWorkshops(): HasManyThrough
    {
        return $this->hasManyThrough(
            Workshop::class,
            RoleUser::class,
            'user_id',
            'id',
            'id',
            'workshop_id'
        )->whereIn('role_id', [Role::get(Role::WORKSHOP_LEADER)->id, Role::get(Role::WORKSHOP_ADMINISTRATOR)->id]);
    }

    /**
     * The workshops where the user is in the application committe.
     * @return HasManyThrough
     */
    public function applicationCommitteWorkshops(): HasManyThrough
    {
        return $this->hasManyThrough(
            Workshop::class,
            RoleUser::class,
            'user_id',
            'id',
            'id',
            'workshop_id'
        )->where('role_id', Role::get(Role::APPLICATION_COMMITTEE_MEMBER)->id);
    }

    /**
     * The faculties where the user is a member.
     * @return BelongsToMany
     */
    public function faculties(): BelongsToMany
    {
        return $this->belongsToMany(Faculty::class, 'faculty_users');
    }

    /**
     * The items the user marked for importing into the Collegium.
     * @return HasMany
     */
    public function importItems(): HasMany
    {
        return $this->hasMany(ImportItem::class);
    }

    /**
     * @return BelongsTo the user's assigned room
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room', 'name');
    }


    /* Print account related */

    /**
     * The user's print account.
     * @return HasOne
     */
    public function printAccount(): HasOne
    {
        return $this->hasOne(PrintAccount::class);
    }

    /**
     * The user's free pages.
     * @return HasMany
     */
    public function freePages(): HasMany
    {
        return $this->hasMany(FreePages::class);
    }

    /**
     * The user's print account history.
     * @return HasMany
     */
    public function printHistory(): HasMany
    {
        return $this->hasMany(PrintAccountHistory::class);
    }

    /**
     * The user's print jobs.
     * @return HasMany
     */
    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }

    /* Internet module related */

    /**
     * The user's internet access.
     * @return hasOne
     */
    public function internetAccess(): HasOne
    {
        return $this->hasOne(InternetAccess::class);
    }

    /**
     * The user's mac addresses.
     * @return hasMany
     */
    public function macAddresses(): HasMany
    {
        return $this->hasMany(MacAddress::class);
    }

    /**
     * The user's wifi connections.
     * @return hasManyThrough
     */
    public function wifiConnections(): HasManyThrough
    {
        return $this->hasManyThrough(
            WifiConnection::class,
            InternetAccess::class,
            'user_id', // Foreign key on InternetAccess table...
            'wifi_username', // Foreign key on WifiConnection table...
            'id', // Local key on Users table...
            'wifi_username' // Local key on InternetAccess table...
        );
    }

    /* Semester related */

    /**
     * Returns the semesters where the user has any status. The relation uses a SemesterStatus pivot class.
     * @return BelongsToMany
     */
    public function semesterStatuses(): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'semester_status')
            ->withPivot(['status', 'verified', 'comment'])->using(SemesterStatus::class);
    }

    /**
     * Returns the semesters where the user has any status. The relation uses a SemesterStatus pivot class.
     * @return HasMany
     */
    public function semesterEvaluations(): HasMany
    {
        return $this->hasMany(SemesterEvaluation::class);
    }

    /* Transaction related */

    /**
     * Returns the transactions paid by the user.
     * @return HasMany
     */
    public function transactionsPaid(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payer_id');
    }

    /**
     * Returns the transactions received by the user.
     * @return HasMany
     */
    public function transactionsReceived(): HasMany
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
    }

    /* Mr and Miss related */

    /**
     * Returns the user's mr and miss votes.
     * @return HasMany
     */
    public function mrAndMissVotesGiven(): HasMany
    {
        return $this->hasMany(MrAndMissVote::class, 'voter');
    }

    /**
     * Returns the user's mr and miss votes.
     * @return HasMany
     */
    public function mrAndMissVotesGot(): HasMany
    {
        return $this->hasMany(MrAndMissVote::class, 'votee');
    }

    /* Community Service related */

    /**
     * Returns the community services the user has requested
     * @return HasMany
     */
    public function communityServiceRequests(): HasMany
    {
        return $this->hasMany(CommunityService::class, 'requester_id');
    }

    /**
     * Returns the community services the user has approved/yet to approve
     * @return HasMany
     */
    public function communityServiceApprovals(): HasMany
    {
        return $this->hasMany(CommunityService::class, 'approver_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Local scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include users with the given role.
     *
     * @param Builder $query
     * @param Role|string $role
     * @param Workshop|RoleObject|string|null $object
     * @return Builder
     */
    public function scopeRole(Builder $query, Role|string $role, Workshop|RoleObject|string $object = null): Builder
    {
        $role = Role::get($role);
        if ($object) {
            $object = $role->getObject($object);
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
     * Scope a query to only include users whose data can be accessed by the given user.
     * @param Builder $query
     * @return Builder
     */
    public function scopeCanView(Builder $query): Builder
    {
        if(user()->isAdmin()) {
            return $query;
        }
        if(user()->hasRole(Role::STAFF)) {
            return $query->role(Role::TENANT);
        }
        if(user()->can('viewAll', User::class)) {
            return $query->collegist();
        }
        if(user()->can('viewSome', User::class)) {
            return $query->collegist()->whereHas('workshops', function ($query) {
                $query->whereIn('id', user()->roleWorkshops->pluck('id')->toArray());
            });
        }
        return $query->where('id', -1);
    }

    /**
     * Scope a query to only include collegist users (including alumni).
     * @return Builder
     */
    public function scopeCollegist(): Builder
    {
        return $this->where(function ($query) {
            return $query->role(Role::COLLEGIST)
                ->orWhere(function ($query) {
                    return $query->role(Role::ALUMNI);
                });
        });
    }

    /**
     * Scope a query to only include active users in the given semester.
     *
     * @param Builder $query
     * @param int $semester_id
     * @return Builder
     */
    public function scopeActive(Builder $query, ?int $semester_id = null): Builder
    {
        return $query->whereHas('semesterStatuses', function ($q) use ($semester_id) {
            $q->where('status', SemesterStatus::ACTIVE)
              ->where('id', $semester_id ?? Semester::current()->id);
        });
    }

    /**
     * Scope a query to only include resident users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeResident(Builder $query): Builder
    {
        return $query->role(Role::COLLEGIST, Role::RESIDENT);
    }

    /**
     * Scope a query to only include extern users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExtern(Builder $query): Builder
    {
        return $query->role(Role::COLLEGIST, RoleObject::firstWhere('name', Role::EXTERN));
    }

    /**
     * Scope a query to only include current tenant users.
     * A tenant is currently active if his tenant until date is in the future.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrentTenant(Builder $query): Builder
    {
        return $query->role(Role::TENANT)
            ->whereHas('personalInformation', function ($q) {
                $q->where('tenant_until', '>', now());
            });
    }

    /**
     * Scope a query to only include users who have to pay kkt or netreg in the current semester.
     *
     * @param Builder $query
     * @param int $semester_id
     * @return Builder
     */
    public function scopeHasToPayKKTNetreg(Builder $query): Builder
    {
        return $query->hasToPayKKTNetregInSemester(Semester::current()->id);
    }

    /**
     * Scope a query to only include users who have to pay kkt or netreg in the given semester.
     *
     * @param Builder $query
     * @param int $semester_id
     * @return Builder
     */
    public function scopeHasToPayKKTNetregInSemester(Builder $query, int $semester_id): Builder
    {
        return $query->role(Role::collegist())->active($semester_id)
            ->whereDoesntHave('transactionsPaid', function ($query) use ($semester_id) {
                $query->where('semester_id', $semester_id);
                $query->whereIn('payment_type_id', [PaymentType::kkt()->id, PaymentType::netreg()->id]);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Public functions
    |--------------------------------------------------------------------------
    */

    /**
     * Verifies a user.
     */
    public function setVerified(): void
    {
        $this->update(['verified' => true]);
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale(): string
    {
        // default english, see issue #11
        return $this->isCollegist() ? 'hu' : 'en';
    }

    /**
     * Sends an invitation email for new users to set a password.
     * Used by `glaivepro/invytr` package.
     */
    public function sendPasswordSetNotification($token)
    {
        Mail::to($this)->queue(new Invitation($this, $token));
    }

    /**
     * Determine if the user has personal information set
     *
     * @return boolean
     */
    public function hasPersonalInformation(): bool
    {
        return isset($this->personalInformation);
    }

    /**
     * Determine if the user has educational information set
     *
     * @return boolean
     */
    public function hasEducationalInformation(): bool
    {
        return isset($this->educationalInformation);
    }


    /* Role related */

    /**
     * Determine if the user is a sys admin. Uses cache.
     * @return boolean
     */
    public function isAdmin(): bool
    {
        return in_array(
            $this->id,
            Cache::remember('sys-admins', 60, function () {
                return Role::get(Role::SYS_ADMIN)->users()->pluck('id')->toArray();
            })
        );
    }

    /**
     * Determine if the user is a collegist (including alumni). Uses cache.
     * @return boolean
     */
    public function isCollegist($alumni = true): bool
    {
        if($this->verified == false) {
            return $this->roles()->where('role_id', Role::collegist()->id)->exists();
        };

        return in_array(
            $this->id,
            Cache::remember('collegists', 60, function () {
                return Role::collegist()->getUsers()->pluck('id')->toArray();
            })
        ) || ($alumni && in_array(
            $this->id,
            Cache::remember('alumni', 60, function () {
                return Role::alumni()->getUsers()->pluck('id')->toArray();
            })
        ));
    }

    /**
     * Attach collegist role as extern or resident.
     * If the user is already a collegist, the object is updated.
     */
    public function setCollegist($objectName): void
    {
        $role = Role::collegist();
        $object = $role->getObject($objectName);
        $this->roles()->detach($role->id);
        $this->roles()->attach($role->id, ['object_id' => $object->id]);

        Cache::forget('collegists');
        WorkshopBalance::generateBalances(Semester::current()->id);
    }

    /**
     * Decides if the user is a resident collegist currently.
     *
     * @return bool
     */
    public function isResident(): bool
    {
        if($this->verified == false) {
            return $this->roles()
            ->where('role_id', Role::collegist()->id)
            ->where('object_id', RoleObject::firstWhere('name', Role::RESIDENT)->id)
            ->exists();
        }
        return $this->hasRole([Role::COLLEGIST => Role::RESIDENT]);
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
     * Decides if the user is an extern collegist currently.
     *
     * @return bool
     */
    public function isExtern(): bool
    {
        if($this->verified == false) {
            return $this->roles()
            ->where('role_id', Role::collegist()->id)
            ->where('object_id', RoleObject::firstWhere('name', Role::EXTERN)->id)
            ->exists();
        }
        return $this->hasRole([Role::COLLEGIST => Role::EXTERN]);
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
     * Determine if the user has a tenant role.
     * @return boolean
     */
    public function isTenant(): bool
    {
        return $this->hasRole(Role::TENANT);
    }

    /**
     * @return bool if the user is currently a tenant
     * A tenant is currently a tenant if they are a tenant and their tenant_until date is in the future.
     */
    public function isCurrentTenant(): bool
    {
        return $this->isTenant() && $this->personalInformation->tenant_until && Carbon::parse($this->personalInformation->tenant_until)->gt(Carbon::now());
    }

    /**
     * @return bool if the user needs to update their tenant status
     * A user needs to update their tenant status if they are a tenant and their tenant_until date is in the past.
     */
    public function needsUpdateTenantUntil(): bool
    {
        return $this->isTenant() && !$this->isCurrentTenant();
    }

    /**
     * Decides if the user has any of the given roles.
     * If a base_role => [possible_objects] array is given, it will check if the user has the base_role with any of the possible_objects.
     *
     * Example usage:
     * hasRole(Role::COLLEGIST)
     * hasRole(Role::collegist()))
     * hasRole([Role::COLLEGIST => Role::EXTERN])
     * hasRole([Role::COLLEGIST => 4, Role::get(Role::WORKSHOP_LEADER)])
     * hasRole([Role::STUDENT_COUNCIL => [Role::PRESIDENT, Role::SCIENCE_VICE_PRESIDENT]]])
     *
     * @param $roles Role|name|id|[Role|name|id|[Role|name => RoleObject|Workshop|name|id]]
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $query = $this->roles();
        $query->where(function ($query) use ($roles) {
            foreach ($roles as $key => $value) {
                $query->orWhere(function ($query) use ($key, $value) {
                    if (is_integer($key)) {
                        //indexed with integers, object not passed
                        $role = Role::get($value);
                        $query->where('role_id', $role->id);
                    } else {
                        $role = Role::get($key);
                        $query->where('role_id', $role->id);
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                        //check if user has any of the objects
                        $query->where(function ($query) use ($role, $value) {
                            foreach ($value as $object) {
                                $object = $role->getObject($object);
                                if ($object instanceof Workshop) {
                                    $query->orWhere('workshop_id', $object->id);
                                } elseif ($object instanceof RoleObject) {
                                    $query->orWhere('object_id', $object->id);
                                }
                            }
                        });
                    }
                });
            }
        });

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
        Cache::forget('collegists');
    }

    /* Status related */

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
        $this->semesterStatuses()->syncWithoutDetaching([
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
     * Returns the collegist's status in the semester.
     *
     * @param int|Semester $semester
     * @return SemesterStatus|null The user's status in the semester or null.
     */
    public function getStatus(int|Semester $semester = null): SemesterStatus|null
    {
        return $this->semesterStatuses->find($semester ?? Semester::current())?->pivot;
    }

    /**
     * Decides if the user is active in the semester.
     *
     * @param ?Semester $semester
     * @return bool
     */
    public function isActive(Semester $semester = null): bool
    {
        return $this->getStatus($semester)?->status == SemesterStatus::ACTIVE;
    }


    /* Printing related */

    /**
     * Returns how many documents the user printed overall.
     *
     * @return int
     */
    public function numberOfPrintedDocuments(): int
    {
        return $this->printHistory()
            ->where('balance_change', '<', 0)
            ->orWhere('free_page_change', '<', 0)
            ->count();
    }

    /**
     * Returns how much the user spent for their printings.
     *
     * @return int
     */
    public function spentBalance(): int
    {
        return abs($this->printHistory()
            ->where('balance_change', '<', 0)
            ->sum('balance_change'));
    }

    /**
    * Returns how many free pages the user used.
    *
    * @return int
    */
    public function spentFreePages(): int
    {
        return abs($this->printHistory()
            ->where('free_page_change', '<', 0)
            ->sum('free_page_change'));
    }

    /**
     * Returns how many free pages are left that can still be used
     * @return int
     */
    public function sumOfActiveFreePages(): int
    {
        return $this->freePages()->where('deadline', '>', Carbon::now())->sum('amount');
    }

    /* Transaction related */

    /**
     * Returns the payed kkt amount in the semester. 0 if has not payed kkt.
     * @param Semester $semester
     * @return int
     */
    public function payedKKTInSemester(Semester $semester): int
    {
        $transaction = $this->transactionsPaid()
            ->where('payment_type_id', PaymentType::kkt()->id)
            ->where('semester_id', $semester->id)
            ->get();

        return $transaction ? $transaction->amount : 0;
    }

    /**
     * Returns the payed kkt amount in the current semester. 0 if has not payed kkt.
     * @return int
     */
    public function payedKKT(): int
    {
        return $this->payedKKTInSemester(Semester::current());
    }

    /*
    |--------------------------------------------------------------------------
    | Static functions
    |--------------------------------------------------------------------------
    */

    /**
     * @return array|User[]|Collection the system admins
     */
    public static function admins(): Collection|array
    {
        return self::role(Role::SYS_ADMIN)->get();
    }

    /**
     * @return array|User[]|Collection the collegists (without alumni)
     */
    public static function collegists(): Collection|array
    {
        return self::role(Role::COLLEGIST)->get();
    }

    /**
     * @return array|User[]|Collection the student council leaders (including committee leaders)
     */
    public static function studentCouncilLeaders(): array|Collection
    {
        $objects = RoleObject::whereIn(
            'name',
            array_merge(Role::STUDENT_COUNCIL_LEADERS, Role::COMMITTEE_LEADERS)
        )
            ->pluck('id')->toArray();

        return User::whereHas('roles', function ($q) use ($objects) {
            return $q->where('role_users.role_id', Role::StudentsCouncil()->id)
                ->whereIn('role_users.object_id', $objects);
        })->get();
    }

    /**
     * @return User|null the president
     */
    public static function president(): ?User
    {
        return self::role(Role::STUDENT_COUNCIL, Role::PRESIDENT)->first();
    }

    /**
     * @return User|null the president
     */
    public static function studentCouncilSecretary(): ?User
    {
        return self::role(Role::STUDENT_COUNCIL_SECRETARY)->first();
    }

    /**
     * @return array|User[]|Collection board of trustees members
     */
    public static function boardOfTrusteesMembers(): Collection|array
    {
        return self::role(Role::BOARD_OF_TRUSTEES_MEMBER)->get();
    }

    /**
     * @return array|User[]|Collection ethics commitioners
     */
    public static function ethicsCommissioners(): Collection|array
    {
        return self::role(Role::ETHICS_COMMISSIONER)->get();
    }

    /**
     * @return User|null the director
     */
    public static function director(): ?User
    {
        return self::role(Role::director())->first();
    }

    /**
     * @return User|null the president
     */
    public static function secretary(): ?User
    {
        return self::role(Role::SECRETARY)->first();
    }

    /**
     * @return User|null the head of the staff
     */
    public static function staff(): ?User
    {
        return self::role(Role::STAFF)->first();
    }

    /**
     * @return array|Collection|User[] the users with printer role
     */
    public static function printers(): Collection|array
    {
        return self::role(Role::PRINTER)->get();
    }

    /**
     * @return array|Collection|User[] the users with printer role
     */
    public static function tenants(): Collection|array
    {
        return self::role(Role::TENANT)->get();
    }

    /**
     * Returns how many not verified users there are currently.
     * Used by the NotificationCounter trait.
     * @return int
     */
    public static function notifications(): int
    {
        return self::withoutGlobalScope('verified')->where('verified', false)->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Global scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Overwrite the model's booted function to exclude not verified users from queries.
     */
    protected static function booted()
    {
        // You can use `withoutGlobalScope('verified')` to include the unverified users in queries.
        static::addGlobalScope('verified', function (Builder $builder) {
            if (Auth::hasUser() && user()->verified) {
                $builder->where('verified', true);
            }
        });
    }
}
