<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Faculty
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Faculty newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faculty newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faculty query()
 * @method static \Illuminate\Database\Eloquent\Builder|Faculty whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faculty whereName($value)
 * @mixin \Eloquent
 */
class Faculty extends Model
{
    public const AJK = 'Állam- és Jogtudományi Kar';
    public const BGGYK = 'Bárczi Gusztáv Gyógypedagógiai Kar';
    public const BTK = 'Bölcsészettudományi Kar';
    public const IK = 'Informatikai Kar';
    public const GTI = 'Gazdaságtudományi Kar';
    public const PPK = 'Pedagógiai és Pszichológiai Kar';
    public const TOK = 'Tanító- és Óvóképző Kar';
    public const TATK = 'Társadalomtudományi Kar';
    public const TTK = 'Természettudományi Kar';

    public const ALL = [
        self::AJK,
        self::BGGYK,
        self::BTK,
        self::GTI,
        self::IK,
        self::PPK,
        self::TOK,
        self::TATK,
        self::TTK,
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'faculty_users');
    }
}
