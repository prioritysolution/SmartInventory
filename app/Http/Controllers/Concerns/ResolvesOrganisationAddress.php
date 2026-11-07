<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;

trait ResolvesOrganisationAddress
{
    protected function organisationAddress(): string
    {
        $cached = session('org_address');
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $orgId = (int) session('org_id');
        if ($orgId <= 0) {
            return '';
        }

        $org = DB::selectOne(
            'SELECT Address, City, District, State, PinCode FROM mst_organisation WHERE Org_Id = ?',
            [$orgId]
        );
        if (!$org) {
            return '';
        }

        $parts = array_values(array_filter([
            trim((string) ($org->Address ?? '')),
            trim((string) ($org->City ?? '')),
            trim((string) ($org->District ?? '')),
            trim((string) ($org->State ?? '')),
        ], function ($value) {
            return $value !== '';
        }));
        $line = implode(', ', $parts);
        $pin = trim((string) ($org->PinCode ?? ''));
        if ($pin !== '') {
            $line = $line !== '' ? $line . ' - ' . $pin : $pin;
        }

        session(['org_address' => $line]);

        return $line;
    }
}
