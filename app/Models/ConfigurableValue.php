<?php

namespace App\Models;

use App\Models\Workshop;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ConfigurableValue extends Model
{
    protected $fillable = ['key', 'workshop_id', 'raw_value', 'value_type'];

    public $timestamps = true;

    private static function getSummary(string $key, ?int $workshop_id = null)
    {
        $summary = "{$key};";
        if ($workshop_id != null) {
            $summary .= "{$workshop_id}";
        }
        return $summary;
    }

    private static function substituteVariables(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        return str_replace(
            [
                "SYSADMIN_EMAIL",
                "SECRETARIAT_EMAIL",
                "STUDENT_COUNCIL_EMAIL",
            ],
            [
                config('mail.sys_admin_mail'),
                config('mail.secretary_mail'),
                config('contacts.mail_valasztmany'),
            ],
            $text
        );
    }

    public function summary()
    {
        return self::getSummary($this->key, $this->workshop_id);
    }

    public static function getText(string $key, ?int $workshop_id = null): string
    {
        return self::getConfigurableValue($key, $workshop_id)->text ?? "";
    }

    public static function getNumber(string $key, ?int $workshop_id = null): int
    {
        return self::getConfigurableValue($key, $workshop_id)->number ?? 0;
    }

    public static function fromSummary(string $summary)
    {
        $parts = explode(";", $summary);
        $key = $parts[0];
        $workshop_id = count($parts) > 1 && $parts[1] !== '' ? intval($parts[1]) : null;
        return self::getConfigurableValue($key, $workshop_id);
    }

    public static function getConfigurableValue(string $key, ?int $workshop_id = null): ConfigurableValue
    {
        return self::firstOrCreate(
            [
                'key' => $key,
                'workshop_id' => $workshop_id,
            ],
            [
                'raw_value' => '',
                'value_type' => 'text',
            ]
        );
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo('App\Models\Workshop');
    }

    public function text(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->value_type !== 'text') {
                    throw new \InvalidArgumentException(
                        "Not a text value: value_type is '{$this->value_type}', expected 'text'"
                    );
                }

                return self::substituteVariables($this->raw_value);
            }
        );
    }

    public function number(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->value_type !== 'number') {
                    throw new \InvalidArgumentException(
                        "Not a number value: value_type is '{$this->value_type}', expected 'number'"
                    );
                }

                return intval($this->raw_value);
            }
        );
    }
}
