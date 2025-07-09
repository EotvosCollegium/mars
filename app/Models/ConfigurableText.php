<?php

namespace App\Models;

use App\Models\Workshop;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ConfigurableText extends Model
{
    protected $fillable = ['key', 'workshop_id', 'rawtext'];

    public $timestamps = true;

    private static function getSummary(string $key, ?int $workshop_id = null)
    {
        $summary = "${key};";
        if ($workshop_id != null) {
            $summary .= "${workshop_id}";
        }
        return $summary;
    }

    public function summary()
    {
        return self::getSummary($this->key, $this->workshop_id);
    }

    public static function getText(string $key, ?int $workshop_id = null): string
    {
        return self::getConfigurableText($key, $workshop_id)->text ?? "";
    }

    public static function getConfigurableTextFromSummary(string $summary)
    {
        return self::where(['summarize' => $summary])->first();
    }

    public static function getConfigurableText(string $key, ?int $workshop_id = null): ConfigurableText
    {
        $configurable_text = self::where(['summarize' => self::getSummary($key, $workshop_id)])->first();
        if (!$configurable_text) {
            $configurable_text = self::create(
                [
                    'key' => $key,
                    'workshop_id' => $workshop_id,
                ]
            );
        }
        return $configurable_text;
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo('App\Models\Workshop');
    }

    public function text(): Attribute
    {
        return Attribute::make(
            get: fn () =>
                str_replace(
                    [
                        "SYSADMIN_EMAIL",
                        "SECRETARIAT_EMAIL",
                        "STUDENT_COUNCIL_EMAIL"
                    ],
                    [
                        config('mail.sys_admin_mail'),
                        config('mail.secretary_mail'),
                        config('contacts.mail_valasztmany')
                    ],
                    $this->rawtext
                ),
        );
    }
}
