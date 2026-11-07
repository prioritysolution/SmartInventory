<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

trait SearchesParties
{
    /**
     * Select2 AJAX party lookup via USP_SEARCH_PARTY_LIST.
     * party_type: 1=supplier, 2=customer.
     */
    public function searchParties(Request $request)
    {
        $partyType = (int) $request->input('party_type', 2);
        if (!in_array($partyType, [1, 2], true)) {
            $partyType = 2;
        }

        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = min(50, max(10, (int) $request->input('page_size', 30)));
        $offset = ($page - 1) * $pageSize;
        $agentId = (int) $request->input('agent_id', 0);
        $branchId = (int) session('branch_id');

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $rows = DB::connection('coops')->select('CALL USP_SEARCH_PARTY_LIST(?, ?, ?, ?, ?, ?, ?)', [
            $partyType,
            $branchId,
            $agentId,
            $q,
            $pageSize,
            $offset,
            1, // active only for Select2 lookups
        ]);

        $total = (int) ($rows[0]->Total_Count ?? count($rows));
        $results = array_map(static function ($row) {
            return [
                'id' => (int) $row->Party_Id,
                'text' => trim(($row->Party_Name ?? '') . '-' . ($row->Party_Code ?? ''), '-'),
                'Party_Id' => (int) $row->Party_Id,
                'Party_Code' => $row->Party_Code,
                'Party_Name' => $row->Party_Name,
                'Contact_No' => $row->Contact_No ?? '',
                'Address_Line1' => $row->Address_Line1 ?? '',
                'Status_Cd' => $row->Status_Cd ?? 1,
            ];
        }, $rows);

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($offset + $pageSize) < $total,
            ],
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
        ]);
    }

    /**
     * Select2 AJAX agent lookup via USP_GET_AGENT_LIST (filtered in PHP by page).
     */
    public function searchAgents(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = min(50, max(10, (int) $request->input('page_size', 30)));
        $branchId = (int) session('branch_id');

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        $pdo = DB::connection('coops')->getPdo();
        $stmt = $pdo->prepare('CALL USP_GET_AGENT_LIST(?, ?, ?, ?)');
        $stmt->execute([$branchId, $q, $page, $pageSize]);
        $slice = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $total = count($slice);
        if ($stmt->nextRowset()) {
            $totalRow = $stmt->fetch(\PDO::FETCH_OBJ);
            if ($totalRow && isset($totalRow->Total)) {
                $total = (int) $totalRow->Total;
            }
        }
        $stmt->closeCursor();

        $results = array_map(static function ($row) {
            return [
                'id' => (int) $row->Agent_Id,
                'text' => trim(($row->Agent_Name ?? '') . ' (' . ($row->Agent_Code ?? '') . ')'),
                'Agent_Id' => (int) $row->Agent_Id,
                'Agent_Code' => $row->Agent_Code,
                'Agent_Name' => $row->Agent_Name,
            ];
        }, $slice);

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $pageSize) < $total,
            ],
        ]);
    }
}
