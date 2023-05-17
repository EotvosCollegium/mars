<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property User $user
 * @property string $place_of_birth
 * @property string $date_of_birth
 * @property string $mothers_name
 * @property string $phone_number
 * @property string $country
 * @property string $county
 * @property string $zip_code
 * @property string $city
 * @property string $street_and_number
 * @property string $tenant_until
 * @property string $profile_picture_id
 * @property string $relatives_contact_data
 * @method getAddress()
 * @method getPlaceAndDateOfBirth()
 */
class PersonalInformation extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The tenant_until attribute is only set for users with the tenant role. It is synced with the has_internet_until attribute.
     * It is initially set in the registration form. If it is in the past (or not set) for a user with the tenant role,
     *  the user will be automatically redirected to the tenant_update form.
     */
    protected $fillable = [
        'user_id',
        'place_of_birth',
        'date_of_birth',
        'mothers_name',
        'phone_number',
        'country',
        'county',
        'zip_code',
        'city',
        'street_and_number',
        'tenant_until',
        'profile_picture_id',
        'relatives_contact_data',
    ];

    /**
     * The user that owns the personal information.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The full address of the user.
     * @return string
     */
    public function getAddress(): string
    {
        $country = $this->country === 'Hungary' ? '' : ($this->country.', ');

        return $country.$this->zip_code.' '.$this->city.', '.$this->street_and_number;
    }

    /**
     * The place and date of birth of the user.
     * @return string
     */
    public function getPlaceAndDateOfBirth()
    {
        return $this->place_of_birth.', '.$this->date_of_birth;
    }
}
