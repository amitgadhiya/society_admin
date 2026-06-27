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

class AdminTaskReportController extends Controller
{
    use TaskDueDateTrait;
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $societyId = $user->society_id;
        $today     = now()->toDateString();

        $days = (int) $request->get('days', 14);
        if (! in_array($days, [7, 14, 30])) {
            $days = 14;
        }
        $from = now()->subDays($days - 1)->toDateString();

        $scheduleType = $request->get('schedule_type');
        $validTypes   = ['daily', 'weekly', 'monthly', 'quarterly', 'biannual', 'annual'];
        if (! in_array($scheduleType, $validTypes)) {
            $scheduleType = null;
        }

        // Society watchman IDs — used for scoping all log queries
        $watchmanIds = Watchman::where('society_id', $societyId)->pluck('id');

        // --- Summary stats ---
        $completedToday = WatchmanTaskLog::whereIn('watchman_id', $watchmanIds)
            ->where('is_completed', true)
            ->where('completion_date', $today)
            ->count();

        $completedThisMonth = WatchmanTaskLog::whereIn('watchman_id', $watchmanIds)
            ->where('is_completed', true)
            ->whereYear('completion_date', now()->year)
            ->whereMonth('completion_date', now()->month)
            ->count();

        $activeWatchmen = Watchman::where('society_id', $societyId)->where('active', true)->count();

        $activeTasks = Task::where('society_id', $societyId)->where('status', 'active')->count();

        // Breakdown of active tasks by schedule type
        $tasksByScheduleType = Task::where('society_id', $societyId)
            ->where('status', 'active')
            ->selectRaw('recurrence_type, COUNT(*) as count')
            ->groupBy('recurrence_type')
            ->pluck('count', 'recurrence_type');

        // --- By Day ---
        $byDayRaw = WatchmanTaskLog::whereIn('watchman_id', $watchmanIds)
            ->where('is_completed', true)
            ->where('completion_date', '>=', $from)
            ->selectRaw('completion_date, COUNT(*) as total')
            ->groupBy('completion_date')
            ->pluck('total', 'completion_date');

        $dayLabels = [];
        $dayCounts = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date        = now()->subDays($i)->toDateString();
            $dayLabels[] = now()->subDays($i)->format('d M');
            $dayCounts[] = (int) ($byDayRaw[$date] ?? 0);
        }

        // --- By Task ---
        $byTask = Task::where('society_id', $societyId)
        ->withCount([
            'logs as total_completions' => fn ($q) => $q
                ->whereIn('watchman_id', $watchmanIds)
                ->where('is_completed', true),
            'logs as today_completions' => fn ($q) => $q
                ->whereIn('watchman_id', $watchmanIds)
                ->where('is_completed', true)
                ->where('completion_date', $today),
        ])
        ->when($scheduleType, fn ($q) => $q->where('recurrence_type', $scheduleType))
        ->having('total_completions', '>', 0)
        ->orderByDesc('total_completions')
        ->get();

        // --- Watchman daily report (accordion + all-time stats) ---
        $watchmanLogTotals = WatchmanTaskLog::whereIn('watchman_id', $watchmanIds)
            ->where('is_completed', true)
            ->selectRaw('watchman_id, COUNT(*) as total, MAX(completion_date) as last_date')
            ->groupBy('watchman_id')
            ->get()
            ->keyBy('watchman_id');

        $todayLogMap = WatchmanTaskLog::whereIn('watchman_id', $watchmanIds)
            ->where('completion_date', $today)
            ->get()
            ->keyBy(fn ($log) => $log->watchman_id . '_' . $log->task_id);

        $todayCarbon = Carbon::today();

        $watchmanDailyReport = WatchmanTask::with(['watchman', 'task'])
            ->whereIn('watchman_id', $watchmanIds)
            ->where('status', 'active')
            ->whereHas('task', fn ($q) => $q->where('status', 'active'))
            ->when($scheduleType, fn ($q) => $q->whereHas('task', fn ($tq) => $tq->where('recurrence_type', $scheduleType)))
            ->get()
            ->groupBy('watchman_id')
            ->map(function ($assignments) use ($todayLogMap, $watchmanLogTotals, $todayCarbon) {
                $tasks = $assignments
                    ->filter(fn ($wt) => $this->isTaskDueOnDate($wt->task, $todayCarbon))
                    ->map(function ($wt) use ($todayLogMap) {
                        $log = $todayLogMap[$wt->watchman_id . '_' . $wt->task_id] ?? null;
                        return (object) [
                            'task'    => $wt->task,
                            'log'     => $log,
                            'is_done' => $log && $log->is_completed,
                        ];
                    });
                $allTimeRow = $watchmanLogTotals[$assignments->first()->watchman_id] ?? null;
                return (object) [
                    'watchman'        => $assignments->first()->watchman,
                    'tasks'           => $tasks,
                    'done_count'      => $tasks->where('is_done', true)->count(),
                    'total'           => $tasks->count(),
                    'completed_total' => (int) ($allTimeRow?->total ?? 0),
                    'last_completion' => $allTimeRow?->last_date,
                ];
            })
            ->filter(fn ($item) => $item->total > 0)
            ->sortBy(fn ($item) => $item->done_count / max($item->total, 1))
            ->values();

        return view('masters.task.report', compact(
            'completedToday', 'completedThisMonth', 'activeWatchmen', 'activeTasks',
            'dayLabels', 'dayCounts', 'byTask', 'days', 'today',
            'watchmanDailyReport', 'scheduleType', 'tasksByScheduleType'
        ));
    }
}
