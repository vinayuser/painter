<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPainter implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $painter = User::query()->find($value);

        if (! $painter || $painter->role !== UserRole::Painter || ! $painter->is_active) {
            $fail('The selected painter is invalid or unavailable.');
        }
    }
}
