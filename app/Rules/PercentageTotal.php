<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PercentageTotal implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $total = array_sum(array_map('floatval', $value));

        if ($total > 100) {
            $fail('The sum of percentages should not exceed 100.');
        }
    }
}
