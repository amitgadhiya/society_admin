<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Watchman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminWatchmanController extends Controller
{
    public function index(Request $request)
    {
        $societyId = $request->user()->society_id;
        $query     = Watchman::where('society_id', $societyId);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('mobile', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        $watchmen = $query->latest()->paginate(15)->appends($request->only('search'));

        return view('masters.watchman.index', compact('watchmen'));
    }

    public function create()
    {
        return view('masters.watchman.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'mobile'      => ['nullable', 'string', 'max:20'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'password'    => ['required', 'string', 'min:6'],
            'photo'       => ['nullable', 'image',],
            // 'photo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['society_id'] = $request->user()->society_id;
        $validated['password']   = Hash::make($validated['password']);
        $validated['active']     = $request->boolean('active', true);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('watchman', 'public');
        }

        Watchman::create($validated);

        return redirect()->route('watchman.index')->with('success', 'Watchman added successfully.');
    }

    public function show(Watchman $watchman)
    {
        $this->checkAccess($watchman);

        $watchman->load(['watchmanTasks.task']);

        $assignedTaskIds = $watchman->watchmanTasks->pluck('task_id');
        $availableTasks = Task::where('status', 'active')
            ->whereNotIn('id', $assignedTaskIds)
            ->orderBy('title')
            ->get();

        return view('masters.watchman.show', compact('watchman', 'availableTasks'));
    }

    public function edit(Watchman $watchman)
    {
        $this->checkAccess($watchman);

        return view('masters.watchman.edit', compact('watchman'));
    }

    public function update(Request $request, Watchman $watchman)
    {
        $this->checkAccess($watchman);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'mobile'      => ['nullable', 'string', 'max:20'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'password'    => ['nullable', 'string', 'min:6'],
            // 'photo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo'       => ['nullable', 'image',],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['active'] = $request->boolean('active');

        if ($request->hasFile('photo')) {
            if ($watchman->photo) {
                Storage::disk('public')->delete($watchman->photo);
            }
            $validated['photo'] = $request->file('photo')->store('watchman', 'public');
        }

        $watchman->update($validated);

        return redirect()->route('watchman.index')->with('success', 'Watchman updated successfully.');
    }

    public function destroy(Watchman $watchman)
    {
        $this->checkAccess($watchman);

        if ($watchman->photo) {
            Storage::disk('public')->delete($watchman->photo);
        }

        $watchman->delete();

        return redirect()->route('watchman.index')->with('success', 'Watchman deleted successfully.');
    }

    private function checkAccess(Watchman $watchman): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ((int) $watchman->society_id !== (int) $user->society_id) {
            abort(403);
        }
    }
}
