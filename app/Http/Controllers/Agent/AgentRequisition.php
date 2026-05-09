<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class AgentRequisition extends Controller
{
    public function index()
    {
        return view('Agent.requisition', [
            'year_start' => session('year_start'),
            'year_end'   => session('year_end'),
        ]);
    }

    public function searchItem(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $items = DB::connection('coops')->select('CALL USP_SEARCH_ITEM(?)', [$request->keyword]);
        return response()->json($items);
    }

    public function search(Request $request)
    {
        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');
        $result = DB::connection('coops')->select('CALL USP_SEARCH_AGENT_REQ(?, ?, ?)', [
            session('agent_id'),
            $request->frm_date,
            $request->to_date,
        ]);
        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'    => 'required|date',
            'items'   => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.qnty'    => 'required|numeric|min:1',
            'items.*.unit_id' => 'required|integer',
        ]);

        Config::set('database.connections.coops.database', session('org_schema'));
        DB::purge('coops');

        try {
            DB::connection('coops')->beginTransaction();

            DB::connection('coops')->statement('DROP TEMPORARY TABLE IF EXISTS tempitem');
            DB::connection('coops')->statement('CREATE TEMPORARY TABLE tempitem (item_id INT, qnty SMALLINT, unit_id SMALLINT)');

            foreach ($request->items as $item) {
                DB::connection('coops')->statement(
                    'INSERT INTO tempitem (item_id, qnty, unit_id) VALUES (?, ?, ?)',
                    [$item['item_id'], $item['qnty'], $item['unit_id']]
                );
            }

            $result = DB::connection('coops')->select('CALL USP_AGENT_REQUISITION(?, ?, ?, ?, ?, ?, ?)', [
                $request->indent_id ,
                session('agent_id'),
                $request->date,
                $request->remarks ,
                session('year_id'),
                session('branch_id'),
                $request->indent_id ? 2 : 1,
            ]);

            if ($result[0]->Error_No < 0) {
                DB::connection('coops')->rollBack();
                return response()->json(['success' => false, 'message' => $result[0]->Message], 400);
            }

            DB::connection('coops')->commit();
            return response()->json(['success' => true, 'message' => $result[0]->Message]);
        } catch (\Exception $e) {
            DB::connection('coops')->rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
