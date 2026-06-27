<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceBill;
use App\Models\PaymentReceipt;
use App\Models\Unit;
use App\Models\UnitMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    /**
     * GET /my-ledger
     * Returns a chronological statement of all bills and payments for every
     * unit the authenticated member belongs to.
     */
    public function myLedger(Request $request): JsonResponse
    {
        $user = $request->user();

        $unitIds = UnitMember::query()
            ->where('society_id', $user->society_id)
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            })
            ->pluck('unit_id');

        $units = Unit::query()
            ->with('wing')
            ->whereIn('id', $unitIds)
            ->get();

        $result = $units->map(fn (Unit $unit) => $this->buildLedger($unit));

        return response()->json([
            'status' => true,
            'units' => $result,
        ]);
    }

    /**
     * GET /units/{unit}/ledger
     * Admin/manager view: ledger for a specific unit.
     */
    public function unitLedger(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        $this->ensureSameSociety($user->society_id, $unit->society_id);

        $isManager = in_array($user->role, ['admin', 'secretary', 'treasurer', 'accountant'], true);
        $isMember = UnitMember::query()
            ->where('unit_id', $unit->id)
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            })
            ->exists();

        abort_unless($isManager || $isMember, 403, 'You are not allowed to view this unit ledger.');

        return response()->json([
            'status' => true,
            'ledger' => $this->buildLedger($unit->load('wing')),
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function buildLedger(Unit $unit): array
    {
        // ── All bills for this unit (oldest first) ───────────────────────────
        $bills = MaintenanceBill::query()
            ->with(['billingCycle', 'items'])
            ->where('unit_id', $unit->id)
            ->orderBy('bill_date')
            ->orderBy('id')
            ->get();

        // ── All payment receipts for this unit (oldest first) ────────────────
        $receipts = PaymentReceipt::query()
            ->with(['allocations.maintenanceBill', 'user'])
            ->where('unit_id', $unit->id)
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        // ── Build a merged, chronological ledger ─────────────────────────────
        // Each entry is either a 'bill' or a 'payment'. We merge by date then
        // type (bills before payments on same date) and compute running balance.

        $entries = collect();

        foreach ($bills as $bill) {
            $entries->push([
                'type'        => 'bill',
                'sort_date'   => $bill->bill_date->format('Y-m-d'),
                'sort_order'  => 0,
                'id'          => $bill->id,
                'date'        => $bill->bill_date->format('Y-m-d'),
                'due_date'    => $bill->due_date->format('Y-m-d'),
                'description' => $bill->billingCycle?->title ?? $bill->bill_no,
                'bill_no'     => $bill->bill_no,
                'status'      => $bill->status,
                'debit'       => (float) $bill->total_charges,
                'credit'      => 0.0,
                'items'       => $bill->items->map(fn ($i) => [
                    'charge_name' => $i->charge_name,
                    'amount'      => (float) $i->amount,
                ])->values(),
            ]);
        }

        foreach ($receipts as $receipt) {
            $entries->push([
                'type'        => 'payment',
                'sort_date'   => $receipt->payment_date->format('Y-m-d'),
                'sort_order'  => 1,
                'id'          => $receipt->id,
                'date'        => $receipt->payment_date->format('Y-m-d'),
                'due_date'    => null,
                'description' => 'Payment – ' . $receipt->payment_mode
                    . ($receipt->reference_no ? ' (' . $receipt->reference_no . ')' : ''),
                'receipt_no'  => $receipt->receipt_no,
                'payment_mode'=> $receipt->payment_mode,
                'reference_no'=> $receipt->reference_no,
                'paid_by'     => $receipt->user?->name,
                'debit'       => 0.0,
                'credit'      => (float) $receipt->amount,
                'items'       => [],
            ]);
        }

        // Sort by date, then bills before payments on the same date
        $sorted = $entries->sortBy([
            ['sort_date',  'asc'],
            ['sort_order', 'asc'],
            ['id',         'asc'],
        ])->values();

        // Opening balance from unit record (carry-forward before any bill)
        $runningBalance = (float) ($unit->opening_balance ?? 0);
        $openingBalance = $runningBalance;

        $ledgerRows = $sorted->map(function (array $entry) use (&$runningBalance) {
            if ($entry['type'] === 'bill') {
                $runningBalance += $entry['debit'];
            } else {
                $runningBalance -= $entry['credit'];
            }

            return array_merge(
                $entry,
                ['balance' => round($runningBalance, 2)],
            );
        });

        return [
            'unit'            => [
                'id'          => $unit->id,
                'unit_number' => $unit->unit_number,
                'wing'        => $unit->wing?->name,
                'floor'       => $unit->floor,
            ],
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($runningBalance, 2),
            'outstanding_due' => max(round($runningBalance, 2), 0),
            'entries'         => $ledgerRows,
        ];
    }

    private function ensureSameSociety(?int $expected, ?int $actual): void
    {
        abort_unless($expected !== null && $expected === $actual, 403, 'Record does not belong to your society.');
    }
}
