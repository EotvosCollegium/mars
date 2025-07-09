<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\RoleObject;
use App\Models\User;
use App\Models\Workshop;
use App\Models\PersonalInformation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Cache;

class PersonalInformationPolicy
{
    use HandlesAuthorization;

    /**
     * We let admins do anything here.
     */
    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    private function canHaveCollegistSpecificData(User $user) : bool
    {
        return $user->isCollegist(alumni: true) || $user->application;
    }

    private function canHaveTenantSpecificData(User $user) : bool
    {
        return $user->hasRole([Role::TENANT]);
    }

    public function havePartialCollegistRegistration(User $user) : bool
    {
        return !$user->verified && $user->roles->isEmpty();
    }

    public function see(User $user, PersonalInformation $personal_information, string $field){
        $target = $personal_information->user;
        if($user->cannot('view', $target)){
            return false;
        }
        switch($field){
            case "name": return true;
            case "email": return true;
            case "phone_number": return true;
            case "mothers_name": return $this->canHaveCollegistSpecificData($target);
            case "place_of_birth": return $this->canHaveCollegistSpecificData($target);
            case "date_of_birth": return $this->canHaveCollegistSpecificData($target);
            case "country": return $this->canHaveCollegistSpecificData($target);
            case "county": return $this->canHaveCollegistSpecificData($target);
            case "zip_code": return $this->canHaveCollegistSpecificData($target);
            case "city": return $this->canHaveCollegistSpecificData($target);
            case "street_and_number": return $this->canHaveCollegistSpecificData($target);
            case "relatives_contact_data": return $this->canHaveCollegistSpecificData($target);
            case "tenant_until": return $this->canHaveTenantSpecificData($target);
        }
    }

    public function edit(User $user, PersonalInformation $personal_information, string $field){
        $target = $personal_information->user;
        if(!$this->see($user, $personal_information, $field)){
            return false;
        }
        if($user->hasRole([
            Role::SECRETARY
        ])){
            return true;
        }
        $new_applicant = false;
        if($user->application){
            $new_applicant = !$target->verified && $user->roles->isEmpty();
        }
        switch($field){
            case "name": return $new_applicant;
            case "email": return false;
            case "phone_number": return true;
            case "mothers_name": return $new_applicant;
            case "place_of_birth": return $new_applicant;
            case "date_of_birth": return $new_applicant;
            case "country": return true;
            case "county": return true;
            case "zip_code": return true;
            case "city": return true;
            case "street_and_number": return true;
            case "relatives_contact_data": return true;
            case "tenant_until": return true;
        }
    }

    public function saveWithout(User $user, PersonalInformation $personal_information, string $field){
        $target = $personal_information->user;
        if(!$this->edit($user, $personal_information, $field)){
            return false;
        }
        $new_applicant = false;
        if($user->application){
            $new_applicant = !$target->verified && $user->roles->isEmpty();
        }
        switch($field){
            case "name": return false;
            case "email": return false;
            case "relatives_contact_data": return true;
        }
        return $new_applicant;
    }

    public function submitApplicationWithout(User $user, PersonalInformation $personal_information, string $field){
        switch($field){
            case "name": return false;
            case "email": return false;
            case "phone_number": return false;
            case "mothers_name": return false;
            case "place_of_birth": return false;
            case "date_of_birth": return false;
            case "country": return false;
            case "county": return false;
            case "zip_code": return false;
            case "city": return false;
            case "street_and_number": return false;
            case "relatives_contact_data": return true;
            case "tenant_until": return true;
        }
    }
}
