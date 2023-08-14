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
     * @return void
     */
    public function __construct(User $user, string $fieldName = 'email', ?string $className = User::class)
    {
        $this->user = $user;
        $this->relationship = $relationship;
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
        // PHP parses the string to the relationship/query on the relationship
        $databaseValue = null;
        $query = User::query();
        if ($this->relationship != null) {
            $databaseValue = $this->user->{$this->relationship}->{$this->fieldName};
        } else {
            $databaseValue = $this->user->{$this->fieldName};
        }
        Log::info($query->where($this->fieldName, '=', $value)->get());
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
