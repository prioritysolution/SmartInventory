<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait PaginatesReportRows
{
    /**
     * Return a full list when print asks for everything (limit=0),
     * otherwise a page for mouse-scroll fetch.
     *
     * @param  array<int, mixed>  $rows
     * @param  array<int, string>  $sumKeys
     */
    protected function pagedReport(Request $request, $rows, array $sumKeys = []): JsonResponse
    {
        $list = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $list[] = is_array($row) ? $row : (array) $row;
        }

        $limit = (int) $request->input('limit', 0);
        $offset = max(0, (int) $request->input('offset', 0));

        $totals = [];
        foreach ($sumKeys as $key) {
            $sum = 0.0;
            foreach ($list as $row) {
                if ((int) ($row['Row_Kind'] ?? 0) !== 0) {
                    continue;
                }
                $sum += (float) ($row[$key] ?? 0);
            }
            $totals[$key] = $sum;
        }

        if ($limit <= 0) {
            return response()->json($list);
        }

        $limit = min(100, max(10, $limit));
        $total = count($list);
        $slice = array_values(array_slice($list, $offset, $limit));

        return response()->json([
            'results'      => $slice,
            'recordsTotal' => $total,
            'has_more'     => ($offset + count($slice)) < $total,
            'totals'       => $totals,
        ]);
    }

    /**
     * Page already-limited SQL rows that include Total_Count.
     *
     * @param  array<int, mixed>  $rows
     */
    protected function pagedSqlReport(Request $request, array $rows, int $total, array $totals = []): JsonResponse
    {
        $list = [];
        foreach ($rows as $row) {
            $item = is_array($row) ? $row : (array) $row;
            unset($item['Total_Count']);
            $list[] = $item;
        }

        $limit = min(100, max(10, (int) $request->input('limit', 40)));
        $offset = max(0, (int) $request->input('offset', 0));

        return response()->json([
            'results'      => $list,
            'recordsTotal' => $total,
            'has_more'     => ($offset + count($list)) < $total,
            'totals'       => $totals,
        ]);
    }
}
