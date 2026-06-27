<?php

namespace App\Http\Controllers;

use App\Models\BillingCycle;
use App\Models\Unit;
use App\Models\User;
use App\Services\MaintenanceBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function indexCycles(Request $request): JsonResponse
    {
        $user = $request->user();

        $cycles = BillingCycle::query()
            ->where('society_id', $user->society_id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return response()->json([
            'status' => true,
            'billing_cycles' => $cycles,
        ]);
    }

    public function storeCycle(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureManager($user);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2000'],
            'due_date' => ['required', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $cycle = BillingCycle::create([
            'society_id' => $user->society_id,
            'title' => $validated['title'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'due_date' => $validated['due_date'],
            'status' => $validated['status'] ?? 'draft',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Billing cycle created successfully.',
            'billing_cycle' => $cycle,
        ], 201);
    }

    public function generateBills(
        Request $request,
        BillingCycle $billingCycle,
        MaintenanceBillingService $billingService,
    ): JsonResponse
    {
        $user = $request->user();
        $this->ensureManager($user);
        $this->ensureSameSociety($user->society_id, $billingCycle->society_id);

        $validated = $request->validate([
            'bill_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'unit_ids' => ['nullable', 'array'],
            'unit_ids.*' => ['integer', 'exists:units,id'],
            'charge_items' => ['required', 'array', 'min:1'],
            'charge_items.*.charge_name' => ['required', 'string', 'max:255'],
            'charge_items.*.charge_code' => ['nullable', 'string', 'max:100'],
            'charge_items.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $unitsQuery = Unit::query()
            ->where('society_id', $user->society_id)
            ->where('status', 'active');

        if (! empty($validated['unit_ids'])) {
            $unitsQuery->whereIn('id', $validated['unit_ids']);
        }

        $units = $unitsQuery->get();
        $billDate = $validated['bill_date'] ?? now()->toDateString();
        $dueDate = $validated['due_date'] ?? $billingCycle->due_date->toDateString();
        $result = $billingService->generateBills(
            $billingCycle,
            $units,
            $validated['charge_items'],
            $billDate,
            $dueDate,
        );

        return response()->json([
            'status' => true,
            'message' => 'Bills generated successfully.',
            'generated_count' => count($result['generatedBills']),
            'skipped_count' => $result['skippedCount'],
            'bills' => $result['generatedBills'],
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
