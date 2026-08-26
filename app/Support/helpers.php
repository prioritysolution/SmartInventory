<?php

use App\Support\DateFormat;

if (!function_exists('dmy')) {
    function dmy($value): string
    {
        return DateFormat::toDisplay($value);
    }
}
