<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Watchman;
use App\Models\WatchmanTask;
use App\Models\WatchmanTaskLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class WatchmanTaskController extends Controller
{
    /** List active tasks due today for the authenticated watchman */
    public function index(Request $request): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman  = $request->user();
        $today     = Carbon::today();
        $dayOfWeek = (int) $today->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $day       = (int) $today->day;
        $month     = (int) $today->month;
        // SQL: fetch candidates — status/society checks + skip tasks past their end_date.
        // Day-matching and after_occurrences are handled in PHP below.
        $watchmanTasks = WatchmanTask::with(['task'])
            ->where('watchman_id', $watchman->id)
            ->where('status', 'active')
            ->whereHas('task', function ($q) use ($watchman, $today) {
                $q->where('society_id', $watchman->society_id)
                  ->where('status', 'active')
                  ->where(function ($q) use ($today) {
                      $q->whereNull('recurrence_ends')
                        ->orWhere('recurrence_ends', 'never')
                        ->orWhere('recurrence_ends', 'after_occurrences')
                        ->orWhere(function ($q) use ($today) {
                            $q->where('recurrence_ends', 'on_date')
                              ->whereDate('end_date', '>=', $today);
                        });
                  });
            })
            ->get();

        // Preload total completions and today's completions in two queries
        $taskIds = $watchmanTasks->pluck('task_id')->all();

        $completionCounts = WatchmanTaskLog::whereIn('task_id', $taskIds)
            ->where('watchman_id', $watchman->id)
            ->where('is_completed', true)
            ->selectRaw('task_id, COUNT(*) as cnt')
            ->groupBy('task_id')
            ->pluck('cnt', 'task_id');

        // Set of task_ids the watchman already completed today
        $completedTodayIds = WatchmanTaskLog::whereIn('task_id', $taskIds)
            ->where('watchman_id', $watchman->id)
            ->where('completion_date', $today->toDateString())
            ->where('is_completed', true)
            ->pluck('task_id')
            ->flip()
            ->all();

        $tasks = $watchmanTasks
            ->filter(function (WatchmanTask $wt) use ($today, $dayOfWeek, $day, $month, $completionCounts, $completedTodayIds) {
                $task = $wt->task;

                // recurrence_ends: after_occurrences
                // Hide only when the limit is reached AND the task was NOT completed today.
                // If completed today, keep showing it so the watchman sees their last occurrence.
                if (
                    $task->is_repetitive
                    && $task->recurrence_ends === 'after_occurrences'
                    && $task->occurrences
                ) {
                    $done = (int) ($completionCounts[$task->id] ?? 0);
                    $completedToday = isset($completedTodayIds[$task->id]);
                    if ($done >= (int) $task->occurrences && ! $completedToday) {
                        return false;
                    }
                }

                return $this->isDueToday($task, $today, $dayOfWeek, $day, $month);
            })
            ->values()
            ->map(function (WatchmanTask $wt) use ($watchman, $today) {
                $log = WatchmanTaskLog::where('task_id', $wt->task_id)
                    ->where('watchman_id', $watchman->id)
                    ->where('completion_date', $today)
                    ->first();

                $task = $wt->task;
                $dayOfComplete = $task->is_repetitive
                    ? $today->copy()->addDays((int) $task->days_to_complete)->toDateString()
                    : $task->deadline_date?->toDateString();

                return [
                    'id'              => $task->id,
                    'title'           => $task->title,
                    'description'     => $task->description,
                    'scheduled_time'  => $task->scheduled_time,
                    'day_of_complete' => $dayOfComplete,
                    'is_completed'    => $log?->is_completed ?? false,
                    'completed_at'    => $log?->completed_at,
                    'remarks'         => $log?->remarks,
                ];
            });

        return response()->json(['status' => true, 'tasks' => $tasks]);
    }

    /**
     * Decide if a task is due today, respecting days_to_complete as an open window
     * after the trigger date (not just the exact trigger day).
     */
    private function isDueToday(Task $task, Carbon $today, int $dayOfWeek, int $day, int $month): bool
    {
        if (! $task->is_repetitive) {
            return $task->deadline_date !== null
                && $task->deadline_date->greaterThanOrEqualTo($today);
        }

        $window = max(1, (int) $task->days_to_complete);

        return match ($task->recurrence_type) {
            'daily'                           => true,
            'weekly'                          => $this->isWeeklyDue($task->week_days ?? [], $dayOfWeek, $window),
            'monthly'                         => $this->isMonthlyDue($task, $day, $month, $window),
            'quarterly', 'biannual', 'annual' => true, // no specific day configured yet in UI
            default                           => false,
        };
    }

    /**
     * Weekly: show on the trigger day AND for the following (days_to_complete - 1) days.
     * E.g. week_days=[1] (Monday), days_to_complete=3 => show Mon, Tue, Wed.
     *
     * Walk back up to $window days: if any of those past days was a trigger day, return true.
     */
    private function isWeeklyDue(array $weekDays, int $dayOfWeek, int $window): bool
    {
        for ($offset = 0; $offset < $window; $offset++) {
            $checkDay = (($dayOfWeek - 1 - $offset + 7) % 7) + 1;
            if (in_array($checkDay, $weekDays, strict: true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Monthly: show from month_day through (month_day + days_to_complete - 1),
     * restricted to the configured months (if any).
     * E.g. month_day=1, days_to_complete=5 => show 1st-5th of each qualifying month.
     */
    private function isMonthlyDue(Task $task, int $day, int $month, int $window): bool
    {
        $monthDay = (int) $task->month_day;
        if ($monthDay <= 0) {
            return false;
        }

        if ($day < $monthDay || $day >= $monthDay + $window) {
            return false;
        }

        $months = $task->months;
        return empty($months) || in_array($month, $months, strict: true);
    }

    /** Mark a task as complete for today */
    public function complete(Request $request, Task $task): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();
        $now = Carbon::now('Asia/Kolkata');

        $assigned = WatchmanTask::where('task_id', $task->id)
            ->where('watchman_id', $watchman->id)
            ->where('status', 'active')
            ->exists();

        if (! $assigned) {
            return response()->json(['status' => false, 'message' => 'Task not assigned to you.'], 403);
        }

        $data = $request->validate([
            'remarks'   => 'nullable|string',
            'photo'     => 'nullable|image',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $existing = WatchmanTaskLog::where('task_id', $task->id)
                ->where('watchman_id', $watchman->id)
                ->where('completion_date', $now->toDateString())
                ->value('photo');
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $photoPath = $request->file('photo')->store('task-photos', 'public');
        }
        
        $updateData = [
            'is_completed' => true,
            'completed_at' => $now,
            'remarks'      => $data['remarks'] ?? null,
            'latitude'     => $data['latitude'] ?? null,
            'longitude'    => $data['longitude'] ?? null,
        ];
        // dd($request->photo);
        if ($photoPath !== null) {
            $updateData['photo'] = $photoPath;
        }

        $log = WatchmanTaskLog::updateOrCreate(
            [
                'task_id'         => $task->id,
                'watchman_id'     => $watchman->id,
                'completion_date' => $now->toDateString(),
            ],
            $updateData
        );

        return response()->json([
            'status'  => true,
            'message' => 'Task marked as complete.',
            'log'     => $log,
        ]);
    }

    /** Unmark a task (set is_completed back to false for today) */
    public function uncomplete(Request $request, Task $task): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();
        $now = Carbon::now('Asia/Kolkata');

        $log = WatchmanTaskLog::where('task_id', $task->id)
            ->where('watchman_id', $watchman->id)
            ->where('completion_date', $now->toDateString())
            ->first();

        if (! $log || ! $log->is_completed) {
            return response()->json(['status' => false, 'message' => 'Task is not marked as complete.'], 422);
        }

        if ($log->photo) {
            Storage::disk('public')->delete($log->photo);
        }

        $log->update([
            'is_completed' => false,
            'completed_at' => null,
            'photo'        => null,
            'latitude'     => null,
            'longitude'    => null,
        ]);

        return response()->json(['status' => true, 'message' => 'Task marked as incomplete.', 'log' => $log]);
    }

    /** View completion logs for a task (history) */
    public function logs(Request $request, Task $task): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();

        $logs = WatchmanTaskLog::where('task_id', $task->id)
            ->where('watchman_id', $watchman->id)
            ->orderByDesc('completion_date')
            ->get();

        return response()->json(['status' => true, 'task' => $task->title, 'logs' => $logs]);
    }
}
