<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Watchman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTaskController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Task::where('society_id', $user->society_id);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $tasks = $query->latest()->paginate(15)->appends($request->only('search'));

        return view('masters.task.index', compact('tasks'));
    }

    public function create()
    {
        return view('masters.task.create');
    }

    public function store(Request $request)
    {
        $isRepetitive = $request->boolean('is_repetitive');

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'status'           => ['required', 'in:active,inactive'],
            'scheduled_time'   => ['nullable', 'date_format:H:i'],
            'deadline_date'    => ['required_if:is_repetitive,0', 'nullable', 'date'],
            'days_to_complete' => ['required_if:is_repetitive,1', 'nullable', 'integer', 'min:1'],
            'recurrence_type'  => ['required_if:is_repetitive,1', 'nullable', 'in:daily,weekly,monthly,quarterly,biannual,annual'],
            'recurrence_ends'  => ['required_if:is_repetitive,1', 'nullable', 'in:never,after_occurrences,on_date'],
            'occurrences'      => ['required_if:recurrence_ends,after_occurrences', 'nullable', 'integer', 'min:1'],
            'end_date'         => ['required_if:recurrence_ends,on_date', 'nullable', 'date'],
            'week_days'        => ['required_if:recurrence_type,weekly', 'nullable', 'array'],
            'week_days.*'      => ['integer', 'between:1,7'],
            'month_day'        => ['required_if:recurrence_type,monthly', 'nullable', 'integer', 'min:1', 'max:31'],
            'months'           => ['nullable', 'array'],
            'months.*'         => ['integer', 'between:1,12'],
        ]);

        $recEnds = $validated['recurrence_ends'] ?? null;
        $recType = $validated['recurrence_type'] ?? null;

        Task::create([
            'society_id'       => $request->user()->society_id,
            'title'            => $validated['title'],
            'description'      => $validated['description'] ?? null,
            'status'           => $validated['status'],
            'scheduled_time'   => $validated['scheduled_time'],
            'is_repetitive'    => $isRepetitive,
            'deadline_date'    => !$isRepetitive ? ($validated['deadline_date'] ?? null) : null,
            'days_to_complete' => $isRepetitive  ? ($validated['days_to_complete'] ?? null) : null,
            'recurrence_type'  => $isRepetitive  ? $recType : null,
            'recurrence_ends'  => $isRepetitive  ? ($recEnds ?? 'never') : null,
            'occurrences'      => $recEnds === 'after_occurrences' ? ($validated['occurrences'] ?? null) : null,
            'end_date'         => $recEnds === 'on_date' ? ($validated['end_date'] ?? null) : null,
            'week_days'        => $recType === 'weekly'  ? array_map('intval', $validated['week_days'] ?? []) : null,
            'month_day'        => $recType === 'monthly' ? (int) ($validated['month_day'] ?? 1) : null,
            'months'           => $recType === 'monthly' ? array_map('intval', $validated['months'] ?? []) : null,
        ]);

        return redirect()->route('task.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['watchmanTasks.watchman']);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $assignedIds       = $task->watchmanTasks->pluck('watchman_id');
        $availableWatchmen = Watchman::where('society_id', $user->society_id)
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        return view('masters.task.show', compact('task', 'availableWatchmen'));
    }

    public function edit(Task $task)
    {
        return view('masters.task.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $isRepetitive = $request->boolean('is_repetitive');

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'status'           => ['required', 'in:active,inactive'],
            'scheduled_time'   => ['nullable', 'date_format:H:i'],
            'deadline_date'    => ['required_if:is_repetitive,0', 'nullable', 'date'],
            'days_to_complete' => ['required_if:is_repetitive,1', 'nullable', 'integer', 'min:1'],
            'recurrence_type'  => ['required_if:is_repetitive,1', 'nullable', 'in:daily,weekly,monthly,quarterly,biannual,annual'],
            'recurrence_ends'  => ['required_if:is_repetitive,1', 'nullable', 'in:never,after_occurrences,on_date'],
            'occurrences'      => ['required_if:recurrence_ends,after_occurrences', 'nullable', 'integer', 'min:1'],
            'end_date'         => ['required_if:recurrence_ends,on_date', 'nullable', 'date'],
            'week_days'        => ['required_if:recurrence_type,weekly', 'nullable', 'array'],
            'week_days.*'      => ['integer', 'between:1,7'],
            'month_day'        => ['required_if:recurrence_type,monthly', 'nullable', 'integer', 'min:1', 'max:31'],
            'months'           => ['nullable', 'array'],
            'months.*'         => ['integer', 'between:1,12'],
        ]);

        $recEnds = $validated['recurrence_ends'] ?? null;
        $recType = $validated['recurrence_type'] ?? null;
        
        $task->update([
            'title'            => $validated['title'],
            'description'      => $validated['description'] ?? null,
            'status'           => $validated['status'],
            'scheduled_time'   => $validated['scheduled_time'],
            'is_repetitive'    => $isRepetitive,
            'deadline_date'    => !$isRepetitive ? ($validated['deadline_date'] ?? null) : null,
            'days_to_complete' => $isRepetitive  ? ($validated['days_to_complete'] ?? null) : null,
            'recurrence_type'  => $isRepetitive  ? $recType : null,
            'recurrence_ends'  => $isRepetitive  ? ($recEnds ?? 'never') : null,
            'occurrences'      => $recEnds === 'after_occurrences' ? ($validated['occurrences'] ?? null) : null,
            'end_date'         => $recEnds === 'on_date' ? ($validated['end_date'] ?? null) : null,
            'week_days'        => $recType === 'weekly'  ? array_map('intval', $validated['week_days'] ?? []) : null,
            'month_day'        => $recType === 'monthly' ? (int) ($validated['month_day'] ?? 1) : null,
            'months'           => $recType === 'monthly' ? array_map('intval', $validated['months'] ?? []) : null,
        ]);

        return redirect()->route('task.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('task.index')->with('success', 'Task deleted successfully.');
    }
}
