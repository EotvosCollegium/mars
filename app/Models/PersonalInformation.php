<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getAddress()
    {
        $country = $this->country === 'Hungary' ? '' : ($this->country.', ');

        return $country.$this->zip_code.' '.$this->city.', '.$this->street_and_number;
    }

    public function getPlaceAndDateOfBirth()
    {
        return $this->place_of_birth.', '.$this->date_of_birth;
    }
}
