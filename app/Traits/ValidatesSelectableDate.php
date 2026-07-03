<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait ValidatesSelectableDate
{
    private function validateSelectableDate(string $date, string $field): void
    {
        $validator = Validator::make([$field => $date], [
            $field => [
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Carbon::parse((string) $value)->isSunday()) {
                        $fail('No se pueden seleccionar citas en domingo.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
