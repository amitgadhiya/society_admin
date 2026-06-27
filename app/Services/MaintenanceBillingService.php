<?php

namespace App\Services;

use App\Models\BillingCycle;
use App\Models\MaintenanceBill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class MaintenanceBillingService
{
    /**
     * @param Collection<int, \App\Models\Unit> $units
     * @param array<int, array{charge_name:string, charge_code:?string, amount:float|int|string}> $chargeItems
     * @return array{generatedBills: array<int, \App\Models\MaintenanceBill>, skippedCount: int}
     */
    public function generateBills(
        BillingCycle $billingCycle,
        Collection $units,
        array $chargeItems,
        string $billDate,
        string $dueDate,
    ): array {
        $totalCharges = collect($chargeItems)->sum(
            fn (array $item) => (float) $item['amount']
        );

        $generatedBills = [];
        $skippedCount = 0;

        DB::transaction(function () use (
            $billingCycle,
            $units,
            $chargeItems,
            $billDate,
            $dueDate,
            $totalCharges,
            &$generatedBills,
            &$skippedCount,
        ) {
            foreach ($units as $unit) {
                $exists = MaintenanceBill::query()
                    ->where('billing_cycle_id', $billingCycle->id)
                    ->where('unit_id', $unit->id)
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                $previousBill = MaintenanceBill::query()
                    ->where('unit_id', $unit->id)
                    ->orderByDesc('bill_date')
                    ->orderByDesc('id')
                    ->first();

                $openingBalance = $previousBill
                    ? (float) $previousBill->closing_balance
                    : (float) ($unit->opening_balance ?? 0);
                $closingBalance = round($openingBalance + $totalCharges, 2);

                // If a credit from the previous bill fully covers this bill,
                // mark it paid immediately so the member sees no balance due.
                $initialStatus = $closingBalance <= 0 ? 'paid' : 'unpaid';

                $bill = MaintenanceBill::create([
                    'society_id' => $billingCycle->society_id,
                    'billing_cycle_id' => $billingCycle->id,
                    'unit_id' => $unit->id,
                    'bill_no' => sprintf(
                        'BILL-%d-%02d-%d',
                        $billingCycle->year,
                        $billingCycle->month,
                        $unit->id,
                    ),
                    'bill_date' => $billDate,
                    'due_date' => $dueDate,
                    'opening_balance' => $openingBalance,
                    'total_charges' => $totalCharges,
                    'total_discount' => 0,
                    'late_fee' => 0,
                    'total_paid' => 0,
                    'closing_balance' => $closingBalance,
                    'status' => $initialStatus,
                ]);

                foreach ($chargeItems as $index => $item) {
                    $bill->items()->create([
                        'charge_name' => $item['charge_name'],
                        'charge_code' => $item['charge_code'] ?? null,
                        'amount' => $item['amount'],
                        'sort_order' => $index + 1,
                    ]);
                }

                $generatedBills[] = $bill->load('items');
            }
        });

        return [
            'generatedBills' => $generatedBills,
            'skippedCount' => $skippedCount,
        ];
    }
}
