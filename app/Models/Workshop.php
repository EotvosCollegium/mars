<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Workshop
 *
 * @property integer $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Workshop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Workshop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Workshop query()
 * @method static \Illuminate\Database\Eloquent\Builder|Workshop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Workshop whereName($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkshopBalance> $balances
 * @property-read int|null $balances_count
 * @mixin \Eloquent
 */
class Workshop extends Model
{
    public const ANGOL = 'Angol-amerikai műhely';
    public const BIOLOGIA = 'Biológia-kémia műhely';
    public const BOLLOK = 'Bollók János Klasszika-filológia műhely';
    public const FILOZOFIA = 'Filozófia műhely';
    public const AURELION = 'Aurélien Sauvageot Francia műhely';
    public const GAZDALKODASTUDOMANYI = 'Gazdaságtudományi műhely';
    public const GERMANISZTIKA = 'Germanisztika műhely';
    public const INFORMATIKA = 'Informatika műhely';
    public const MAGYAR = 'Magyar műhely';
    public const MATEMATIKA = 'Matematika-fizika műhely';
    public const MENDOL = 'Mendöl Tibor földrajz-, föld- és környezettudományi műhely';
    public const OLASZ = 'Olasz műhely';
    public const ORIENTALISZTIKA = 'Orientalisztika műhely';
    public const SKANDINAVISZTIKA = 'Skandinavisztika műhely';
    public const SPANYOL = 'Spanyol műhely';
    public const SZLAVISZTIKA = 'Szlavisztika műhely';
    public const TARSADALOMTUDOMANYI = 'Társadalomtudományi műhely';
    public const TORTENESZ = 'Történész műhely';

    public const ALL = [
        self::ANGOL,
        self::BIOLOGIA,
        self::BOLLOK,
        self::FILOZOFIA,
        self::AURELION,
        self::GAZDALKODASTUDOMANYI,
        self::GERMANISZTIKA,
        self::INFORMATIKA,
        self::MAGYAR,
        self::MATEMATIKA,
        self::MENDOL,
        self::OLASZ,
        self::ORIENTALISZTIKA,
        self::SKANDINAVISZTIKA,
        self::SPANYOL,
        self::SZLAVISZTIKA,
        self::TARSADALOMTUDOMANYI,
        self::TORTENESZ,
    ];

    public const COLORS = [
        self::ANGOL => 'deep-purple lighten-3',
        self::BIOLOGIA => 'green lighten-2',
        self::BOLLOK => 'teal lighten-2',
        self::FILOZOFIA => 'teal accent-4',
        self::AURELION => 'lime darken-2',
        self::GAZDALKODASTUDOMANYI => 'brown lighten-2',
        self::GERMANISZTIKA => 'blue-grey lighten-2',
        self::INFORMATIKA => 'light-blue darken-4',
        self::MAGYAR => 'red lighten-2',
        self::MATEMATIKA => 'blue darken-2',
        self::MENDOL => 'cyan darken-2',
        self::OLASZ => 'red accent-3',
        self::ORIENTALISZTIKA => 'amber lighten-1',
        self::SKANDINAVISZTIKA => 'deep-orange lighten-3',
        self::SPANYOL => 'deep-purple darken-2',
        self::SZLAVISZTIKA => 'light-blue lighten-2',
        self::TARSADALOMTUDOMANYI => 'purple lighten-1',
        self::TORTENESZ => 'teal darken-4',
    ];

    /**
     * Defines the BelongsToMany connection to users.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workshop_users');
    }

    /**
     * Returns a collection containing the residents in the workshop.
     */
    public function residents()
    {
        return $this->users->filter(function ($user, $key) {
            return $user->isResident();
        });
    }

    /**
     * Returns a collection containing the externs in the workshop.
     */
    public function externs()
    {
        return $this->users->filter(function ($user, $key) {
            return $user->isExtern();
        });
    }

    /**
     * Returns a collection with all functionaries of the workshop in it.
     */
    public function functionaries(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_users');
    }

    /**
     * Filters functionaries to only include workshop administrators.
     */
    public function administrators(): BelongsToMany
    {
        return $this->functionaries()->wherePivot('role_id', Role::get(Role::WORKSHOP_ADMINISTRATOR)->id);
    }

    /**
     * Filters functionaries to only include workshop leaders.
     */
    public function leaders(): BelongsToMany
    {
        return $this->functionaries()->wherePivot('role_id', Role::get(Role::WORKSHOP_LEADER)->id);
    }

    /**
     * Returns all the balances the workshop has had in different semesters.
     */
    public function balances(): HasMany
    {
        return $this->hasMany(WorkshopBalance::class);
    }

    /**
     * Returns the balance for a given semester
     * (by default the current one).
     */
    public function balance(int $semester = null): ?WorkshopBalance
    {
        return $this->balances()->firstWhere('semester_id', $semester ?? Semester::current()->id);
    }

    /**
     * Associates each workshop with a fixed color.
     */
    public function color(): string
    {
        return isset(self::COLORS[$this->name]) ? self::COLORS[$this->name] : 'black';
    }
}
