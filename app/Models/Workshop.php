<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    public const ANGOL = 'Angol-amerikai műhely';
    public const BIOLOGIA = 'Biológia-kémia műhely';
    public const BOLLOK = 'Bollók János Klasszika-filológia műhely';
    public const FILOZOFIA = 'Filozófia műhely';
    public const AURELION = 'Aurélien Sauvageot Francia műhely';
    public const GAZDALKODASTUDOMANYI = 'Gazdálkodástudományi műhely';
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'workshop_users');
    }

    public function activeMembers()
    {
        return $this->activeMembersInSemester(Semester::current());
    }

    public function activeMembersInSemester(Semester $semester)
    {
        return $this->filter(function ($user, $key) use ($semester) {
            return $user->isActiveInSemester($semester);
        });
    }

    public function residents()
    {
        return $this->users->filter(function ($user, $key) {
            return $user->isResident();
        });
    }

    public function externs()
    {
        return $this->users->filter(function ($user, $key) {
            return $user->isExtern();
        });
    }

    public function color()
    {
        switch ($this->name) {
            case self::ANGOL:
                return 'deep-purple lighten-3';
            case self::BIOLOGIA:
                return 'green lighten-2';
            case self::BOLLOK:
                return 'teal lighten-2';
            case self::FILOZOFIA:
                return 'teal accent-4';
            case self::AURELION:
                return 'lime darken-2';
            case self::GAZDALKODASTUDOMANYI:
                return 'brown lighten-2';
            case self::GERMANISZTIKA:
                return 'blue-grey lighten-2';
            case self::INFORMATIKA:
                return 'light-blue darken-4';
            case self::MAGYAR:
                return 'red lighten-2';
            case self::MATEMATIKA:
                return 'blue darken-2';
            case self::MENDOL:
                return 'cyan darken-2';
            case self::OLASZ:
                return 'red accent-3';
            case self::ORIENTALISZTIKA:
                return 'amber lighten-1';
            case self::SKANDINAVISZTIKA:
                return 'deep-orange lighten-3';
            case self::SPANYOL:
                return 'deep-purple darken-2';
            case self::SZLAVISZTIKA:
                return 'light-blue lighten-2';
            case self::TARSADALOMTUDOMANYI:
                return 'purple lighten-1';
            case self::TORTENESZ:
                return 'teal darken-4';
            default:
                return 'black';
        }
    }
}
