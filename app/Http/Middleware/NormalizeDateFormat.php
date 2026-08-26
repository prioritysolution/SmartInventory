<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeDateFormat
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->convertDates($request->all()));

        return $next($request);
    }

    private function convertDates(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->convertDates($value);
                continue;
            }

            if (!is_string($value)) {
                continue;
            }

            if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', trim($value), $match)) {
                $day = (int) $match[1];
                $month = (int) $match[2];
                $year = (int) $match[3];
                if (checkdate($month, $day, $year)) {
                    $data[$key] = sprintf('%04d-%02d-%02d', $year, $month, $day);
                }
            }
        }

        return $data;
    }
}
