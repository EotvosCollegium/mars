<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * App\Models\Role
 *
 * @property string $name
 * @property string $translatedName
 * @property boolean $has_objects
 * @property boolean $has_workshops
 * @property integer $id
 * @property-read Collection|\App\Models\RoleObject[] $objects
 * @property-read int|null $objects_count
 * @property-read Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereHasObjects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereHasWorkshops($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @mixin \Eloquent
 */
class Role extends Model
{
    // General roles
    public const SYS_ADMIN = 'sys-admin';
    public const COLLEGIST = 'collegist';
    public const TENANT = 'tenant';
    public const WORKSHOP_ADMINISTRATOR = 'workshop-administrator';
    public const WORKSHOP_LEADER = 'workshop-leader';
    public const APPLICATION_COMMITTEE_MEMBER = 'application-committee';
    public const AGGREGATED_APPLICATION_COMMITTEE_MEMBER = 'aggregated-application-committee';
    public const SECRETARY = 'secretary';
    public const DIRECTOR = 'director';
    public const STAFF = 'staff';
    public const LOCALE_ADMIN = 'locale-admin';
    public const STUDENT_COUNCIL = 'student-council';
    public const STUDENT_COUNCIL_SECRETARY = 'student-council-secretary';
    public const BOARD_OF_TRUSTEES_MEMBER = 'board-of-trustees-member';
    public const ETHICS_COMMISSIONER = 'ethics-commissioner';
    public const ALUMNI = 'alumni';
    public const RECEPTIONIST = 'receptionist';

    //Students' Committe role's objects
    public const PRESIDENT = 'president';
    public const ECONOMIC_VICE_PRESIDENT = 'economic-vice-president';
    public const SCIENCE_VICE_PRESIDENT = 'science-vice-president';
    public const CULTURAL_LEADER = 'cultural-leader';
    public const CULTURAL_REFERENT = 'cultural-referent';
    public const CULTURAL_MEMBER = 'cultural-member';
    public const KKT_HANDLER = 'kkt-handler';
    public const COMMUNITY_LEADER = 'community-leader';
    public const COMMUNITY_REFERENT = 'community-referent';
    public const COMMUNITY_MEMBER = 'community-member';
    public const COMMUNICATION_LEADER = 'communication-leader';
    public const COMMUNICATION_REFERENT = 'communication-referent';
    public const COMMUNICATION_MEMBER = 'communication-member';
    public const SPORT_LEADER = 'sport-leader';
    public const SPORT_REFERENT = 'sport-referent';
    public const SPORT_MEMBER = 'sport-member';
    public const STUDENT_COUNCIL_LEADERS = [
        self::PRESIDENT,
        self::SCIENCE_VICE_PRESIDENT,
        self::ECONOMIC_VICE_PRESIDENT
    ];
    public const COMMITTEE_LEADERS = [
        self::CULTURAL_LEADER,
        self::COMMUNITY_LEADER,
        self::COMMUNICATION_LEADER,
        self::SPORT_LEADER,
    ];
    public const COMMITTEE_REFERENTS = [
        self::CULTURAL_REFERENT,
        self::COMMUNITY_REFERENT,
        self::COMMUNICATION_REFERENT,
        self::SPORT_REFERENT,
    ];
    public const COMMITTEE_MEMBERS = [
        self::CULTURAL_MEMBER,
        self::COMMUNITY_MEMBER,
        self::COMMUNICATION_MEMBER,
        self::SPORT_MEMBER,
        self::KKT_HANDLER,
    ];

    public const STUDENT_POSTION_ROLES = [
        self::SYS_ADMIN,
        self::WORKSHOP_ADMINISTRATOR,
        self::STUDENT_COUNCIL_SECRETARY,
        self::STUDENT_COUNCIL,
        self::BOARD_OF_TRUSTEES_MEMBER,
        self::ETHICS_COMMISSIONER,
    ];

    //collegist related roles
    public const RESIDENT = 'resident';
    public const EXTERN = 'extern';

    // all roles
    public const ALL = [
        self::SYS_ADMIN,
        self::COLLEGIST,
        self::TENANT,
        self::WORKSHOP_ADMINISTRATOR,
        self::WORKSHOP_LEADER,
        self::APPLICATION_COMMITTEE_MEMBER,
        self::AGGREGATED_APPLICATION_COMMITTEE_MEMBER,
        self::SECRETARY,
        self::DIRECTOR,
        self::STAFF,
        self::LOCALE_ADMIN,
        self::STUDENT_COUNCIL,
        self::STUDENT_COUNCIL_SECRETARY,
        self::BOARD_OF_TRUSTEES_MEMBER,
        self::ETHICS_COMMISSIONER,
        self::ALUMNI,
        self::RECEPTIONIST
    ];

    protected $fillable = [
        'name', 'has_objects', 'has_workshops'
    ];

    protected $casts = [
        'has_objects' => 'boolean',
        'has_workshops' => 'boolean'
    ];

    public $timestamps = false;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_users')
            ->withPivot(['object_id', 'workshop_id'])->using(RoleUser::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(RoleObject::class, 'role_id');
    }

    /**
     * Returns the role model for the given role name/id.
     */
    public static function get(Role|string|int $role): Role
    {
        if ($role instanceof Role) {
            return $role;
        }
        return Cache::remember('role_' . $role, 86400, function () use ($role) {
            if (is_numeric($role)) {
                $role = Role::find((int)$role);
            } else {
                $role = Role::where('name', $role)->first();
            }

            if (!$role) {
                throw new InvalidArgumentException('Role not found: ' . $role);
            }

            return $role;
        });
    }

    /**
     * Returns the role object belonging to the role while checking the validity of the role-object pair.
     * @param integer|string|null $object roleObject or workshop name/id
     * @return RoleObject|Workshop|null
     * @throws InvalidArgumentException
     */
    public function getObject(int|string|Workshop|RoleObject $object = null): Workshop|RoleObject|null
    {
        if ($object instanceof Workshop) {
            return $object;
        }
        if ($object instanceof RoleObject) {
            return $object;
        }
        return Cache::remember('role_' . $this->id . '_object_' . $object, 86400, function () use ($object) {
            /* @var RoleObject|Workshop|null $object */
            if ($this->has_objects && is_numeric($object)) {
                $object = $this->objects()->find((int)$object);
            } elseif ($this->has_objects) {
                $object = $this->objects()->firstWhere('name', $object);
            } elseif ($this->has_workshops && is_numeric($object)) {
                $object = Workshop::find((int)$object);
            } elseif ($this->has_workshops) {
                $object = Workshop::firstWhere('name', $object);
            } elseif (!isset($object)) {
                $object = null;
            }
            if (!$this->isValid($object)) {
                throw new InvalidArgumentException("Role object/workshop '" . $object . "' does not exist for the " . $this->name . " role.");
            }
            return $object;
        });
    }

    /**
     * Checks if a role-object pair is valid.
     * @param RoleObject|Workshop|null $object
     */
    public function isValid(Workshop|RoleObject $object = null): bool
    {
        if ($this->has_objects
            && $object instanceof RoleObject
            && $this->objects()->where('id', $object->id)->exists()) {
            return true;
        }
        if ($this->has_workshops && $object instanceof Workshop) {
            return true;
        }
        if (!$this->has_workshops && !$this->has_objects && !isset($object)) {
            return true;
        }
        return false;
    }


    /**
     * Returns the users with the given role.
     * @param RoleObject|Workshop|null $object
     * @return Collection|User[]
     */
    public function getUsers(Workshop|RoleObject $object = null): Collection|array
    {
        return User::withRole($this, $object)->get();
    }

    /**
     * Returns the role for the collegist.
     */
    public static function collegist(): Role
    {
        return self::where('name', self::COLLEGIST)->first();
    }

    /**
     * Returns the role for the students council.
     */
    public static function studentsCouncil(): Role
    {
        return self::where('name', self::STUDENT_COUNCIL)->first();
    }

    /**
     * Returns the role for the director.
     */
    public static function director(): Role
    {
        return self::where('name', self::DIRECTOR)->first();
    }

    /**
     * Returns the role for the system administrators.
     */
    public static function sysAdmin(): Role
    {
        return self::where('name', self::SYS_ADMIN)->first();
    }

    /**
     * Returns the role for the alumni.
     */
    public static function alumni(): Role
    {
        return self::where('name', self::ALUMNI)->first();
    }

    /**
     * Get the translated_name attribute.
     *
     * @return Attribute
     */
    public function translatedName(): Attribute
    {
        return Attribute::make(
            get: fn () => __('role.' . $this->name)
        );
    }


    public function color(): string
    {
        return match ($this->name) {
            self::SYS_ADMIN => 'pink',
            self::COLLEGIST => 'coli',
            self::TENANT => 'coli blue',
            self::WORKSHOP_ADMINISTRATOR => 'purple',
            self::WORKSHOP_LEADER => 'deep-purple',
            self::SECRETARY => 'indigo',
            self::DIRECTOR => 'blue',
            self::STAFF => 'cyan',
            self::LOCALE_ADMIN => 'amber',
            self::STUDENT_COUNCIL => 'green darken-4',
            self::APPLICATION_COMMITTEE_MEMBER => 'light-blue darken-4',
            self::AGGREGATED_APPLICATION_COMMITTEE_MEMBER => 'yellow darken-4',
            self::STUDENT_COUNCIL_SECRETARY => 'pink lighten-3',
            self::BOARD_OF_TRUSTEES_MEMBER => 'deep-orange darken-1',
            self::ETHICS_COMMISSIONER => 'green lighten-2',
            self::ALUMNI => 'grey darken-1',
            self::RECEPTIONIST => 'brown',
            default => 'grey',
        };
    }
}
