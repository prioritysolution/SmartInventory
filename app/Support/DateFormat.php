<?php

namespace App\Support;

class DateFormat
{
    public static function toDisplay($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $raw = trim((string) $value);

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $raw, $match)) {
            if (checkdate((int) $match[2], (int) $match[3], (int) $match[1])) {
                return $match[3] . '-' . $match[2] . '-' . $match[1];
            }
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})/', $raw, $match)) {
            return $match[1] . '-' . $match[2] . '-' . $match[3];
        }

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $raw, $match)) {
            return $match[1] . '-' . $match[2] . '-' . $match[3];
        }

        try {
            return \Carbon\Carbon::parse($raw)->format('d-m-Y');
        } catch (\Exception $e) {
            return $raw;
        }
    }
}
