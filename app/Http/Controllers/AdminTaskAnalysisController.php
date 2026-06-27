<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Watchman;
use App\Models\WatchmanTask;
use App\Models\WatchmanTaskLog;
use App\Traits\TaskDueDateTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTaskAnalysisController extends Controller
{
    use TaskDueDateTrait;
    // ------------------------------------------------------------------ index
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $societyId = $user->society_id;

        $watchmanIds = Watchman::where('society_id', $societyId)->pluck('id');

        $from       = $request->get('from', now()->subDays(29)->toDateString());
        $to         = $request->get('to',   now()->toDateString());
        $watchmanId = $request->get('watchman_id');
        $taskId     = $request->get('task_id');

        $applyFilters = function ($query) use ($watchmanIds, $from, $to, $watchmanId, $taskId) {
            $query->whereIn('watchman_id', $watchmanIds)
                  ->where('is_completed', true)
                  ->whereBetween('completion_date', [$from, $to]);
            if ($watchmanId) $query->where('watchman_id', $watchmanId);
            if ($taskId)     $query->where('task_id', $taskId);
            return $query;
        };

        $totalCompletions = $applyFilters(WatchmanTaskLog::query())->count();
        $uniqueWatchmen   = $applyFilters(WatchmanTaskLog::query())->distinct('watchman_id')->count('watchman_id');
        $uniqueTasks      = $applyFilters(WatchmanTaskLog::query())->distinct('task_id')->count('task_id');

        $byWatchmanRows = $applyFilters(WatchmanTaskLog::query())
            ->selectRaw('watchman_id, COUNT(*) as total, COUNT(DISTINCT task_id) as unique_tasks')
            ->groupBy('watchman_id')
            ->orderByDesc('total')
            ->get();

        $byTaskRows = $applyFilters(WatchmanTaskLog::query())
            ->selectRaw('task_id, COUNT(*) as total, COUNT(DISTINCT watchman_id) as unique_watchmen')
            ->groupBy('task_id')
            ->orderByDesc('total')
            ->get();

        $watchmenMap = Watchman::whereIn('id', $byWatchmanRows->pluck('watchman_id'))->get()->keyBy('id');
        $tasksMap    = Task::whereIn('id', $byTaskRows->pluck('task_id'))->get()->keyBy('id');

        $logs = $applyFilters(WatchmanTaskLog::query())
            ->with(['watchman', 'task'])
            ->orderByDesc('completion_date')
            ->orderByDesc('completed_at')
            ->paginate(30)
            ->appends($request->only('from', 'to', 'watchman_id', 'task_id'));

        $watchmenList = Watchman::where('society_id', $societyId)
            ->where('active', true)
            ->orderBy('name')->get();

        $tasksList = Task::where('society_id', $societyId)->where('status', 'active')->orderBy('title')->get();

        return view('masters.task.analysis', compact(
            'from', 'to', 'watchmanId', 'taskId',
            'totalCompletions', 'uniqueWatchmen', 'uniqueTasks',
            'byWatchmanRows', 'watchmenMap',
            'byTaskRows', 'tasksMap',
            'logs',
            'watchmenList', 'tasksList'
        ));
    }

    // --------------------------------------------------------------- dailyLog
    public function dailyLog(Request $request)
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $societyId = $user->society_id;

        $watchmanId = $request->input('watchman_id');
        $dateMode   = $request->input('date_mode', 'single');
        $date       = null;

        if ($dateMode === 'single') {
            $date = $request->input('date', now()->toDateString());
            $from = $to = $date;
        } else {
            $dateMode = 'range';
            $from = $request->input('from', now()->toDateString());
            $to   = $request->input('to',   now()->toDateString());
        }

        $scheduleType = $request->input('schedule_type');
        $validTypes   = ['daily', 'weekly', 'monthly', 'quarterly', 'biannual', 'annual'];
        if (! in_array($scheduleType, $validTypes)) {
            $scheduleType = null;
        }

        $watchmanIds = Watchman::where('society_id', $societyId)->pluck('id');

        // Dates in range, newest first
        $dates = collect();
        $cur   = Carbon::parse($to);
        $start = Carbon::parse($from);
        while ($cur >= $start) {
            $dates->push($cur->toDateString());
            $cur->subDay();
        }

        // Logs indexed by "watchman_id_task_id_date"
        $logsIndexed = WatchmanTaskLog::whereIn('watchman_id', $watchmanIds)
            ->when($watchmanId, fn($q) => $q->where('watchman_id', $watchmanId))
            ->whereBetween('completion_date', [$from, $to])
            ->get()
            ->keyBy(fn($log) => $log->watchman_id . '_' . $log->task_id . '_' . $log->completion_date->toDateString());

        // All active assignments
        $allAssignments = WatchmanTask::with(['watchman', 'task'])
            ->where('created_at', '<', $from)
            ->whereIn('watchman_id', $watchmanIds)
            ->when($watchmanId, fn($q) => $q->where('watchman_id', $watchmanId))
            ->where('status', 'active')
            ->whereHas('task', fn($q) => $q->where('status', 'active'))
            ->when($scheduleType, fn($q) => $q->whereHas('task', fn($tq) => $tq->where('recurrence_type', $scheduleType)))
            ->get();

        $watchmenList = Watchman::where('society_id', $societyId)
            ->where('active', true)
            ->orderBy('name')->get();

        $assignmentsByWatchman = $allAssignments->groupBy('watchman_id');

        // Date report: for each date show only tasks that were actually due that day
        $dateReport = $dates->map(function ($dateStr) use ($assignmentsByWatchman, $logsIndexed) {
            $carbon = Carbon::parse($dateStr);

            $watchmenItems = $assignmentsByWatchman->map(function ($assignments) use ($logsIndexed, $dateStr, $carbon) {
                $tasks = $assignments
                    ->filter(fn($wt) => $this->isTaskDueOnDate($wt->task, $carbon))
                    ->map(function ($wt) use ($logsIndexed, $dateStr) {
                        $log = $logsIndexed->get($wt->watchman_id . '_' . $wt->task_id . '_' . $dateStr);
                        return (object) [
                            'task'    => $wt->task,
                            'log'     => $log,
                            'is_done' => $log && $log->is_completed,
                        ];
                    });

                return (object) [
                    'watchman' => $assignments->first()->watchman,
                    'tasks'    => $tasks,
                    'done'     => $tasks->where('is_done', true)->count(),
                    'total'    => $tasks->count(),
                ];
            })->filter(fn($wi) => $wi->total > 0)->values();

            return (object) [
                'date'     => $dateStr,
                'watchmen' => $watchmenItems,
                'done'     => $watchmenItems->sum('done'),
                'total'    => $watchmenItems->sum('total'),
            ];
        });

        // Summary stats derived from the filtered report (task-date slots, not raw assignments)
        $totalAssigned   = $dateReport->sum('total');
        $totalCompleted  = $dateReport->sum('done');
        $totalIncomplete = $totalAssigned - $totalCompleted;

        $tasksByScheduleType = Task::where('society_id', $societyId)
            ->where('status', 'active')
            ->selectRaw('recurrence_type, COUNT(*) as count')
            ->groupBy('recurrence_type')
            ->pluck('count', 'recurrence_type');

        return view('masters.task.log', compact(
            'dateMode', 'date', 'from', 'to', 'watchmanId', 'scheduleType',
            'dateReport',
            'totalAssigned', 'totalCompleted', 'totalIncomplete',
            'tasksByScheduleType', 'watchmenList'
        ));
    }

}
