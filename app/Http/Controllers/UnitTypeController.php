<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Models\MaintenancePlanRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UnitTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $types = UnitType::query()
            ->where('society_id', $user->society_id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'unit_types' => $types,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureManager($authUser);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('unit_types', 'name')->where(
                    fn ($query) => $query->where('society_id', $authUser->society_id)
                ),
            ],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $type = UnitType::create([
            'society_id' => $authUser->society_id,
            'name' => trim((string) $validated['name']),
            'status' => $validated['status'] ?? 'active',
            'created_by' => $authUser->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Unit type created successfully.',
            'unit_type' => $type,
        ], 201);
    }

    public function update(Request $request, UnitType $unitType): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureManager($authUser);
        $this->ensureSameSociety($authUser->society_id, $unitType->society_id);

        $validated = $request->validate([
            'name' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('unit_types', 'name')->where(
                    fn ($query) => $query->where('society_id', $authUser->society_id)
                )->ignore($unitType->id),
            ],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $oldName = $unitType->name;

        DB::transaction(function () use ($validated, $unitType, $oldName) {
            $newName = array_key_exists('name', $validated)
                ? trim((string) $validated['name'])
                : $unitType->name;

            $unitType->update([
                'name' => $newName,
                'status' => $validated['status'] ?? $unitType->status,
            ]);

            if ($newName !== $oldName) {
                Unit::query()
                    ->where('unit_type_id', $unitType->id)
                    ->update(['unit_type' => $newName]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Unit type updated successfully.',
            'unit_type' => $unitType,
        ]);
    }

    public function destroy(Request $request, UnitType $unitType): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureManager($authUser);
        $this->ensureSameSociety($authUser->society_id, $unitType->society_id);

        $hasUnits = Unit::query()->where('unit_type_id', $unitType->id)->exists();
        abort_unless(! $hasUnits, 422, 'Unit type cannot be deleted because it is assigned to units.');

        $hasPlanRates = MaintenancePlanRate::query()
            ->where('unit_type', $unitType->name)
            ->whereHas('plan', function ($query) use ($authUser) {
                $query->where('society_id', $authUser->society_id);
            })
            ->exists();

        abort_unless(! $hasPlanRates, 422, 'Unit type cannot be deleted because it is used in maintenance plans.');

        $unitType->delete();

        return response()->json([
            'status' => true,
            'message' => 'Unit type deleted successfully.',
        ]);
    }

    private function ensureManager(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Only admin, secretary, or treasurer can manage unit types.');
    }

    private function ensureSameSociety(?int $expectedSocietyId, ?int $actualSocietyId): void
    {
        abort_unless($expectedSocietyId !== null && $expectedSocietyId === $actualSocietyId, 403, 'Record does not belong to your society.');
    }
}
