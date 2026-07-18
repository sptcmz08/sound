<?php

namespace App\Casts;

use App\Support\Quantity;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class FlexibleDecimal implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Quantity::trim($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return (string) $value;
    }
}
