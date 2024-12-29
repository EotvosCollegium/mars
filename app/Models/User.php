<?php

namespace App\Models;

use App\Mail\Invitation;
use App\Models\GeneralAssemblies\PresenceCheck;
use App\Models\Internet\InternetAccess;
use App\Models\Reservations\ReservableItem;
use App\Models\Reservations\Reservation;
use App\Utils\HasRoles;
use App\Utils\NotificationCounter;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Auth\Passwords\PasswordBroker;
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
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $unique_name
 * @property string $password
 * @property string $remember_token
 * @property bool $verified
 * @property Role[]|Collection $roles
 * @property PersonalInformation|null $personalInformation
 * @property EducationalInformation|null $educationalInformation
 * @property File|null $profilePicture
 * @property Application|null $application
 * @property Workshop[]|Collection $workshops
 * @property Faculty[]|Collection $faculties
 * @property ImportItem[]|Collection $importItems
 * @property Room|null $room
 * @property PrintAccount|null $printAccount
 * @property FreePages[]|Collection $freePages
 * @property PrintAccountHistory[]|Collection $printHistory
 * @property PrintJob[]|Collection $printJobs
 * @property InternetAccess|null $internetAccess
 * @property Semester[]|Collection $semesterStatuses
 * @property Transaction[]|Collection $transactionsPaid
 * @property Transaction[]|Collection $transactionsReceived
 * @property MrAndMissVote[]|Collection $mrAndMissVotesGiven
 * @property MrAndMissVote[]|Collection $mrAndMissVotesGot
 * @property CommunityService[]|Collection $communityServiceRequests
 * @property CommunityService[]|Collection $communityServiceApprovals
 * @method Builder|static role(Role $role, Workshop|RoleObject|string|null $object)
 * @method Builder|static collegist()
 * @method Builder|static active()
 * @method Builder|static resident()
 * @method Builder|static extern()
 * @method Builder|static currentTenant()
 * @method Builder|static hasToPayKKTNetregInSemester(int $semester_id)
 * @method Builder|static semestersWhere(string $status)
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $community_service_approvals_count
 * @property-read int|null $community_service_requests_count
 * @property-read int|null $faculties_count
 * @property-read int|null $free_pages_count
 * @property-read int|null $import_items_count
 * @property-read int|null $mac_addresses_count
 * @property-read int|null $mr_and_miss_votes_given_count
 * @property-read int|null $mr_and_miss_votes_got_count
 * @property-read Collection|PresenceCheck[] $presenceChecks
 * @property-read int|null $presence_checks_count
 * @property-read int|null $print_history_count
 * @property-read int|null $print_jobs_count
 * @property-read int|null $roles_count
 * @property-read Collection|SemesterEvaluation[] $semesterEvaluations
 * @property-read int|null $semester_evaluations_count
 * @property-read int|null $semester_statuses_count
 * @property-read int|null $transactions_paid_count
 * @property-read int|null $transactions_received_count
 * @property-read int|null $wifi_connections_count
 * @property-read int|null $workshops_count
 * @method static Builder|User canView()
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|User hasToPayKKTNetreg()
 * @method static Builder|User hasToPayKKTNetregInSemester(int $semester_id)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User verified()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRoom($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereVerified($value)
 * @method static Builder|User withRole(Role|string $role, Workshop|RoleObject|string|null $object = null)
 * @property-read Collection|\App\Models\Workshop[] $applicationCommitteWorkshops
 * @property-read int|null $application_committe_workshops_count
 * @property-read Collection|\App\Models\Workshop[] $roleWorkshops
 * @property-read int|null $role_workshops_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @mixin Eloquent
 */
class User extends Authenticatable implements HasLocalePreference
{
    use NotificationCounter;
    use Notifiable;
    use HasFactory;
    use HasRoles;


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
     */
    protected $fillable = [
        'name', 'email', 'password', 'verified', 'room'
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
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

    /*
    |--------------------------------------------------------------------------
    | Functions
    |--------------------------------------------------------------------------
    */

    public function generatePasswordResetToken(): string
    {
        return app(PasswordBroker::class)->createToken($this);
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
        return $this->hasOne(Application::class);
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

    /**
     * The user's internet access that contains wifi connections, mac addresses, etc.
     * @return hasOne
     */
    public function internetAccess(): HasOne
    {
        return $this->hasOne(InternetAccess::class);
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

    /**
     * Returns the general assembly presence checks the user has signed.
     * @return BelongsToMany
     */
    public function presenceChecks(): BelongsToMany
    {
        return $this->belongsToMany(PresenceCheck::class);
    }

    /**
     * @return HasMany The reservations the user has made.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * @return BelongsToMany The reservable items which the user has ever reserved.
     */
    public function reservableItems(): BelongsToMany
    {
        return $this->belongsToMany(ReservableItem::class, 'reservations', 'user_id', 'reservable_item_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Local scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include verified users. See also: global 'verified' scope.
     * @param Builder $query
     * @return Builder
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('users.verified', 1);
    }

    /**
     * Scope a query to only include users whose data can be accessed by the given user.
     * @param Builder $query
     * @return Builder
     */
    public function scopeCanView(Builder $query): Builder
    {
        /** @var Builder|static $query */

        if (user()->isAdmin()) {
            return $query;
        }
        if (user()->hasRole(Role::STAFF)) {
            return $query->withRole(Role::TENANT);
        }
        if (user()->can('viewAll', User::class)) {
            return $query->collegist();
        }
        if (user()->can('viewSome', User::class)) {
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
            return $query->withRole(Role::COLLEGIST)
                ->orWhere(function ($query) {
                    return $query->withRole(Role::ALUMNI);
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
        /** @var Builder|static $query */
        return $query->withRole(Role::COLLEGIST, Role::RESIDENT);
    }

    /**
     * Scope a query to only include extern users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExtern(Builder $query): Builder
    {
        /** @var Builder|static $query */
        return $query->withRole(Role::COLLEGIST, RoleObject::firstWhere('name', Role::EXTERN));
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
        /** @var Builder|static $query */
        return $query->withRole(Role::TENANT)
            ->whereHas('personalInformation', function ($q) {
                $q->where('tenant_until', '>', now());
            });
    }

    /**
     * Scope a query to only include users who have to pay kkt or netreg in the current semester.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeHasToPayKKTNetreg(Builder $query): Builder
    {
        /** @var Builder|static $query */
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
        /** @var Builder|static $query */
        return $query->withRole(Role::collegist())->active($semester_id)
            ->whereDoesntHave('transactionsPaid', function ($query) use ($semester_id) {
                $query->where('semester_id', $semester_id);
                $query->whereIn('payment_type_id', [PaymentType::kkt()->id, PaymentType::netreg()->id]);
            });
    }

    /**
     * Scope a query to only include users who got accepted in the specified year.
     */
    public function scopeYearOfAcceptance(Builder $query, int $yearOfAcceptance): Builder
    {
        return $query->whereHas('educationalInformation', function (Builder $query) use ($yearOfAcceptance) {
            $query->where('year_of_acceptance', $yearOfAcceptance);
        });
    }

    /**
     * Scope a query to only include users whose name contains the given string.
     */
    public function scopeNameLike(Builder $query, string $nameLike): Builder
    {
        return $query->where('name', 'like', '%' . $nameLike . '%');
    }

    /**
     * Scope a query to only include users who are in all the specified workshops.
     * The workshops are specified by their IDs.
     */
    public function scopeInAllWorkshopIds(Builder $query, array $workshopsIdsAll): Builder
    {
        return $query->whereHas('workshops', function (Builder $query) use ($workshopsIdsAll) {
            $query->whereIn('id', $workshopsIdsAll);
        }, '=', count($workshopsIdsAll));
    }

    /**
     * Scope a query to only include users who have any of the specified roles.
     */
    public function scopeHasStatusAnyOf(Builder $query, array $statusesAny): Builder
    {
        return $query->where(function ($query) use ($statusesAny) {
            foreach ($statusesAny as $status) {
                $query->orWhereHas('semesterStatuses', function (Builder $query) use ($status) {
                    $query->where('status', $status);
                    $query->where('id', Semester::current()->id);
                });
            }
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
        return $this->hasRole(Role::TENANT) ? 'en' : 'hu';
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
        if ($this->verified == false) {
            return $this->roles()->where('role_id', Role::collegist()->id)->exists();
        }

        return in_array(
            $this->id,
            Cache::remember('collegists', 60, function () {
                return Role::collegist()->getUsers()->pluck('id')->toArray();
            })
        ) || ($alumni === true && in_array(
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
        $this->addRole($role, $object);

        Cache::forget('collegists');
        WorkshopBalance::generateBalances(Semester::current());
    }

    /**
     * Decides if the user is a resident collegist currently.
     *
     * @return bool
     */
    public function isResident(): bool
    {
        if ($this->verified == false) {
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
        if ($this->verified == false) {
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
     * Determine if the user has an alumni role.
     * @return boolean
     */
    public function isAlumni(): bool
    {
        return $this->hasRole(Role::ALUMNI);
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

    /* Status related */

    /**
     * Sets the collegist's status for a semester.
     *
     * @param Semester $semester
     * @param string $status
     * @param null|string $comment
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
     * @param string $status
     * @param null|string $comment
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

        return $this->semesterStatuses->find($semester ?? Semester::current())?->getRelationValue("pivot");
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
     * Returns the paid kkt amount in the semester; or null if the user has not paid kkt.
     * @param Semester $semester
     * @return ?int
     */
    public function paidKKTInSemester(Semester $semester): ?int
    {
        $transaction = $this->transactionsPaid()
            ->where('payment_type_id', PaymentType::kkt()->id)
            ->where('semester_id', $semester->id)
            ->first();

        return $transaction ? $transaction->amount : null;
    }

    /**
     * Returns the paid kkt amount in the current semester; or null if the user has not paid kkt.
     * @return ?int
     */
    public function paidKKT(): ?int
    {
        return $this->paidKKTInSemester(Semester::current());
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
        return self::withRole(Role::SYS_ADMIN)->get();
    }

    /**
     * @return array|User[]|Collection the collegists (without alumni)
     */
    public static function collegists(): Collection|array
    {
        return self::withRole(Role::COLLEGIST)->get();
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
        return self::withRole(Role::STUDENT_COUNCIL, Role::PRESIDENT)->first();
    }

    /**
     * @return User|null the president
     */
    public static function studentCouncilSecretary(): ?User
    {
        return self::withRole(Role::STUDENT_COUNCIL_SECRETARY)->first();
    }

    /**
     * @return array|User[]|Collection board of trustees members
     */
    public static function boardOfTrusteesMembers(): Collection|array
    {
        return self::withRole(Role::BOARD_OF_TRUSTEES_MEMBER)->get();
    }

    /**
     * @return array|User[]|Collection ethics commitioners
     */
    public static function ethicsCommissioners(): Collection|array
    {
        return self::withRole(Role::ETHICS_COMMISSIONER)->get();
    }

    /**
     * @return User|null the director
     */
    public static function director(): ?User
    {
        return self::withRole(Role::director())->first();
    }

    /**
     * @return User|null the secretary
     */
    public static function secretary(): ?User
    {
        return self::withRole(Role::SECRETARY)->first();
    }

    /**
     * @return array|User[]|Collection workshop leaders
     */
    public static function workshopLeaders(): Collection|array
    {
        return self::withRole(Role::WORKSHOP_LEADER)->get();
    }

    /**
     * @return User|null the head of the staff
     */
    public static function staff(): ?User
    {
        return self::withRole(Role::STAFF)->first();
    }
    /**
     * @return array|Collection|User[] the users with printer role
     */
    public static function tenants(): Collection|array
    {
        return self::withRole(Role::TENANT)->get();
    }

    /**
     * Returns how many not verified users there are currently.
     * Used by the NotificationCounter trait.
     * @return int
     */
    public static function notificationCount(): int
    {
        return self::withoutGlobalScope('verified')
            ->whereHas('roles', function ($q) {
                $q->where('role_id', Role::get(Role::TENANT));
            })
            ->where('verified', false)->count();
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
        // Use local "verified" scope for queries that run automatically without Auth::user().
        static::addGlobalScope('verified', function (Builder $builder) {
            /** @var Builder|static $builder */
            if (Auth::hasUser() && user()->verified) {
                return $builder->verified();
            }
        });
    }
}
