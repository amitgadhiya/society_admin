<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Watchman;
use App\Models\WatchmanTask;
use App\Models\WatchmanTaskLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

// date_default_timezone_set('Asia/Kolkata');
class WatchmanTaskController extends Controller
{
    /** List all active tasks assigned to the authenticated watchman */
    public function index_old(Request $request): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();
        $today    = now()->toDateString();
        $tasks = WatchmanTask::with(['task'])
            ->where('watchman_id', $watchman->id)
            ->where('status', 'active')
            ->get()
            ->map(function (WatchmanTask $wt) use ($watchman, $today) {
                $log = WatchmanTaskLog::where('task_id', $wt->task_id)
                    ->where('watchman_id', $watchman->id)
                    ->where('completion_date', $today)
                    ->first();
                // dd($log?->completed_at);
                return [
                    'id'           => $wt->task->id,
                    'title'        => $wt->task->title,
                    'description'  => $wt->task->description,
                    'is_completed' => $log?->is_completed ?? false,
                    'completed_at' => $log?->completed_at,
                    'remarks'      => $log?->remarks,
                ];
            });
        dd($tasks);
        return response()->json(['status' => true, 'tasks' => $tasks]);
    }
    public function index(Request $request): JsonResponse
    {

        /** @var Watchman $watchman */
        $watchman = $request->user();
        $today = Carbon::today();
        $dayOfWeek = $today->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $day = $today->day;
        $month = $today->month;
        $tasks = WatchmanTask::with(['task'])
        ->where('watchman_id', $watchman->id)
        ->where('status', 'active')
        ->whereHas('task', function ($q) use ($watchman, $today, $dayOfWeek, $day, $month) {
            $q->where('society_id', $watchman->society_id)
            ->where('status', 'active')
            ->where(function ($q) use ($today, $dayOfWeek, $day, $month) {
                // DAILY
                $q->where('schedule_type', 'daily')
                // WEEKLY
                ->orWhere(function ($q) use ($dayOfWeek) {
                    $q->where('schedule_type', 'weekly')
                        ->where('week_day', $dayOfWeek);
                })
                // MONTHLY
                ->orWhere(function ($q) use ($day) {
                    $q->where('schedule_type', 'monthly')
                        ->where('month_day', $day);
                })
                // YEARLY
                ->orWhere(function ($q) use ($day, $month) {
                    $q->where('schedule_type', 'yearly')
                        ->where('annual_day', $day)
                        ->where('annual_month', $month);
                })
                // ONCE
                ->orWhere(function ($q) use ($today) {
                    $q->where('schedule_type', 'once')
                        ->whereDate('due_date', $today);
                });
            });
        })->get()
        ->map(function (WatchmanTask $wt) use ($watchman, $today) {
                $log = WatchmanTaskLog::where('task_id', $wt->task_id)
                    ->where('watchman_id', $watchman->id)
                    ->where('completion_date', $today)
                    ->first();
                // dd($log?->completed_at);
                return [
                    'id'           => $wt->task->id,
                    'title'        => $wt->task->title,
                    'description'  => $wt->task->description,
                    'is_completed' => $log?->is_completed ?? false,
                    'completed_at' => $log?->completed_at,
                    'remarks'      => $log?->remarks,
                ];
        });
        return response()->json(['status' => true, 'tasks' => $tasks]);
    }

    /** Mark a task as complete for today */
    public function complete(Request $request, Task $task): JsonResponse
    {
        /** @var Watchman $watchman */
        $watchman = $request->user();
        $now=Carbon::now('Asia/Kolkata');
        $assigned = WatchmanTask::where('task_id', $task->id)
            ->where('watchman_id', $watchman->id)
            ->where('status', 'active')
            ->exists();

        if (! $assigned) {
            return response()->json(['status' => false, 'message' => 'Task not assigned to you.'], 403);
        }

        $data = $request->validate([
            'remarks' => 'nullable|string',
        ]);

        $log = WatchmanTaskLog::updateOrCreate(
            [
                'task_id'         => $task->id,
                'watchman_id'     => $watchman->id,
                'completion_date' => $now->toDateString(),
            ],
            [
                'is_completed' => true,
                'completed_at' => $now,
                'remarks'      => $data['remarks'] ?? null,
            ]
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
        $now=Carbon::now('Asia/Kolkata');
        $log = WatchmanTaskLog::where('task_id', $task->id)
            ->where('watchman_id', $watchman->id)
            ->where('completion_date', $now->toDateString())
            ->first();

        if (! $log || ! $log->is_completed) {
            return response()->json(['status' => false, 'message' => 'Task is not marked as complete.'], 422);
        }

        $log->update(['is_completed' => false, 'completed_at' => null]);

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
