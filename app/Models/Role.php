<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    // all roles
    public const ALL = [
        self::PRINT_ADMIN,
        self::NETWORK_ADMIN,
        self::COLLEGIST,
        self::TENANT,
        self::WORKSHOP_ADMINISTRATOR,
        self::WORKSHOP_LEADER,
        self::APPLICATION_COMMITTEE_MEMBER,
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_users')->withPivot('object_id');
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
     * @param int $roleId
     * @param string $objectName optional. Returns false if the object is null for a role which can have objects.
     * @return bool
     */
    public static function isUnique($roleName, $objectName = null): bool
    {
        switch ($roleName) {
            case self::DIRECTOR:
                return true;
            case self::WORKSHOP_ADMINISTRATOR:
                return true;
            case self::WORKSHOP_LEADER:
                return true;
            case self::STUDENT_COUNCIL:
                return $objectName == self::PRESIDENT || in_array($objectName, self::COMMITTEE_LEADERS);
            default:
                return false;
        }
    }

    /**
     * Checks if the specified role can be attached to someone.
     * Object id is required if the role can have objects.
     * @param int $roleId
     * @param int $objectId optional
     * @return bool.
     */
    public static function canBeAttached($roleId, $objectId = null): bool
    {
        $role = self::findOrFail($roleId);
        if ($role->canHaveObject()) {
            if ($objectId == null) {
                return false;
            }
            $object = $role->possibleObjects()->firstWhere('id', $objectId)->name;
        }

        if (self::isUnique($role->name, ($object ?? null))) {
            return User::whereHas('roles', function (Builder $query) use ($roleId, $objectId) {
                $query->where('id', $roleId)->where('role_users.object_id', $objectId);
            })->count() < 1;
        }
        return true;
    }

    /**
     * Returns the object (with id and name) of the role or null if the role does not have.
     */
    public function object()
    {
        if (!$this->canHaveObject()) {
            return null;
        }

        return $this->possibleObjects()->where('id', $this->pivot->object_id)->first();
    }

    public static function getObjectIdByName($roleName, $objectName)
    {
        $objects = self::possibleObjectsFor($roleName);
        return $objects->where('name', $objectName)->first()->id;
    }

    public static function getUsers(string $roleName, string $objectName = null)
    {
        if (isset($objectName)) {
            return User::whereHas('roles', function ($q) use ($roleName, $objectName) {
                $q->where('role_id', Role::getId($roleName))
                    ->where('object_id', Role::getObjectIdByName($roleName, $objectName));
            });
        }
        return self::firstWhere('name', $roleName)->users;
    }

    public static function getId(string $roleName)
    {
        return self::where('name', $roleName)->first()->id;
    }

    /**
     * Returns if the role can have objects
     */
    public function canHaveObject(): bool
    {
        return self::canHaveObjectFor($this->name);
    }

    /**
     * Returns if the role can have objects.
     * @return string object name
     */
    public static function canHaveObjectFor($name): bool
    {
        // TODO: PERMISSION_HANDLER could also be there
        return in_array($name, [
            self::WORKSHOP_ADMINISTRATOR,
            self::WORKSHOP_LEADER,
            self::APPLICATION_COMMITTEE_MEMBER,
            self::LOCALE_ADMIN,
            self::STUDENT_COUNCIL,
            self::COLLEGIST
        ]);
    }

    /**
     * Returns the collection of the possible object for the role.
     * @return collection of the objects with id (starting from 1, except workshops?) and name.
     */
    public function possibleObjects()
    {
        return self::possibleObjectsFor($this->name);
    }

    /**
     * Returns the collection of the possible object for a role.
     * @param string $name the role's name
     * @return collection of the objects with id (starting from 1, except workshops?) and name.
     */
    public static function possibleObjectsFor($name)
    {
        if (in_array($name, [self::WORKSHOP_ADMINISTRATOR, self::WORKSHOP_LEADER, self::APPLICATION_COMMITTEE_MEMBER])) {
            return Workshop::all();
        }
        if ($name == self::LOCALE_ADMIN) {
            $locales = array_keys(config('app.locales'));

            return self::toSelectableCollection($locales);
        }

        if ($name == self::STUDENT_COUNCIL) {
            $student_council_members = array_merge(self::STUDENT_COUNCIL_LEADERS, self::COMMITTEE_LEADERS, self::COMMITTEE_MEMBERS);

            return self::toSelectableCollection($student_council_members);
        }

        if ($name == self::COLLEGIST) {
            $collegists = [
                'resident',
                'extern',
            ];

            return self::toSelectableCollection($collegists);
        }

        return collect([]);
    }

    public function hasElevatedPermissions(): bool
    {
        return in_array($this->name, [self::PRINT_ADMIN, self::NETWORK_ADMIN, self::PERMISSION_HANDLER, self::SECRETARY, self::DIRECTOR]);
    }

    public function hasTranslatedName(): bool
    {
        return in_array($this->name, [self::WORKSHOP_ADMINISTRATOR, self::WORKSHOP_LEADER]);
    }

    /**
     * Create a collection with id and name of the items in an array.
     * The ids starts at 1.
     * @param array $items the items in the array will be the name attributes
     * @return collection
     */
    private static function toSelectableCollection(array $items)
    {
        $objects = [];
        $id = 1;
        foreach ($items as $name) {
            $objects[] = (object) ['id' => $id++, 'name' => $name];
        }

        return collect($objects);
    }

    public function isSysAdmin()
    {
        return in_array($this->name, [self::NETWORK_ADMIN]);
    }

    public function color()
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
            default:
                return 'grey';
        }
    }
}
