<?php

namespace App\Utils;

use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * A class that can be used to filter {@see User} instances in queries based on various criteria.
 *
 * Usage:
 * <ol>
 * <li>Create a user query that should be further narrowed down, e.g. {@see User::canView()}</li>
 * <li>Create a new class instance with the previously obtained query instance</li>
 * <li>Call the desired filter methods on the instance to narrow down the query</li>
 * <li>Get the final query by calling {@see UserFilter::getQuery()} on the instance</li>
 * </ol>
 */
class UserFilter
{
    /**
     * @var Builder|User the query that will return the filtered users
     */
    private Builder|User $query;

    /**
     * @param Builder|User $initialQuery the query that should be used as a base for the filtering:
     * additional conditions will be added to this query (the instance is cloned)
     */
    public function __construct(Builder|User $initialQuery)
    {
        $this->query = $initialQuery->clone(); //Clone to achieve encapsulation
    }

    /**
     * @return Builder|User the query that will return the filtered users
     */
    public function getQuery(): Builder|User
    {
        return $this->query;
    }

    /**
     * @param int $yearOfAcceptance the year in which the filtered users must have been accepted
     * @return $this the current filter instance (for chaining)
     */
    public function yearOfAcceptance(int $yearOfAcceptance): UserFilter
    {
        $this->query->whereHas('educationalInformation', function (Builder $query) use ($yearOfAcceptance) {
            $query->where('year_of_acceptance', $yearOfAcceptance);
        });
        return $this;
    }

    /**
     * @param string $nameLike the string that must be a substring of the filtered users' name
     * @return $this the current filter instance (for chaining)
     */
    public function nameLike(string $nameLike): UserFilter
    {
        $this->query->where('name', 'like', '%' . $nameLike . '%');
        return $this;
    }

    /**
     * @param int[] $roleIdsAll the IDs of roles all of which the filtered users must have
     * @return $this the current filter instance (for chaining)
     */
    public function roleIdsAll(array $roleIdsAll): UserFilter
    {
        foreach ($roleIdsAll as $roleId) {
            $this->query->whereHas('roles', function (Builder $query) use ($roleId) {
                $query->where('id', $roleId);
            });
        }
        return $this;
    }

    /**
     * @param int[] $workshopsIdsAll the IDs of workshops all of which the filtered users must be a member of
     * @return $this the current filter instance (for chaining)
     */
    public function workshopIdsAll(array $workshopsIdsAll): UserFilter
    {
        foreach ($workshopsIdsAll as $workshopId) {
            $this->query->whereHas('workshops', function (Builder $query) use ($workshopId) {
                $query->where('id', $workshopId);
            });
        }
        return $this;
    }

    /**
     * @param SemesterStatus[] $statusesAny the statuses any of which the filtered users must have
     * @return $this the current filter instance (for chaining)
     */
    public function statusesAny(array $statusesAny): UserFilter
    {
        $this->query->where(function ($query) use ($statusesAny) {
            foreach ($statusesAny as $status) {
                $query->orWhereHas('semesterStatuses', function (Builder $query) use ($status) {
                    $query->where('status', $status);
                    $query->where('id', Semester::current()->id);
                });
            }
        });
        return $this;
    }
}
