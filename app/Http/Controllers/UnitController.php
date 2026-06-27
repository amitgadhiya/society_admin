<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitMember;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Wing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $units = Unit::query()
            ->with([
                'wing',
                'unitType',
                'members.user',
            ])
            ->where('society_id', $user->society_id)
            ->orderBy('unit_number')
            ->get();

        return response()->json([
            'status' => true,
            'units' => $units,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureManager($user);

        $validated = $request->validate([
            'wing_id' => ['nullable', 'integer', 'exists:wings,id'],
            'unit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_number')->where(
                    fn ($query) => $query->where('society_id', $user->society_id)
                ),
            ],
            'floor' => ['nullable', 'integer', 'min:0'],
            'unit_type' => ['nullable', 'string', 'max:100'],
            'unit_type_id' => ['nullable', 'integer', 'exists:unit_types,id'],
            'area_sqft' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'primary_owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'owner_start_date' => ['nullable', 'date'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'registered_in_name_of' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
        ]);

        if (! empty($validated['wing_id'])) {
            $wing = Wing::query()->findOrFail($validated['wing_id']);
            abort_unless($wing->society_id === $user->society_id, 403, 'Wing does not belong to your society.');
        }

        if (! empty($validated['primary_owner_user_id'])) {
            $owner = User::query()->findOrFail($validated['primary_owner_user_id']);
            abort_unless($owner->society_id === $user->society_id, 403, 'Owner does not belong to your society.');
        }

        $resolvedUnitType = null;

        if (! empty($validated['unit_type_id'])) {
            $resolvedUnitType = UnitType::query()->findOrFail($validated['unit_type_id']);
            abort_unless($resolvedUnitType->society_id === $user->society_id, 403, 'Unit type does not belong to your society.');
        } elseif (! empty($validated['unit_type'])) {
            $resolvedUnitType = UnitType::query()
                ->where('society_id', $user->society_id)
                ->whereRaw('LOWER(name) = ?', [strtolower(trim((string) $validated['unit_type']))])
                ->first();

            abort_unless($resolvedUnitType !== null, 422, 'Unit type must be selected from society master.');
        }

        $unit = DB::transaction(function () use ($validated, $user, $resolvedUnitType) {
            $unit = Unit::create([
                'society_id' => $user->society_id,
                'wing_id' => $validated['wing_id'] ?? null,
                'unit_type_id' => $resolvedUnitType?->id,
                'unit_number' => $validated['unit_number'],
                'floor' => $validated['floor'] ?? null,
                'unit_type' => $resolvedUnitType?->name,
                'area_sqft' => $validated['area_sqft'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'registered_in_name_of' => $validated['registered_in_name_of'] ?? null,
                'contact_number' => $validated['contact_number'] ?? null,
            ]);

            if (! empty($validated['primary_owner_user_id'])) {
                UnitMember::create([
                    'society_id' => $user->society_id,
                    'unit_id' => $unit->id,
                    'user_id' => $validated['primary_owner_user_id'],
                    'member_type' => 'owner',
                    'is_primary' => true,
                    'start_date' => $validated['owner_start_date'] ?? now()->toDateString(),
                ]);
            }

            return $unit;
        });

        return response()->json([
            'status' => true,
            'message' => 'Unit created successfully.',
            'unit' => $unit->load(['wing', 'unitType', 'members.user']),
        ], 201);
    }

    public function addMember(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        $this->ensureManager($user);
        $this->ensureSameSociety($user->society_id, $unit->society_id);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'member_type' => ['required', 'string', 'max:50'],
            'is_primary' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $memberUser = User::query()->findOrFail($validated['user_id']);
        $this->ensureSameSociety($user->society_id, $memberUser->society_id);

        $member = DB::transaction(function () use ($validated, $user, $unit) {
            if (($validated['is_primary'] ?? false) === true) {
                UnitMember::query()
                    ->where('unit_id', $unit->id)
                    ->update(['is_primary' => false]);
            }

            return UnitMember::create([
                'society_id' => $user->society_id,
                'unit_id' => $unit->id,
                'user_id' => $validated['user_id'],
                'member_type' => $validated['member_type'],
                'is_primary' => $validated['is_primary'] ?? false,
                'start_date' => $validated['start_date'] ?? now()->toDateString(),
                'end_date' => $validated['end_date'] ?? null,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Unit member added successfully.',
            'member' => $member->load('user'),
        ], 201);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        $this->ensureManager($user);
        $this->ensureSameSociety($user->society_id, $unit->society_id);

        $validated = $request->validate([
            'wing_id' => ['nullable', 'integer', 'exists:wings,id'],
            'unit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_number')->where(
                    fn ($query) => $query->where('society_id', $user->society_id)
                )->ignore($unit->id),
            ],
            'floor' => ['nullable', 'integer', 'min:0'],
            'unit_type' => ['nullable', 'string', 'max:100'],
            'unit_type_id' => ['nullable', 'integer', 'exists:unit_types,id'],
            'area_sqft' => ['nullable', 'numeric', 'min:0'],
            'maintenance_scheme' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'registered_in_name_of' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
        ]);

        if (! empty($validated['wing_id'])) {
            $wing = Wing::query()->findOrFail($validated['wing_id']);
            abort_unless($wing->society_id === $user->society_id, 403, 'Wing does not belong to your society.');
        }

        $resolvedUnitType = null;

        if (! empty($validated['unit_type_id'])) {
            $resolvedUnitType = UnitType::query()->findOrFail($validated['unit_type_id']);
            abort_unless($resolvedUnitType->society_id === $user->society_id, 403, 'Unit type does not belong to your society.');
        } elseif (! empty($validated['unit_type'])) {
            $resolvedUnitType = UnitType::query()
                ->where('society_id', $user->society_id)
                ->whereRaw('LOWER(name) = ?', [strtolower(trim((string) $validated['unit_type']))])
                ->first();

            abort_unless($resolvedUnitType !== null, 422, 'Unit type must be selected from society master.');
        }

        $unit->update([
            'wing_id' => $validated['wing_id'] ?? null,
            'unit_type_id' => $resolvedUnitType?->id,
            'unit_number' => $validated['unit_number'],
            'floor' => $validated['floor'] ?? null,
            'unit_type' => $resolvedUnitType?->name,
            'area_sqft' => $validated['area_sqft'] ?? null,
            'status' => $validated['status'] ?? $unit->status,
            'opening_balance' => $validated['opening_balance'] ?? $unit->opening_balance,
            'registered_in_name_of' => array_key_exists('registered_in_name_of', $validated) ? $validated['registered_in_name_of'] : $unit->registered_in_name_of,
            'contact_number' => array_key_exists('contact_number', $validated) ? $validated['contact_number'] : $unit->contact_number,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Unit updated successfully.',
            'unit' => $unit->fresh()->load(['wing', 'unitType', 'members.user']),
        ]);
    }

    public function destroy(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        $this->ensureManager($user);
        $this->ensureSameSociety($user->society_id, $unit->society_id);

        $hasFinancialData = $unit->maintenanceBills()->exists()
            || $unit->paymentReceipts()->exists()
            || $unit->incomeEntries()->exists();

        abort_unless(! $hasFinancialData, 422, 'Unit cannot be deleted because financial records exist for this unit.');

        $unit->delete();

        return response()->json([
            'status' => true,
            'message' => 'Unit deleted successfully.',
        ]);
    }

    private function ensureManager(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer', 'accountant']), 403, 'Only admin, secretary, treasurer, or accountant can perform this action.');
    }

    private function ensureSameSociety(?int $expectedSocietyId, ?int $actualSocietyId): void
    {
        abort_unless($expectedSocietyId !== null && $expectedSocietyId === $actualSocietyId, 403, 'Record does not belong to your society.');
    }
}
