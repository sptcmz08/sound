<?php

namespace App\Support;

final class Quantity
{
    public static function trim(int|float|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $raw = (string) $value;
        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $raw)) {
            return $raw;
        }

        [$whole, $fraction] = array_pad(explode('.', $raw, 2), 2, '');
        $fraction = rtrim($fraction, '0');

        return $fraction === '' ? $whole : "{$whole}.{$fraction}";
    }

    public static function format(int|float|string|null $value): string
    {
        $trimmed = self::trim($value);
        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $trimmed)) {
            return $trimmed;
        }

        [$whole, $fraction] = array_pad(explode('.', $trimmed, 2), 2, '');
        $formatted = number_format((int) $whole);

        return $fraction === '' ? $formatted : "{$formatted}.{$fraction}";
    }
}
