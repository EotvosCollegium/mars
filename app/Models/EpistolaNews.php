<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EpistolaNews
 *
 * @property int $id
 * @property int|null $uploader_id
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $date
 * @property \Illuminate\Support\Carbon|null $time
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string|null $picture_path
 * @property int $sent
 * @property string|null $details_name_1
 * @property string|null $details_url_1
 * @property string|null $details_name_2
 * @property string|null $details_url_2
 * @property string|null $deadline_name
 * @property \Illuminate\Support\Carbon|null $deadline_date
 * @property \Illuminate\Support\Carbon|null $date_for_sorting
 * @property string|null $category
 * @property-read string $bg_color
 * @property-read string $color
 * @property-read string|null $date_time
 * @property-read \App\Models\User|null $uploader
 * @property-read string|null $valid_until
 * @method static \Database\Factories\EpistolaNewsFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews query()
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDateForSorting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDeadlineDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDeadlineName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDetailsName1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDetailsName2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDetailsUrl1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereDetailsUrl2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews wherePicturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EpistolaNews whereUploaderId($value)
 * @mixin \Eloquent
 */
class EpistolaNews extends Model
{
    use HasFactory;

    protected $table = 'epistola';

    public $timestamps = false;

    protected $fillable = [
        'uploader_id',
        'title',
        'subtitle',
        'description',
        'date',
        'time',
        'end_date',
        'details_name_1',
        'details_name_2',
        'details_url_1',
        'details_url_2',
        'deadline_name',
        'deadline_date',
        'picture_path',
        'date_for_sorting',
        'category',
        'sent',
    ];

    protected $casts = [
        'date' => 'datetime',
        'time' => 'datetime',
        'end_date' => 'datetime',
        'date_for_sorting' => 'datetime',
        'valid_until' => 'datetime',
        'deadline_date' => 'datetime',
    ];

    /**
     * Get the valid_until attribute. Notifications should be sent before this date
     *
     * @return Attribute
     */
    public function validUntil(): Attribute
    {
        return Attribute::make(
            get: function (): string|null {
                $date = $this->deadline_date ?? $this->date;
                return $date?->format('Y.m.d');
            }
        );
    }

    /**
     * Get the date_time attribute (start date - end date).
     *
     * @return Attribute
     */
    public function dateTime(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $datetime = $this->date?->format('Y.m.d.') ?? '';
                $datetime .= $this->time?->format(' G:i') ?? '';
                $datetime .= $this->end_date?->format(' - Y.m.d.') ?? '';
                return $datetime;
            }
        );
    }

    /**
     * Get the color attribute (black/white calculated by the bg_color).
     * Uses the yiq algorithm.
     *
     * @return Attribute
     */
    public function color(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $r = hexdec(substr($this->bg_color, 1, 2));
                $g = hexdec(substr($this->bg_color, 3, 2));
                $b = hexdec(substr($this->bg_color, 5, 2));
                $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

                return ($yiq >= 128) ? 'black' : 'white';
            }
        );
    }

    /**
     * Get the bg_color attribute (generated from the category name).
     *
     * @return Attribute
     */
    public function bgColor(): Attribute
    {
        return Attribute::make(
            get: fn (): string => substr(dechex(crc32($this->category)), 0, 6)
        );
    }

    public function shouldBeSent()
    {
        return ($this->valid_until != null)
            && (now()->addDays(7)->format('Y.m.d') > $this->valid_until)
            && !$this->sent;
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
