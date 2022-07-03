<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property boolean $has_objects
 * @property boolean $has_workshops
 * @property integer $id
 */
class Role extends Model
{
    // General roles
    public const PRINT_ADMIN = 'print-admin';
    public const NETWORK_ADMIN = 'internet-admin';
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
    public const PERMISSION_HANDLER = 'permission-handler';
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
        self::PRINT_ADMIN,
        self::NETWORK_ADMIN,
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
        self::PERMISSION_HANDLER,
        self::STUDENT_COUNCIL,
    ];

    protected $fillable = [
        'name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_users')->using(RoleUser::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(RoleObject::class, 'role_id');
    }

    public function getObject(string $objectName) : RoleObject
    {
        /* @var RoleObject|null $object */
        $object = $this->objects()->where('name', $objectName)->first();
        if(!$object)
            throw new InvalidArgumentException($objectName . "role object does not exist for the " . $this->name . " role.");
        return $object;
    }

    /**
     * @return string the translated role
     */
    public function name(): string
    {
        return __('role.' . $this->name);
    }

    /**
     * Returns true if the role can be attached to only one user at a time.
     * @param RoleObject|null $object. Returns false if the object is null for a role which can have objects.
     * @return bool
     */
    public function isUnique(RoleObject $object = null): bool
    {
        switch ($this->name) {
            case self::WORKSHOP_LEADER:
            case self::WORKSHOP_ADMINISTRATOR:
            case self::DIRECTOR:
                return true;
//            case self::WORKSHOP_ADMINISTRATOR:
//                return true;
//            case self::WORKSHOP_LEADER:
//                return true;
            case self::STUDENT_COUNCIL:
                return $object && (
                        $object->name == self::PRESIDENT
                        || in_array($object->name, self::COMMITTEE_LEADERS)
                    );
            default:
                return false;
        }
    }

    /**
     * Checks if the specified role can be attached to someone.
     * Object id is required if the role can have objects.
     * @param RoleObject|Workshop|null $object
     * @return bool.
     */
    public function canBeAttached($object = null): bool
    {
        if ($this->has_objects) {
            if (!isset($object)) {
                return false;
            }
        }

        if ($this->isUnique($object)) {
            if(isset($object) && $object instanceof RoleObject){
                return DB::table('role_users')
                        ->where('role_id', $this->id)
                        ->where('object_id', $object->id)
                        ->count() < 1;
            } else if (isset($object) && $object instanceof Workshop){
                return DB::table('role_users')
                        ->where('role_id', $this->id)
                        ->where('workshop_id', $object->id)
                        ->count() < 1;
            } else {
                DB::table('role_users')->where('role_id', $this->id)->count() < 1;
            }
        }
        return true;
    }

    /**
     * Returns the users with the given role.
     * @param RoleObject|Workshop|null $object
     * @return Collection|User[]
     */
    public function getUsers($object = null)
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


    public function color(): string
    {
        switch ($this->name) {
            case self::PRINT_ADMIN:
                return 'red';
            case self::NETWORK_ADMIN:
                return 'pink';
            case self::COLLEGIST:
                return 'coli';
            case self::TENANT:
                return 'coli blue';
            case self::WORKSHOP_ADMINISTRATOR:
                return 'purple';
            case self::WORKSHOP_LEADER:
                return 'deep-purple';
            case self::SECRETARY:
                return 'indigo';
            case self::DIRECTOR:
                return 'blue';
            case self::STAFF:
                return 'cyan';
            case self::PRINTER:
                return 'teal';
            case self::INTERNET_USER:
                return 'light-green';
            case self::LOCALE_ADMIN:
                return 'amber';
            case self::PERMISSION_HANDLER:
                return 'deep-orange';
            case self::STUDENT_COUNCIL:
                return 'green darken-4';
            case self::APPLICATION_COMMITTEE_MEMBER:
                return 'light-blue darken-4';
            case self::AGGREGATED_APPLICATION_COMMITTEE_MEMBER:
                return 'light-blue darken-5';
            default:
                return 'grey';
        }
    }
}
