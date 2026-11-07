<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait SearchesItemsByCode
{
    protected function searchItemsByCode(int $catId, int $subCatId, string $code): array
    {
        $code = trim($code);
        try {
            $pdo = DB::connection('coops')->getPdo();
            $stmt = $pdo->prepare('CALL USP_SEARCH_ITEM_BY_CODE(?, ?, ?)');
            $stmt->execute([$catId, $subCatId, $code]);
            $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $stmt->closeCursor();
            return $rows ?: [];
        } catch (\Exception $e) {
            Log::error('Item search by code error: ' . $e->getMessage());
            try {
                DB::purge('coops');
                $rows = DB::connection('coops')->select('CALL USP_GET_ITEM_LIST(?, ?, ?)', [
                    $catId,
                    $subCatId,
                    $code,
                ]);
                return $rows ?: [];
            } catch (\Exception $fallback) {
                Log::error('Item list fallback error: ' . $fallback->getMessage());
                return [];
            }
        }
    }

    protected function uniqueItemFromList(array $items, string $code): ?object
    {
        if (count($items) === 1) {
            return $items[0];
        }
        $code = trim($code);
        if ($code === '' || count($items) === 0) {
            return null;
        }
        $exact = array_values(array_filter($items, function ($item) use ($code) {
            return strcasecmp(trim((string) ($item->Prod_Code ?? '')), $code) === 0
                || (string) ($item->Barcode_Label ?? '') === $code;
        }));
        return count($exact) === 1 ? $exact[0] : null;
    }

    protected function prodInfoByBarcode(string $code, string $date): array
    {
        if ($code === '' || $date === '' || !ctype_digit($code)) {
            return [];
        }
        try {
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO(?, ?)', [$code, $date]);
            return $result ?: [];
        } catch (\Exception $e) {
            Log::error('Barcode lookup error: ' . $e->getMessage());
            DB::purge('coops');
            return [];
        }
    }

    protected function prodInfoByAgentBarcode(string $code, int $agentId, string $date): array
    {
        if ($code === '' || $date === '' || $agentId <= 0 || !ctype_digit($code)) {
            return [];
        }
        try {
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_AGENT(?, ?, ?)', [
                $code,
                $agentId,
                $date,
            ]);
            return $result ?: [];
        } catch (\Exception $e) {
            Log::error('Agent barcode lookup error: ' . $e->getMessage());
            DB::purge('coops');
            return [];
        }
    }

    protected function prodInfoByItem(int $prodId, string $date): array
    {
        if ($prodId <= 0 || $date === '') {
            return [];
        }
        try {
            $result = DB::connection('coops')->select('CALL USP_GET_PROD_INFO_BY_ITEM(?, ?)', [$prodId, $date]);
            return $result ?: [];
        } catch (\Exception $e) {
            Log::error('Product-by-item lookup error: ' . $e->getMessage());
            DB::purge('coops');
            return [];
        }
    }

    protected function resolveUniqueProductInfo(string $code, string $date): array
    {
        $code = trim($code);
        if ($code === '' || $date === '') {
            return [];
        }
        $result = $this->prodInfoByBarcode($code, $date);
        if (!empty($result)) {
            return $result;
        }
        $unique = $this->uniqueItemFromList($this->searchItemsByCode(0, 0, $code), $code);
        if (!$unique) {
            return [];
        }
        return $this->prodInfoByItem((int) $unique->Prod_Id, $date);
    }
}
