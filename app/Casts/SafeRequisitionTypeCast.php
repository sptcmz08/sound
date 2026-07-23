<?php

namespace App\Casts;

use App\Enums\RequisitionType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SafeRequisitionTypeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): RequisitionType
    {
        if ($value instanceof RequisitionType) {
            return $value;
        }

        return RequisitionType::tryFrom((string) $value) ?? RequisitionType::GENERAL_ISSUE;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof RequisitionType) {
            return $value->value;
        }

        return (string) $value;
    }
}
