<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitMember;
use App\Models\User;
use App\Models\PaymentReceipt;
use App\Models\MaintenanceBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function myDueSummary(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->canViewSocietyDueSummary($user->role)) {
            $unitIds = Unit::query()
                ->where('society_id', $user->society_id)
                ->where('status', 'active')
                ->pluck('id');
        } else {
            $unitIds = UnitMember::query()
                ->where('society_id', $user->society_id)
                ->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
                })
                ->pluck('unit_id');
        }

        $units = Unit::query()
            ->with(['wing'])
            ->whereIn('id', $unitIds)
            ->get();

        $summaries = $units->map(fn (Unit $unit) => $this->buildUnitSummary($unit));

        return response()->json([
            'status' => true,
            'units' => $summaries,
        ]);
    }

    public function unitDueSummary(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        $this->ensureSameSociety($user->society_id, $unit->society_id);

        $isManager = in_array($user->role, ['admin', 'accountant'], true);
        $isMember = UnitMember::query()
            ->where('unit_id', $unit->id)
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            })
            ->exists();

        abort_unless($isManager || $isMember, 403, 'You are not allowed to view this unit summary.');

        return response()->json([
            'status' => true,
            'summary' => $this->buildUnitSummary($unit->load('wing')),
        ]);
    }

    public function unitPayments(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        $this->ensureSameSociety($user->society_id, $unit->society_id);

        $isManager = in_array($user->role, ['admin', 'secretary', 'treasurer', 'accountant'], true);
        abort_unless($isManager, 403, 'Only managers can view payment history.');

        $receipts = $unit->paymentReceipts()
            ->with(['allocations.maintenanceBill'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'payment_receipts' => $receipts,
        ]);
    }

    private function buildUnitSummary(Unit $unit): array
    {
        $bills = $unit->maintenanceBills()
            ->with(['billingCycle', 'items'])
            ->orderByDesc('bill_date')
            ->get();

        // Use the same running-balance formula as the ledger:
        //   outstanding = opening_balance + Σ(bill charges) − Σ(payments received)
        // This is correct even when payments are applied to older bills after newer
        // bills have already been generated (bill.opening_balance is a snapshot and
        // would go stale otherwise).
        $totalCharges   = (float) $bills->sum('total_charges');
        $totalPayments  = (float) PaymentReceipt::where('unit_id', $unit->id)->sum('amount');
        $trueBalance    = (float) ($unit->opening_balance ?? 0) + $totalCharges - $totalPayments;
        $outstandingDue = max(round($trueBalance, 2), 0);

        return [
            'unit' => $unit,
            'summary' => [
                'total_bills'    => $bills->count(),
                'total_charges'  => round($totalCharges, 2),
                'total_paid'     => round($totalPayments, 2),
                'outstanding_due'=> $outstandingDue,
                'overdue_bills'  => $bills->filter(
                    fn ($bill) => $bill->closing_balance > 0 && $bill->due_date->isPast()
                )->count(),
            ],
            'bills' => $bills,
        ];
    }

    private function ensureSameSociety(?int $expectedSocietyId, ?int $actualSocietyId): void
    {
        abort_unless($expectedSocietyId !== null && $expectedSocietyId === $actualSocietyId, 403, 'Record does not belong to your society.');
    }

    public function societyMaintenanceSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $societyId = $user->society_id;

        // Get all active units in the society
        $units = Unit::query()
            ->with(['wing'])
            ->where('society_id', $societyId)
            ->where('status', 'active')
            ->get();

        // Build summary for each unit
        $summaries = $units->map(fn (Unit $unit) => $this->buildUnitSummary($unit));

        // Calculate society-wide totals
        $totalPaid = PaymentReceipt::query()
            ->where('society_id', $societyId)
            ->sum('amount');

        $paidLast30 = PaymentReceipt::query()
            ->where('society_id', $societyId)
            ->where('payment_date', '>=', now()->subDays(30)->toDateString())
            ->sum('amount');

        // Calculate total pending by summing outstanding_due from all unit summaries
        // This ensures consistency with the per-unit calculation
        $totalPending = $summaries->reduce(function ($sum, $summary) {
            $outstandingDue = (float) ($summary['summary']['outstanding_due'] ?? 0);
            return $sum + $outstandingDue;
        }, 0);

        return response()->json([
            'status' => true,
            'units' => $summaries,
            'total_paid' => round((float) $totalPaid, 2),
            'paid_last_30_days' => round((float) $paidLast30, 2),
            'total_pending' => round($totalPending, 2),
        ]);
    }

    private function canViewSocietyDueSummary(?string $role): bool
    {
        return in_array($role, ['admin', 'secretary', 'treasurer', 'accountant'], true);
    }
}
