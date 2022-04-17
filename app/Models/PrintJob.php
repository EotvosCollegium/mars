<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    use HasFactory;

    protected $table = 'print_jobs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    public const QUEUED = 'QUEUED';
    public const ERROR = 'ERROR';
    public const CANCELLED = 'CANCELLED';
    public const SUCCESS = 'SUCCESS';
    public const STATES = [
        self::QUEUED,
        self::ERROR,
        self::CANCELLED,
        self::SUCCESS,
    ];

    protected $fillable = [
        'filename', 'filepath', 'user_id', 'state', 'job_id', 'cost',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public static function translateStates(): \Closure
    {
        return function ($data) {
            $data->state = __('print.'.strtoupper($data->state));

            return $data;
        };
    }

    public static function addCurrencyTag(): \Closure
    {
        return function ($data) {
            $data->cost = "{$data->cost} HUF";

            return $data;
        };
    }
}
