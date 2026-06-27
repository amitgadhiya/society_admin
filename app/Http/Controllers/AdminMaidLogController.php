<?php

namespace App\Http\Controllers;

use App\Models\Maid;
use App\Models\MaidEntryLog;
use App\Models\Watchman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMaidLogController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $societyId = $user->society_id;

        $dateMode = $request->input('date_mode', 'single');

        if ($dateMode === 'single') {
            $date = $request->input('date', now()->toDateString());
            $from = $to = $date;
        } else {
            $dateMode = 'range';
            $date     = null;
            $from     = $request->input('from', now()->toDateString());
            $to       = $request->input('to',   now()->toDateString());
        }

        $maidId     = $request->input('maid_id');
        $watchmanId = $request->input('watchman_id');

        $maidsList = Maid::where('society_id', $societyId)
            ->orderBy('name')
            ->get();

        $watchmenList = Watchman::where('society_id', $societyId)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $query = MaidEntryLog::with(['maid', 'watchman'])
            ->where('society_id', $societyId)
            ->whereBetween('enter_time', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($maidId) {
            $query->where('maid_id', $maidId);
        }

        if ($watchmanId) {
            $query->where('watchman_id', $watchmanId);
        }

        $logs = $query->orderByDesc('enter_time')->get();

        $total   = $logs->count();
        $inside  = $logs->where('status', 'enter')->count();
        $exited  = $logs->where('status', 'exit')->count();

        $dateReport = $logs
            ->groupBy(fn($log) => $log->enter_time->toDateString())
            ->map(fn($dayLogs, $dateStr) => (object) [
                'date'        => $dateStr,
                'logs'        => $dayLogs,
                'total'       => $dayLogs->count(),
                'inside'      => $dayLogs->where('status', 'enter')->count(),
                'exited'      => $dayLogs->where('status', 'exit')->count(),
                'maid_counts' => $dayLogs->groupBy('maid_id')->map->count(),
            ])
            ->values();

        return view('masters.maid.log', compact(
            'dateMode', 'date', 'from', 'to', 'maidId', 'watchmanId',
            'total', 'inside', 'exited',
            'dateReport', 'maidsList', 'watchmenList'
        ));
    }
}
