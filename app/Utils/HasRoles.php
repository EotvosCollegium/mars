<?php

namespace App\Utils;

use App\Models\Role;
use App\Models\RoleObject;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

/**
 * Helper class for role getters/setters.
 */
trait HasRoles
{
    abstract public function roles(): BelongsToMany;

    /**
     * Scope a query to only include users with the given role.
     * Usage: ->withRole(...)
     * See also: hasRole(...) getter for User models.
     *
     * @param Builder $query
     * @param Role|string $role
     * @param Workshop|RoleObject|string|null $object
     * @return Builder
     */
    public function scopeWithRole(Builder $query, Role|string $role, Workshop|RoleObject|string $object = null): Builder
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
     * Decides if the user has any of the given roles.
     * See also: withRole(...) scope for query builders.
     *
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
                //delete other object, if exists
                $this->roles()->detach($role->id);
                $this->roles()->attach($role->id, ['object_id' => $object->id]);
            }
            elseif ($this->roles()->where('id', $role->id)->wherePivot('object_id', $object->id)->doesntExist()) {
                $this->roles()->attach($role->id, ['object_id' => $object->id]);
            }
        } elseif ($role->has_workshops) {
            if ($this->roles()->where('id', $role->id)->wherePivot('workshop_id', $object->id)->doesntExist()) {
                $this->roles()->attach($role->id, ['workshop_id' => $object->id]);
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



}
