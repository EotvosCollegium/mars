<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalInformation extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The tenant_until attribute is only set for users with the tenant role
     * For tenants it is the planned departure they set in the registration form
     * For active collegists living in the dormitory (who also have the tenant role) it is set automatically to the end of the semester
     * Former collegists are assigned the tenant role when they come back the dormitory for a few days/months
     * If the tenant_until attribute is in the past (or not set) for a user with thetenant role,
     *  the user will automatically be redirected to the tenant_update form
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
