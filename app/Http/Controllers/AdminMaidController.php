<?php

namespace App\Http\Controllers;

use App\Models\Maid;
use App\Models\MaidUnitAssignment;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminMaidController extends Controller
{
    public function index(Request $request)
    {
        $societyId = Auth::user()->society_id;
        $query     = Maid::where('society_id', $societyId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $maids = $query->latest()->paginate(15)->appends($request->only('search', 'status'));

        return view('masters.maid.index', compact('maids'));
    }

    public function create()
    {
        return view('masters.maid.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'mobile'         => ['nullable', 'string', 'max:20'],
            'photo'          => ['nullable', 'image'],
            'aadhaar_number' => ['nullable', 'string', 'max:20'],
            'address'        => ['nullable', 'string', 'max:500'],
            'status'         => ['required', 'in:active,inactive'],
        ]);

        $validated['society_id'] = Auth::user()->society_id;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('maids', 'public');
        }

        Maid::create($validated);

        return redirect()->route('maid.index')->with('success', 'Maid added successfully.');
    }

    public function show(Maid $maid)
    {
        $this->checkAccess($maid);

        $maid->load(['unitAssignments.unit.wing']);

        $assignedUnitIds = $maid->unitAssignments->pluck('unit_id');
        $availableUnits  = Unit::with('wing')
            ->where('society_id', Auth::user()->society_id)
            ->whereNotIn('id', $assignedUnitIds)
            ->orderBy('unit_number')
            ->get();

        return view('masters.maid.show', compact('maid', 'availableUnits'));
    }

    public function edit(Maid $maid)
    {
        $this->checkAccess($maid);

        return view('masters.maid.edit', compact('maid'));
    }

    public function update(Request $request, Maid $maid)
    {
        $this->checkAccess($maid);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'mobile'         => ['nullable', 'string', 'max:20'],
            'photo'          => ['nullable', 'image'],
            'aadhaar_number' => ['nullable', 'string', 'max:20'],
            'address'        => ['nullable', 'string', 'max:500'],
            'status'         => ['required', 'in:active,inactive'],
        ]);

        if ($request->hasFile('photo')) {
            if ($maid->photo) {
                Storage::disk('public')->delete($maid->photo);
            }
            $validated['photo'] = $request->file('photo')->store('maids', 'public');
        }

        $maid->update($validated);

        return redirect()->route('maid.show', $maid)->with('success', 'Maid updated successfully.');
    }

    public function destroy(Maid $maid)
    {
        $this->checkAccess($maid);

        if ($maid->photo) {
            Storage::disk('public')->delete($maid->photo);
        }

        $maid->delete();

        return redirect()->route('maid.index')->with('success', 'Maid deleted successfully.');
    }

    public function assignUnit(Request $request, Maid $maid)
    {
        $this->checkAccess($maid);

        $validated = $request->validate([
            'assign_unit_id'    => ['required', 'integer', 'exists:units,id'],
            'assign_type'       => ['required', 'in:maid,cook,driver,nanny,babysitter,cleaner,others'],
            'assign_start_date' => ['required', 'date'],
            'assign_start_time' => ['required', 'date_format:H:i,H:i:s'],
            'assign_end_time'   => ['required', 'date_format:H:i,H:i:s'],
        ]);

        $unit = Unit::find($validated['assign_unit_id']);
        if (!$unit || $unit->society_id !== Auth::user()->society_id) {
            return back()->with('error', 'Invalid unit.');
        }

        $exists = MaidUnitAssignment::where('maid_id', $maid->id)
            ->where('unit_id', $validated['assign_unit_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This unit is already assigned to this maid.');
        }

        MaidUnitAssignment::create([
            'maid_id'    => $maid->id,
            'unit_id'    => $validated['assign_unit_id'],
            'type'       => $validated['assign_type'],
            'start_date' => $validated['assign_start_date'],
            'start_time' => $validated['assign_start_time'],
            'end_time'   => $validated['assign_end_time'],
            'status'     => 'active',
            'is_permitted'     => 'pending',
        ]);

        return back()->with('success', 'Unit assigned successfully.');
    }

    public function updateAssignment(Request $request, MaidUnitAssignment $assignment)
    {
        $this->checkAccess($assignment->maid);

        $validated = $request->validate([
            'type'       => ['required', 'in:maid,cook,driver,nanny,babysitter,cleaner,others'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time'   => ['required', 'date_format:H:i,H:i:s'],
        ]);

        $assignment->type       = $validated['type'];
        $assignment->start_date = $validated['start_date'];
        $assignment->end_date   = $validated['end_date'] ?? null;
        $assignment->start_time = substr($validated['start_time'], 0, 5);
        $assignment->end_time   = substr($validated['end_time'], 0, 5);
        $assignment->save();

        return back()->with('success', 'Assignment updated successfully.');
    }

    public function toggleAssignment(MaidUnitAssignment $assignment)
    {
        $this->checkAccess($assignment->maid);

        $assignment->status = $assignment->status === 'active' ? 'inactive' : 'active';
        $assignment->save();

        return back()->with('success', 'Assignment status updated.');
    }

    public function removeAssignment(MaidUnitAssignment $assignment)
    {
        $this->checkAccess($assignment->maid);

        $assignment->delete();

        return back()->with('success', 'Assignment removed.');
    }

    private function checkAccess(Maid $maid): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ((int) $maid->society_id !== (int) $user->society_id) {
            abort(403);
        }
    }
}
