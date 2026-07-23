<?php

namespace App\Casts;

use App\Enums\RequisitionStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SafeRequisitionStatusCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): RequisitionStatus
    {
        if ($value instanceof RequisitionStatus) {
            return $value;
        }

        return RequisitionStatus::tryFrom((string) $value) ?? RequisitionStatus::PENDING;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof RequisitionStatus) {
            return $value->value;
        }

        return (string) $value;
    }
}
