<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitMember;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUnitController extends Controller
{
    public function index(Request $request)
    {
        
        $societyId = $request->user()->society_id;
        $query = Unit::with(['wing', 'unitType'])
            ->where('society_id', $societyId);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('unit_number', 'like', '%' . $request->search . '%')
                  ->orWhere('registered_in_name_of', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('wing')) {
            $query->where('wing_id', $request->wing);
        }
        if ($request->filled('unit_type_id')) {
            $query->where('unit_type_id', $request->unit_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $units = $query->orderBy('unit_number')->paginate(20)->appends($request->only('search', 'wing', 'status'));
        $wings  = Wing::where('society_id', $societyId)->orderBy('name')->get();
        $unitTypes = UnitType::where('society_id', $societyId)->orderBy('name')->get();
        return view('masters.unit.index', compact('units', 'wings', 'unitTypes'));
    }

    public function create()
    {
        $user = Auth::user();
        $this->ensureManager($user);
        $societyId = Auth::user()->society_id;
        $wings     = Wing::where('society_id', $societyId)->orderBy('name')->get();
        $unitTypes = UnitType::where('society_id', $societyId)->orderBy('name')->get();

        return view('masters.unit.create', compact('wings', 'unitTypes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->ensureManager($user);
        
        $societyId = $request->user()->society_id;

        $validated = $request->validate([
            'unit_number'           => ['required', 'string', 'max:50', Rule::unique('units', 'unit_number')->where(fn ($q) => $q->where('society_id', $societyId))],
            'wing_id'               => ['nullable', 'integer', 'exists:wings,id'],
            'unit_type_id'          => ['nullable', 'integer', 'exists:unit_types,id'],
            'floor'                 => ['nullable', 'integer', 'min:0'],
            'area_sqft'             => ['nullable', 'numeric', 'min:0'],
            'status'                => ['required', 'string', Rule::in(['active', 'inactive'])],
            'opening_balance'       => ['nullable', 'numeric', 'min:0'],
            'registered_in_name_of' => ['nullable', 'string', 'max:255'],
            'contact_number'        => ['nullable', 'string', 'max:50'],
        ]);

        if (! empty($validated['wing_id'])) {
            $wing = Wing::findOrFail($validated['wing_id']);
            abort_unless((int) $wing->society_id === (int) $societyId, 403);
        }

        $unitType = null;
        if (! empty($validated['unit_type_id'])) {
            $unitType = UnitType::findOrFail($validated['unit_type_id']);
            abort_unless((int) $unitType->society_id === (int) $societyId, 403);
        }

        Unit::create([
            'society_id'            => $societyId,
            'wing_id'               => $validated['wing_id'] ?? null,
            'unit_type_id'          => $unitType?->id,
            'unit_type'             => $unitType?->name,
            'unit_number'           => $validated['unit_number'],
            'floor'                 => $validated['floor'] ?? null,
            'area_sqft'             => $validated['area_sqft'] ?? null,
            'status'                => $validated['status'],
            'opening_balance'       => $validated['opening_balance'] ?? 0,
            'registered_in_name_of' => $validated['registered_in_name_of'] ?? null,
            'contact_number'        => $validated['contact_number'] ?? null,
        ]);

        return redirect()->route('unit.index')->with('success', 'Unit added successfully.');
    }

    public function show(Unit $unit)
    {
        $user = Auth::user();
        $this->ensureManager($user);
        $this->checkAccess($unit);
        $unit->load(['wing', 'unitType', 'members.user']);

        $existingUserIds = $unit->members->pluck('user_id');
        $availableUsers  = User::where('society_id', $user->society_id)
            ->whereNotIn('id', $existingUserIds)
            ->orderBy('name')
            ->get();

        return view('masters.unit.show', compact('unit', 'availableUsers'));
    }

    public function addMember(Request $request, Unit $unit)
    {
        $user = Auth::user();
        $this->ensureManager($user);
        $this->checkAccess($unit);

        $validated = $request->validate([
            'user_id'     => ['required', 'integer', 'exists:users,id'],
            'member_type' => ['required', 'string', Rule::in(['owner', 'tenant', 'family', 'other'])],
            'is_primary'  => ['nullable', 'boolean'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $memberUser = User::findOrFail($validated['user_id']);
        abort_unless((int) $memberUser->society_id === (int) $user->society_id, 403);

        if (! empty($validated['is_primary'])) {
            UnitMember::where('unit_id', $unit->id)->update(['is_primary' => false]);
        }

        UnitMember::create([
            'society_id'  => $user->society_id,
            'unit_id'     => $unit->id,
            'user_id'     => $validated['user_id'],
            'member_type' => $validated['member_type'],
            'is_primary'  => $request->boolean('is_primary'),
            'start_date'  => $validated['start_date'] ?? now()->toDateString(),
            'end_date'    => $validated['end_date'] ?? null,
        ]);

        return redirect()->route('unit.show', $unit)->with('success', 'Member added successfully.');
    }

    public function updateMember(Request $request, Unit $unit, UnitMember $member)
    {
        $user = Auth::user();
        $this->ensureManager($user);
        $this->checkAccess($unit);
        abort_unless((int) $member->unit_id === (int) $unit->id, 403);

        $validated = $request->validate([
            'is_primary'  => ['nullable', 'boolean'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'member_type' => ['nullable', 'string', Rule::in(['owner', 'tenant', 'family', 'other'])],
        ]);

        if ($request->boolean('is_primary') && ! $member->is_primary) {
            UnitMember::where('unit_id', $unit->id)
                ->where('id', '!=', $member->id)
                ->update(['is_primary' => false]);
        }

        $member->update([
            'is_primary'  => $request->boolean('is_primary'),
            'start_date'  => $validated['start_date'] ?? $member->start_date,
            'end_date'    => $validated['end_date'] ?? null,
            'member_type' => $validated['member_type'] ?? $member->member_type,
        ]);

        return redirect()->route('unit.show', $unit)->with('success', 'Member updated successfully.');
    }

    public function removeMember(Unit $unit, UnitMember $member)
    {
        $user = Auth::user();
        $this->ensureManager($user);
        $this->checkAccess($unit);
        abort_unless((int) $member->unit_id === (int) $unit->id, 403);

        $member->delete();

        return redirect()->route('unit.show', $unit)->with('success', 'Member removed successfully.');
    }

    public function edit(Unit $unit)
    {
        $user = Auth::user();
        $this->ensureManager($user);
        $this->checkAccess($unit);
        $societyId = Auth::user()->society_id;

        $wings     = Wing::where('society_id', $societyId)->orderBy('name')->get();
        $unitTypes = UnitType::where('society_id', $societyId)->orderBy('name')->get();

        return view('masters.unit.edit', compact('unit', 'wings', 'unitTypes'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->checkAccess($unit);
        $societyId = $request->user()->society_id;

        $validated = $request->validate([
            'unit_number'           => ['required', 'string', 'max:50', Rule::unique('units', 'unit_number')->where(fn ($q) => $q->where('society_id', $societyId))->ignore($unit->id)],
            'wing_id'               => ['nullable', 'integer', 'exists:wings,id'],
            'unit_type_id'          => ['nullable', 'integer', 'exists:unit_types,id'],
            'floor'                 => ['nullable', 'integer', 'min:0'],
            'area_sqft'             => ['nullable', 'numeric', 'min:0'],
            'status'                => ['required', 'string', Rule::in(['active', 'inactive'])],
            'opening_balance'       => ['nullable', 'numeric', 'min:0'],
            'registered_in_name_of' => ['nullable', 'string', 'max:255'],
            'contact_number'        => ['nullable', 'string', 'max:50'],
        ]);

        if (! empty($validated['wing_id'])) {
            $wing = Wing::findOrFail($validated['wing_id']);
            abort_unless((int) $wing->society_id === (int) $societyId, 403);
        }

        $unitType = null;
        if (! empty($validated['unit_type_id'])) {
            $unitType = UnitType::findOrFail($validated['unit_type_id']);
            abort_unless((int) $unitType->society_id === (int) $societyId, 403);
        }

        $unit->update([
            'wing_id'               => $validated['wing_id'] ?? null,
            'unit_type_id'          => $unitType?->id,
            'unit_type'             => $unitType?->name,
            'unit_number'           => $validated['unit_number'],
            'floor'                 => $validated['floor'] ?? null,
            'area_sqft'             => $validated['area_sqft'] ?? null,
            'status'                => $validated['status'],
            'opening_balance'       => $validated['opening_balance'] ?? $unit->opening_balance,
            'registered_in_name_of' => $validated['registered_in_name_of'] ?? null,
            'contact_number'        => $validated['contact_number'] ?? null,
        ]);

        return redirect()->route('unit.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        $this->checkAccess($unit);
        $user = Auth::user();
        $this->ensureManager($user);
        $hasFinancialData = $unit->maintenanceBills()->exists()
            || $unit->paymentReceipts()->exists()
            || $unit->incomeEntries()->exists();

        if ($hasFinancialData) {
            return redirect()->route('unit.index')
                ->with('error', 'Unit cannot be deleted because financial records exist for it.');
        }

        $unit->delete();

        return redirect()->route('unit.index')->with('success', 'Unit deleted successfully.');
    }

    private function checkAccess(Unit $unit): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ((int) $unit->society_id !== (int) $user->society_id) {
            abort(403);
        }
    }
    private function ensureManager(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer', 'accountant']), 403, 'Only admin, secretary, treasurer, or accountant can perform this action.');
    }
}
