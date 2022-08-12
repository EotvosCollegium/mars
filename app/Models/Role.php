<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * @property string $name
 * @property boolean $has_objects
 * @property boolean $has_workshops
 * @property integer $id
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

    //Students' Committe role's objects
    public const PRESIDENT = 'president';
    public const VICE_PRESIDENT = 'vice-president';
    public const ECONOMIC_LEADER = 'economic-leader';
    public const ECONOMIC_MEMBER = 'economic-member';
    public const CULTURAL_LEADER = 'cultural-leader';
    public const CULTURAL_MEMBER = 'cultural-member';
    public const COMMUNITY_LEADER = 'community-leader';
    public const COMMUNITY_MEMBER = 'community-member';
    public const COMMUNICATION_LEADER = 'communication-leader';
    public const COMMUNICATION_MEMBER = 'communication-member';
    public const SPORT_LEADER = 'sport-leader';
    public const SPORT_MEMBER = 'sport-member';
    public const SCIENCE_LEADER = 'science-leader';
    public const SCIENCE_MEMBER = 'science-member';
    public const STUDENT_COUNCIL_LEADERS = [
        self::PRESIDENT,
        self::VICE_PRESIDENT
    ];
    public const COMMITTEE_LEADERS = [
        self::ECONOMIC_LEADER,
        self::CULTURAL_LEADER,
        self::COMMUNITY_LEADER,
        self::COMMUNICATION_LEADER,
        self::SPORT_LEADER,
        self::SCIENCE_LEADER
    ];
    public const COMMITTEE_MEMBERS = [
        self::ECONOMIC_MEMBER,
        self::CULTURAL_MEMBER,
        self::COMMUNITY_MEMBER,
        self::COMMUNICATION_MEMBER,
        self::SPORT_MEMBER,
        self::SCIENCE_MEMBER
    ];

    // Module-related roles
    public const PRINTER = 'printer';
    public const INTERNET_USER = 'internet-user';

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
        self::PRINTER,
        self::INTERNET_USER,
        self::LOCALE_ADMIN,
        self::STUDENT_COUNCIL,
    ];

    protected $fillable = [
        'name',
    ];


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
     * Returns the role object belonging to the role while checking the validity of the role-object pair.
     * @param integer|string|null $object roleObject or workshop name/id
     * @return RoleObject|Workshop|null
     * @throws InvalidArgumentException
     */
    public function getObject(int|string $object = null) : Workshop|RoleObject|null
    {
        /* @var RoleObject|Workshop|null $object */
        if($this->has_objects && is_numeric($object)) {
            $object = $this->objects()->find($object);
        } else if($this->has_objects){
            $object = $this->objects()->firstWhere('name', $object);
        } else if($this->has_workshops && is_numeric($object)){
            $object = Workshop::find($object);
        } else if($this->has_workshops){
            $object = Workshop::firstWhere('name', $object);

        } else if(!isset($object)){
            $object = null;
        }

        if(!$this->isValid($object))
            throw new InvalidArgumentException("Role object/workshop '".$object."' does not exist for the " . $this->name . " role.");

        return $object;
    }
    /**
     * Checks if a role-object pair is valid.
     * @param RoleObject|Workshop|null $object
     */
    public function isValid(Workshop|RoleObject $object = null): bool
    {
        if($this->has_objects
            && $object instanceof RoleObject
            && $this->objects()->where('id', $object->id)->exists())
            return true;
        if($this->has_workshops && $object instanceof Workshop) return true;
        if(!$this->has_workshops && !$this->has_objects && !isset($object)) return true;
        return false;
    }


    /**
     * Returns the users with the given role.
     * @param RoleObject|Workshop|null $object
     * @return Collection|User[]
     */
    public function getUsers(Workshop|RoleObject $object = null): Collection|array
    {
        if($this->has_objects)
        {
            $object = $this->getObject($object);
            return User::whereHas('roles', function ($q) use ($object) {
                $q->where('role_id', $this->id)
                    ->where('object_id', $object->id);
            })->get();

        }
        else if($this->has_workshops)
        {
            if(!($object instanceof Workshop))
                throw new InvalidArgumentException("Role object must be a Workshop instance for the " . $this->name . " role.");
            return User::whereHas('roles', function ($q) use ($object) {
                $q->where('role_id', $this->id)
                    ->where('workshop_id', $object->id);
            })->get();
        }
        if(isset($object)) {
            throw new InvalidArgumentException($this->name . " role must have an object");
        }
        return User::whereHas('roles', function ($q) use ($object) {
            $q->where('role_id', $this->id);
        })->get();
    }


    public static function Collegist() : Role
    {
        return self::where('name', self::COLLEGIST)->first();
    }

    public static function StudentsCouncil()
    {
        return self::where('name', self::STUDENT_COUNCIL)->first();

    }

    public function getTranslatedNameAttribute()
    {
        return __('role.'.$this->name);
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
            self::PRINTER => 'teal',
            self::INTERNET_USER => 'light-green',
            self::LOCALE_ADMIN => 'amber',
            self::STUDENT_COUNCIL => 'green darken-4',
            self::APPLICATION_COMMITTEE_MEMBER => 'light-blue darken-4',
            default => 'grey',
        };
    }
}
