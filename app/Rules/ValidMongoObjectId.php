<?php

namespace App\Rules;

use App\Helpers\ValidationHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMongoObjectId implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!ValidationHelper::isValidMongoObjectId($value)) {
            $fail('The :attribute must be a valid MongoDB ObjectId format.');
        }
    }
}
