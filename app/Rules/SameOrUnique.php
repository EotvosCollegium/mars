<?php

namespace App\Rules;

use App\Models\EducationalInformation;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class SameOrUnique implements Rule
{

    private User $user;
    private string $className;
    private string $fieldName;

    /**
     * Determines wether the given field in the given relationship is unique in the table or is the same which the user already has.
     *
     * @param User $user the user for which the field is checked
     * @param string $fieldName the name of the field which needs to be unique
     * @param string $className the class of the model which the field is an attribute of
     * @return void
     */
    public function __construct(User $user, string $className = User::class, string $fieldName = 'email')
    {
        $this->user = $user;
        $this->className = $className;
        $this->fieldName = $fieldName;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // PHP parses the classname::query() to something like User::query()
        $databaseValue = null;
        $query = $this->className::query();
        if ($this->className != User::class) {
            // If the field is not contained in the user, find it from the class given. 
            // PHP can get the attribute from a string (which {$this->fiendName} is).
            $databaseValue = $this->className::query()->firstWhere('user_id', '=', $this->user->id)->{$this->fieldName};
        } else {
            $databaseValue = $this->user->{$this->fieldName};
        }
        // Return true if either the value in the DB is the same as the validated value or there exists no field in the corresponding table.
        return $databaseValue == $value || !$query->where($this->fieldName, '=', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.unique');
    }
}
