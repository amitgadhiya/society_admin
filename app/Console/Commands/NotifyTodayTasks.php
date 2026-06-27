<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Watchman;
use App\Models\WatchmanTask;
use App\Models\WatchmanTaskLog;
use App\Services\WatchmanNotificationService;

class NotifyTodayTasks extends Command
{
    protected $signature = 'tasks:notify-today';

    protected $description = 'Send notifications for tasks due today';

    public function handle(): int
    {
        $today     = Carbon::today();
        $dayOfWeek = (int) $today->dayOfWeekIso;
        $day       = (int) $today->day;
        $month     = (int) $today->month;

        Watchman::where('active', true)->each(function (Watchman $watchman) use ($today, $dayOfWeek, $day, $month) {
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

            $taskIds = $watchmanTasks->pluck('task_id')->all();

            $completionCounts = WatchmanTaskLog::whereIn('task_id', $taskIds)
                ->where('watchman_id', $watchman->id)
                ->where('is_completed', true)
                ->selectRaw('task_id, COUNT(*) as cnt')
                ->groupBy('task_id')
                ->pluck('cnt', 'task_id');

            $completedTodayIds = WatchmanTaskLog::whereIn('task_id', $taskIds)
                ->where('watchman_id', $watchman->id)
                ->where('completion_date', $today->toDateString())
                ->where('is_completed', true)
                ->pluck('task_id')
                ->flip()
                ->all();

            $dueTasks = $watchmanTasks->filter(function (WatchmanTask $wt) use ($today, $dayOfWeek, $day, $month, $completionCounts, $completedTodayIds) {
                $task = $wt->task;

                if (
                    $task->is_repetitive
                    && $task->recurrence_ends === 'after_occurrences'
                    && $task->occurrences
                ) {
                    $done           = (int) ($completionCounts[$task->id] ?? 0);
                    $completedToday = isset($completedTodayIds[$task->id]);
                    if ($done >= (int) $task->occurrences && ! $completedToday) {
                        return false;
                    }
                }

                return $this->isDueToday($task, $today, $dayOfWeek, $day, $month);
            });

            $now = Carbon::now();

            foreach ($dueTasks as $wt) {
                $task = $wt->task;

                if (isset($completedTodayIds[$task->id])) {
                    continue;
                }

                // Skip tasks with no scheduled time
                if (empty($task->scheduled_time)) {
                    continue;
                }

                // Only fire at the exact scheduled minute
                if ($now->format('H:i') !== Carbon::parse($task->scheduled_time)->format('H:i')) {
                    continue;
                }

                // Calculate the due date for the notification body
                $dueDate = $task->is_repetitive
                    ? $today->copy()->addDays(max(0, (int) $task->days_to_complete - 1))->toDateString()
                    : $task->deadline_date?->toDateString();

                $calTime=Carbon::parse($task->scheduled_time)->format('h:i A');
                WatchmanNotificationService::notify(
                    $watchman->id,
                    'Task Reminder',
                    "Task: {$task->title}" . ($dueDate ? " — complete by {$dueDate} {$calTime}" : ''),
                    'task_alert_reminder'
                );

                $this->info("Notified Watchman #{$watchman->id} | Task: {$task->title} | Due: {$dueDate}");
            }
        });

        return self::SUCCESS;
    }

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
            'quarterly', 'biannual', 'annual' => true,
            default                           => false,
        };
    }

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
}
