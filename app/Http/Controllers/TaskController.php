<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\WatchmanTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $societyId = $request->user()->society_id;
        $tasks = Task::where('society_id', $societyId)->withCount('watchmanTasks')->latest()->get();

        return response()->json(['status' => true, 'tasks' => $tasks]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'in:active,inactive',
        ]);

        $data['society_id'] = $request->user()->society_id;

        $task = Task::create($data);

        return response()->json(['status' => true, 'task' => $task], 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'status' => true,
            'task'   => $task->load('watchmanTasks.watchman'),
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'in:active,inactive',
        ]);

        $task->update($data);

        return response()->json(['status' => true, 'task' => $task]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(['status' => true, 'message' => 'Task deleted.']);
    }

    /** Assign a task to one or more watchmen */
    public function assign(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'watchman_ids'   => 'required|array',
            'watchman_ids.*' => 'integer|exists:watchmen,id',
        ]);

        foreach ($data['watchman_ids'] as $watchmanId) {
            WatchmanTask::updateOrCreate(
                ['task_id' => $task->id, 'watchman_id' => $watchmanId],
                ['status' => 'active']
            );
        }

        return response()->json([
            'status'  => true,
            'message' => 'Task assigned successfully.',
            'task'    => $task->load('watchmanTasks.watchman'),
        ]);
    }

    /** Remove a watchman's assignment from a task */
    public function unassign(Task $task, int $watchmanId): JsonResponse
    {
        WatchmanTask::where('task_id', $task->id)
            ->where('watchman_id', $watchmanId)
            ->delete();

        return response()->json(['status' => true, 'message' => 'Assignment removed.']);
    }
}
