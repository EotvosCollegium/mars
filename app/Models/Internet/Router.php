<?php

namespace App\Models\Internet;

use App\Models\Room;
use App\Models\User;
use App\Utils\NotificationCounter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * App\Models\Internet\Router
 *
 * @property string $ip
 * @property int $room
 * @property int $failed_for
 * @property string|null $port
 * @property string|null $type
 * @property string|null $serial_number
 * @property string|null $mac_5G
 * @property string|null $mac_2G_LAN
 * @property string|null $mac_WAN
 * @property string|null $comment
 * @property string|null $date_of_acquisition
 * @property string|null $date_of_deployment
 * @method static \Database\Factories\Internet\RouterFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Router newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Router newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Router query()
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereDateOfAcquisition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereDateOfDeployment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereFailedFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereMac2GLAN($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereMac5G($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereMacWAN($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereRoom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Router whereType($value)
 * @mixin \Eloquent
 */
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
            foreach (User::admins() as $admin) {
                Mail::to($admin)->queue(new \App\Mail\RouterWarning($admin, $this));
            }
            $room = Room::firstWhere('name', $this->room);
            if ($room!=null) {
                foreach ($room->users as $resident) {
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
