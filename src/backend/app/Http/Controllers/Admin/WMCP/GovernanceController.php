<?php

namespace App\Http\Controllers\Admin\WMCP;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GovernanceController extends Controller
{
    public function index()
    {
        $logs = DB::table('ai_generations')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.wmcp.governance.index', compact('logs'));
    }

    public function show($id)
    {
        $log = DB::table('ai_generations')->find($id);
        if (!$log) abort(404);

        $claims = DB::table('ai_extracted_claims')->where('generation_id', $id)->get();

        return view('admin.wmcp.governance.show', compact('log', 'claims'));
    }
}
