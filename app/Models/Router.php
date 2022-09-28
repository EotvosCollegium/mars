<?php

namespace App\Models;

use App\Utils\NotificationCounter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Router extends Model
{
    use NotificationCounter;
    use HasFactory;

    protected $table = 'routers';
    protected $primaryKey = 'ip';
    public $incrementing = false;
    public $timestamps = false;

    // We send a warning to the network admins on the second error.
    public const WARNING_THRESHOLD = 2;

    protected $fillable = [
        'ip', 'room', 'failed_for', 'port', 'type', 'serial_number',
        'mac_WAN', 'mac_2G_LAN', 'mac_5G', 'comment',
        'date_of_acquisition', 'date_of_deployment',
    ];

    protected $attributes = [
        'failed_for' => 0, //default value
    ];

    public function isDown()
    {
        return $this->failed_for > 0;
    }

    public function isUp()
    {
        return $this->failed_for == 0;
    }

    public function getFailStartDate()
    {
        return Carbon::now()->subMinutes($this->failed_for * 5)->roundMinute(5)->format('Y-m-d H:i');
    }

    public function sendWarning()
    {
        if ($this->failed_for == self::WARNING_THRESHOLD) {
            $internet_admins = Role::firstWhere('name', Role::SYS_ADMIN)->getUsers();
            foreach ($internet_admins as $admin) {
                Mail::to($admin)->queue(new \App\Mail\RouterWarning($admin, $this));
            }
            $room=Room::firstWhere('name', $this->room);
            if ($room!=null) {
                $residents=$room->users()->get();
                foreach ($residents as $resident) {
                    Mail::to($resident)->queue(new \App\Mail\RouterWarningResident($resident, $this));
                }
            }
        }
    }

    public static function notifications()
    {
        return self::where('failed_for', '>', 0)->count();
    }
}
