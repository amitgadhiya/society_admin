<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use App\Models\Wing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminWingController extends Controller
{
    public function index(Request $request)
    {
        $societyId = Auth::user()->society_id;

        $query = Wing::where('society_id', $societyId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $wings = $query->withCount('units')->orderBy('name')->paginate(20)->appends($request->only('search'));

        return view('masters.wing.index', compact('wings'));
    }

    public function create()
    {
        $this->ensureManager(Auth::user());
        return view('masters.wing.create');
    }

    public function store(Request $request)
    {
        $this->ensureManager(Auth::user());
        $societyId = Auth::user()->society_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('wings', 'name')->where(fn ($q) => $q->where('society_id', $societyId))],
        ]);

        Wing::create([
            'society_id' => $societyId,
            'name'       => trim($validated['name']),
        ]);

        return redirect()->route('wing.index')->with('success', 'Wing created successfully.');
    }

    public function show(Wing $wing)
    {
        $this->checkAccess($wing);

        $units = Unit::with('unitType')
            ->where('wing_id', $wing->id)
            ->orderBy('unit_number')
            ->get();

        return view('masters.wing.show', compact('wing', 'units'));
    }

    public function edit(Wing $wing)
    {
        $this->ensureManager(Auth::user());
        $this->checkAccess($wing);

        return view('masters.wing.edit', compact('wing'));
    }

    public function update(Request $request, Wing $wing)
    {
        $this->ensureManager(Auth::user());
        $this->checkAccess($wing);
        $societyId = Auth::user()->society_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('wings', 'name')->where(fn ($q) => $q->where('society_id', $societyId))->ignore($wing->id)],
        ]);

        $wing->update(['name' => trim($validated['name'])]);

        return redirect()->route('wing.index')->with('success', 'Wing updated successfully.');
    }

    public function destroy(Wing $wing)
    {
        $this->ensureManager(Auth::user());
        $this->checkAccess($wing);

        if (Unit::where('wing_id', $wing->id)->exists()) {
            return redirect()->route('wing.index')
                ->with('error', 'Wing cannot be deleted because units are assigned to it.');
        }

        $wing->delete();

        return redirect()->route('wing.index')->with('success', 'Wing deleted successfully.');
    }

    private function checkAccess(Wing $wing): void
    {
        abort_unless((int) $wing->society_id === (int) Auth::user()->society_id, 403);
    }

    private function ensureManager(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403);
    }
}
