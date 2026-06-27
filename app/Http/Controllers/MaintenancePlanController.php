<?php

namespace App\Http\Controllers;

use App\Models\MaintenancePlan;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenancePlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $currentPlan = MaintenancePlan::query()
            ->with('rates')
            ->where('society_id', $user->society_id)
            ->whereDate('effective_from', '<=', now()->toDateString())
            ->where('status', 'active')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        $history = MaintenancePlan::query()
            ->with('rates')
            ->where('society_id', $user->society_id)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return response()->json([
            'status' => true,
            'current_plan' => $currentPlan,
            'history' => $history,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureManager($authUser);

        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:same_for_all,by_unit_type'],
            'default_amount' => ['nullable', 'numeric', 'min:0.01'],
            'effective_from' => ['required', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'rates' => ['nullable', 'array'],
            'rates.*.unit_type' => ['required_with:rates', 'string', 'max:100'],
            'rates.*.amount' => ['required_with:rates', 'numeric', 'min:0.01'],
        ]);

        if ($validated['mode'] === 'same_for_all' && empty($validated['default_amount'])) {
            return response()->json([
                'status' => false,
                'message' => 'default_amount is required for same_for_all mode.',
            ], 422);
        }

        if ($validated['mode'] === 'by_unit_type' && empty($validated['rates'])) {
            return response()->json([
                'status' => false,
                'message' => 'rates are required for by_unit_type mode.',
            ], 422);
        }

        if ($validated['mode'] === 'by_unit_type' && ! empty($validated['rates'])) {
            $allowedTypes = UnitType::query()
                ->where('society_id', $authUser->society_id)
                ->pluck('name')
                ->map(fn ($name) => strtolower(trim((string) $name)))
                ->toArray();

            foreach ($validated['rates'] as $rate) {
                $type = strtolower(trim((string) $rate['unit_type']));
                if (! in_array($type, $allowedTypes, true)) {
                    return response()->json([
                        'status' => false,
                        'message' => "Unit type '{$rate['unit_type']}' is not available in society master.",
                    ], 422);
                }
            }
        }

        $plan = DB::transaction(function () use ($validated, $authUser) {
            $plan = MaintenancePlan::query()->updateOrCreate(
                [
                    'society_id' => $authUser->society_id,
                    'effective_from' => $validated['effective_from'],
                ],
                [
                    'mode' => $validated['mode'],
                    'default_amount' => $validated['mode'] === 'same_for_all'
                        ? $validated['default_amount']
                        : null,
                    'status' => $validated['status'] ?? 'active',
                    'created_by' => $authUser->id,
                ],
            );

            $plan->rates()->delete();

            if ($validated['mode'] === 'by_unit_type') {
                foreach ($validated['rates'] as $rate) {
                    $plan->rates()->create([
                        'unit_type' => trim((string) $rate['unit_type']),
                        'amount' => $rate['amount'],
                    ]);
                }
            }

            return $plan->load('rates');
        });

        return response()->json([
            'status' => true,
            'message' => 'Maintenance plan saved successfully.',
            'maintenance_plan' => $plan,
        ]);
    }

    private function ensureManager(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'secretary', 'treasurer']), 403, 'Only admin, secretary, or treasurer can manage maintenance plans.');
    }
}
