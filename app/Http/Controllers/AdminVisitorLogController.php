<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Visitor;
use App\Models\Watchman;
use App\Models\Wing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminVisitorLogController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $societyId = $user->society_id;

        $dateMode = $request->input('date_mode', 'single');
        $date     = null;

        if ($dateMode === 'single') {
            $date = $request->input('date', now()->toDateString());
            $from = $to = $date;
        } else {
            $dateMode = 'range';
            $from = $request->input('from', now()->toDateString());
            $to   = $request->input('to',   now()->toDateString());
        }

        $watchmanId = $request->input('watchman_id');
        $wingId     = $request->input('wing_id');
        $unitId     = $request->input('unit_id');

        $watchmenList = Watchman::where('society_id', $societyId)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $wingsList = Wing::where('society_id', $societyId)
            ->orderBy('name')
            ->get();

        $unitsList = Unit::where('society_id', $societyId)
            ->when($wingId, fn($q) => $q->where('wing_id', $wingId))
            ->orderBy('unit_number')
            ->get();

        $query = Visitor::with(['unit.wing', 'watchman'])
            ->where('society_id', $societyId)
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($watchmanId) {
            $query->where('watchman_id', $watchmanId);
        }

        if ($unitId) {
            $query->where('visit_to_unit_id', $unitId);
        } elseif ($wingId) {
            $unitIds = Unit::where('society_id', $societyId)
                ->where('wing_id', $wingId)
                ->pluck('id');
            $query->whereIn('visit_to_unit_id', $unitIds);
        }

        $visitors = $query->orderByDesc('created_at')->get();

        $total   = $visitors->count();
        $in      = $visitors->whereNull('out_at')->count();
        $out     = $visitors->whereNotNull('out_at')->count();
        $pending = $visitors->where('status', 'pending')->count();

        $dateReport = $visitors
            ->groupBy(fn($v) => $v->created_at->toDateString())
            ->map(fn($dayVisitors, $dateStr) => (object) [
                'date'     => $dateStr,
                'visitors' => $dayVisitors,
                'total'    => $dayVisitors->count(),
                'in'       => $dayVisitors->whereNull('out_at')->count(),
                'out'      => $dayVisitors->whereNotNull('out_at')->count(),
            ])
            ->values();

        return view('masters.visitor.log', compact(
            'dateMode', 'date', 'from', 'to', 'watchmanId', 'wingId', 'unitId',
            'total', 'in', 'out', 'pending',
            'dateReport', 'watchmenList', 'wingsList', 'unitsList'
        ));
    }
}
