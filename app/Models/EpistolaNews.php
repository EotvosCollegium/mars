<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    protected $dates = ['date', 'time', 'end_date', 'date_for_sorting', 'valid_until', 'deadline_date'];

    /**
     * Get the valid_until attribute. Notifications should be sent before this date
     *
     * @return Attribute
     */
    public function validUntil(): Attribute
    {
        return Attribute::make(
            get: function (): string|null {
                $date = ($this->deadline_date ?? $this->date);
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
            get: function (): string|null {
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
            && ! $this->sent;
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
