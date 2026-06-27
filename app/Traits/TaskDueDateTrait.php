<?php

namespace App\Traits;

use App\Models\Task;
use Carbon\Carbon;

trait TaskDueDateTrait
{
    private function isTaskDueOnDate(Task $task, Carbon $date): bool
    {
        if ($task->recurrence_ends === 'on_date' && $task->end_date) {
            if ($date->gt($task->end_date)) {
                return false;
            }
        }

        if (! $task->is_repetitive) {
            return $task->deadline_date !== null && $date->lte($task->deadline_date);
        }

        $window    = max(1, (int) $task->days_to_complete);
        $dayOfWeek = (int) $date->dayOfWeekIso;
        $day       = (int) $date->day;
        $month     = (int) $date->month;

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
