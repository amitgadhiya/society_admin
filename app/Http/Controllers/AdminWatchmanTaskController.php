<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Watchman;
use App\Models\WatchmanTask;
use App\Services\WatchmanNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminWatchmanTaskController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'watchman_id' => ['required', 'exists:watchmen,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $watchman = Watchman::where('id', $request->watchman_id)
            ->where('society_id', $user->society_id)
            ->firstOrFail();

        // Prevent duplicate (unique constraint guard)
        $exists = WatchmanTask::where('task_id', $task->id)
            ->where('watchman_id', $watchman->id)
            ->exists();

        if ($exists) {
            return back()->with('error', "{$watchman->name} is already assigned to this task.");
        }

        WatchmanTask::create([
            'task_id'     => $task->id,
            'watchman_id' => $watchman->id,
            'status'      => 'active',
        ]);

        WatchmanNotificationService::notify(
            $watchman->id,
            'New Task Assigned',
            "You have been assigned: {$task->title}",
            'task_assign'
        );

        return back()->with('success', "{$watchman->name} assigned successfully.");
    }

    public function storeForWatchman(Request $request, Watchman $watchman): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ((int) $watchman->society_id !== (int) $user->society_id) {
            abort(403);
        }

        $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
        ]);

        $task = Task::findOrFail($request->task_id);

        $exists = WatchmanTask::where('task_id', $task->id)
            ->where('watchman_id', $watchman->id)
            ->exists();

        if ($exists) {
            return back()->with('error', "{$task->title} is already assigned to this watchman.");
        }

        WatchmanTask::create([
            'task_id'     => $task->id,
            'watchman_id' => $watchman->id,
            'status'      => 'active',
        ]);

        WatchmanNotificationService::notify(
            $watchman->id,
            'New Task Assigned',
            "You have been assigned: {$task->title}",
            'task'
        );

        return back()->with('success', "{$task->title} assigned successfully.");
    }

    public function update(WatchmanTask $watchmanTask)
    {
        $this->checkAccess($watchmanTask);

        $watchmanTask->update([
            'status' => $watchmanTask->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Assignment status updated.');
    }

    public function destroy(WatchmanTask $watchmanTask)
    {
        $this->checkAccess($watchmanTask);

        $name = $watchmanTask->watchman->name;
        $watchmanTask->delete();

        return back()->with('success', "{$name} removed from task.");
    }

    private function checkAccess(WatchmanTask $watchmanTask): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $watchmanTask->load('watchman');

        if ((int) $watchmanTask->watchman->society_id !== (int) $user->society_id) {
            abort(403);
        }
    }
}
