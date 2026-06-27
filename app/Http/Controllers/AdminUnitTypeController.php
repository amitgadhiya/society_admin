<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitType;
use App\Models\MaintenancePlanRate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUnitTypeController extends Controller
{
    public function index(Request $request)
    {
        $societyId = Auth::user()->society_id;

        $query = UnitType::where('society_id', $societyId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $unitTypes = $query->withCount('units')->orderBy('name')->paginate(20)->appends($request->only('search'));

        return view('masters.unit-type.index', compact('unitTypes'));
    }

    public function create()
    {
        $this->ensureManager(Auth::user());
        return view('masters.unit-type.create');
    }

    public function store(Request $request)
    {
        $this->ensureManager(Auth::user());
        $societyId = Auth::user()->society_id;

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:100', Rule::unique('unit_types', 'name')->where(fn ($q) => $q->where('society_id', $societyId))],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        UnitType::create([
            'society_id' => $societyId,
            'name'       => trim($validated['name']),
            'status'     => $validated['status'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('unit-type.index')->with('success', 'Unit type created successfully.');
    }

    public function show(UnitType $unitType)
    {
        $this->checkAccess($unitType);
        $units = Unit::with('wing')
            ->where('unit_type_id', $unitType->id)
            ->orderBy('unit_number')
            ->get();

        return view('masters.unit-type.show', compact('unitType', 'units'));
    }

    public function edit(UnitType $unitType)
    {
        $this->ensureManager(Auth::user());
        $this->checkAccess($unitType);

        return view('masters.unit-type.edit', compact('unitType'));
    }

    public function update(Request $request, UnitType $unitType)
    {
        $this->ensureManager(Auth::user());
        $this->checkAccess($unitType);
        $societyId = Auth::user()->society_id;

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:100', Rule::unique('unit_types', 'name')->where(fn ($q) => $q->where('society_id', $societyId))->ignore($unitType->id)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $oldName = $unitType->name;
        $newName = trim($validated['name']);

        $unitType->update(['name' => $newName, 'status' => $validated['status']]);

        if ($newName !== $oldName) {
            Unit::where('unit_type_id', $unitType->id)->update(['unit_type' => $newName]);
        }

        return redirect()->route('unit-type.index')->with('success', 'Unit type updated successfully.');
    }

    public function destroy(UnitType $unitType)
    {
        $this->ensureManager(Auth::user());
        $this->checkAccess($unitType);

        if (Unit::where('unit_type_id', $unitType->id)->exists()) {
            return redirect()->route('unit-type.index')
                ->with('error', 'Unit type cannot be deleted because it is assigned to units.');
        }

        $hasPlanRates = MaintenancePlanRate::where('unit_type', $unitType->name)
            ->whereHas('plan', fn ($q) => $q->where('society_id', Auth::user()->society_id))
            ->exists();

        if ($hasPlanRates) {
            return redirect()->route('unit-type.index')
                ->with('error', 'Unit type cannot be deleted because it is used in maintenance plans.');
        }

        $unitType->delete();

        return redirect()->route('unit-type.index')->with('success', 'Unit type deleted successfully.');
    }

    private function checkAccess(UnitType $unitType): void
    {
        abort_unless((int) $unitType->society_id === (int) Auth::user()->society_id, 403);
    }

    private function ensureManager(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403);
    }
}
