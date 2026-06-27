<?php

namespace App\Http\Controllers;

use App\Models\IncomeCategory;
use App\Models\IncomeEntry;
use App\Models\MaintenanceBill;
use App\Models\PaymentReceipt;
use App\Models\Society;
use App\Models\Unit;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureManager($authUser);

        $validated = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['required', 'string', 'max:50'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'receipt_no' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.maintenance_bill_id' => ['required', 'integer', 'exists:maintenance_bills,id'],
            'allocations.*.allocated_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $unit = Unit::query()->findOrFail($validated['unit_id']);
        $this->ensureSameSociety($authUser->society_id, $unit->society_id);

        if (! empty($validated['user_id'])) {
            $payer = User::query()->findOrFail($validated['user_id']);
            $this->ensureSameSociety($authUser->society_id, $payer->society_id);
        }

        ['allocations' => $allocations, 'remaining' => $unallocated] = $this->prepareAllocations($validated, $unit, (float) $validated['amount']);
        $receiptNumber = $validated['receipt_no'] ?? sprintf('RCPT-%s-%d', now()->format('YmdHis'), $unit->id);

        $receipt = DB::transaction(function () use ($authUser, $validated, $unit, $allocations, $unallocated, $receiptNumber) {
            $receipt = PaymentReceipt::create([
                'society_id' => $authUser->society_id,
                'receipt_no' => $receiptNumber,
                'user_id' => $validated['user_id'] ?? null,
                'unit_id' => $unit->id,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_mode' => $validated['payment_mode'],
                'reference_no' => $validated['reference_no'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'cleared',
            ]);

            foreach ($allocations as $allocation) {
                $bill = MaintenanceBill::query()->findOrFail($allocation['maintenance_bill_id']);

                $receipt->allocations()->create([
                    'maintenance_bill_id' => $bill->id,
                    'allocated_amount' => $allocation['allocated_amount'],
                ]);

                $updatedTotalPaid = (float) $bill->total_paid + (float) $allocation['allocated_amount'];
                $updatedClosingBalance = round(
                    (float) $bill->opening_balance
                    + (float) $bill->total_charges
                    + (float) $bill->late_fee
                    - (float) $bill->total_discount
                    - $updatedTotalPaid,
                    2
                );

                $bill->update([
                    'total_paid' => $updatedTotalPaid,
                    'closing_balance' => $updatedClosingBalance,
                    'status' => $this->resolveBillStatus($bill->due_date->isPast(), $updatedTotalPaid, $updatedClosingBalance),
                ]);
            }

            $incomeCategory = IncomeCategory::query()->firstOrCreate(
                [
                    'society_id' => $authUser->society_id,
                    'name' => 'Maintenance Collection',
                ],
                [
                    'code' => 'MAINTENANCE_COLLECTION',
                ]
            );

            IncomeEntry::create([
                'society_id' => $authUser->society_id,
                'income_category_id' => $incomeCategory->id,
                'unit_id' => $unit->id,
                'user_id' => $validated['user_id'] ?? null,
                'payment_receipt_id' => $receipt->id,
                'entry_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'title' => !empty($validated['title']) ? $validated['title'] : 'Maintenance payment received',
                'description' => $validated['notes'] ?? null,
                'visibility' => 'member',
                'created_by' => $authUser->id,
            ]);

            // If there are no open bills to allocate against (e.g. unit has only an
            // opening_balance with no bills generated yet), reduce opening_balance directly.
            $openingBalanceBefore = (float) ($unit->opening_balance ?? 0);
            $openingBalanceApplied = 0.0;
            if ($unallocated > 0 && $openingBalanceBefore > 0) {
                $openingBalanceApplied = min($unallocated, $openingBalanceBefore);
                $unit->update([
                    'opening_balance' => round($openingBalanceBefore - $openingBalanceApplied, 2),
                ]);
            }

            // Record the exact amount applied to opening_balance so reversal is accurate.
            if ($openingBalanceApplied > 0) {
                $receipt->update(['opening_balance_applied' => $openingBalanceApplied]);
            }

            return $receipt->load('allocations');
        });

        // Notify all society users about the payment
        $formattedAmount = '₹' . number_format((float) $validated['amount'], 2);
        $payer = !empty($validated['user_id']) ? User::find($validated['user_id']) : null;
        $payerName = $payer ? $payer->name : 'A resident';
        
        // Get all users in the society
        $societyUsers = User::query()
            ->where('society_id', $authUser->society_id)
            ->pluck('id')
            ->toArray();
        
        // Notify all users about the payment
        foreach ($societyUsers as $userId) {
            if ($payer && $userId === $payer->id) {
                // Send a personalized message to the payer
                NotificationService::notify(
                    $userId,
                    'Payment Received',
                    "Thank you! Your payment of {$formattedAmount} has been received successfully. Receipt No: {$receipt->receipt_no}.",
                    'payment',
                    ['receipt_no' => $receipt->receipt_no, 'amount' => $validated['amount']]
                );
            } else {
                // Send a general message to other society members
                NotificationService::notify(
                    $userId,
                    'Payment Received',
                    "Payment of {$formattedAmount} has been received from {$payerName}. Receipt No: {$receipt->receipt_no}.",
                    'payment',
                    ['receipt_no' => $receipt->receipt_no, 'amount' => $validated['amount'], 'payer' => $payerName]
                );
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment recorded successfully.',
            'payment_receipt' => $receipt,
        ], 201);
    }

    /**
     * @return array{allocations: array<int, array{maintenance_bill_id:int, allocated_amount:float}>, remaining: float}
     */
    private function prepareAllocations(array $validated, Unit $unit, float $amount): array
    {
        if (! empty($validated['allocations'])) {
            $sum = collect($validated['allocations'])->sum(fn (array $allocation) => (float) $allocation['allocated_amount']);

            if (round($sum, 2) !== round($amount, 2)) {
                throw ValidationException::withMessages([
                    'allocations' => ['Allocated amount must exactly match the payment amount.'],
                ]);
            }

            foreach ($validated['allocations'] as $allocation) {
                $bill = MaintenanceBill::query()->findOrFail($allocation['maintenance_bill_id']);

                if ($bill->society_id !== $unit->society_id || $bill->unit_id !== $unit->id) {
                    throw ValidationException::withMessages([
                        'allocations' => ['All allocations must belong to the same unit and society.'],
                    ]);
                }
            }

            return [
                'allocations' => array_map(
                    fn (array $allocation) => [
                        'maintenance_bill_id' => (int) $allocation['maintenance_bill_id'],
                        'allocated_amount' => (float) $allocation['allocated_amount'],
                    ],
                    $validated['allocations']
                ),
                'remaining' => 0.0,
            ];
        }

        $remaining = $amount;
        $autoAllocations = [];

        $openBills = MaintenanceBill::query()
            ->where('unit_id', $unit->id)
            ->where('closing_balance', '>', 0)
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        foreach ($openBills as $bill) {
            if ($remaining <= 0) {
                break;
            }

            $allocatable = min($remaining, (float) $bill->closing_balance);
            if ($allocatable <= 0) {
                continue;
            }

            $autoAllocations[] = [
                'maintenance_bill_id' => $bill->id,
                'allocated_amount' => $allocatable,
            ];
            $remaining -= $allocatable;
        }

        return ['allocations' => $autoAllocations, 'remaining' => $remaining];
    }

    public function destroy(Request $request, PaymentReceipt $payment): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureManager($authUser);
        $this->ensureSameSociety($authUser->society_id, $payment->society_id);

        $unit = Unit::query()->findOrFail($payment->unit_id);

        DB::transaction(function () use ($payment, $unit) {
            // 1. Reverse each bill allocation — add back total_paid and recalculate closing_balance.
            foreach ($payment->allocations as $allocation) {
                $bill = $allocation->maintenanceBill;
                if (! $bill) {
                    continue;
                }

                $reversedTotalPaid = max((float) $bill->total_paid - (float) $allocation->allocated_amount, 0);
                $reversedClosingBalance = round(
                    (float) $bill->opening_balance
                    + (float) $bill->total_charges
                    + (float) $bill->late_fee
                    - (float) $bill->total_discount
                    - $reversedTotalPaid,
                    2
                );

                $bill->update([
                    'total_paid' => $reversedTotalPaid,
                    'closing_balance' => $reversedClosingBalance,
                    'status' => $this->resolveBillStatus(
                        $bill->due_date->isPast(),
                        $reversedTotalPaid,
                        $reversedClosingBalance
                    ),
                ]);
            }

            // 2. Restore exactly the amount that was applied to opening_balance when the payment was recorded.
            $openingBalanceApplied = round((float) ($payment->opening_balance_applied ?? 0), 2);
            if ($openingBalanceApplied > 0) {
                $unit->update([
                    'opening_balance' => round((float) ($unit->opening_balance ?? 0) + $openingBalanceApplied, 2),
                ]);
            }

            // 3. Delete allocations, income entry linked to this receipt, and the receipt itself.
            $payment->allocations()->delete();
            $payment->incomeEntries()->delete();
            $payment->delete();
        });
        
        // Notify all society users about the payment reversal
        $formattedAmount = '₹' . number_format((float) $payment->amount, 2);
        $reverserName = $authUser->name;
        
        // Get all users in the society
        $societyUsers = User::query()
            ->where('society_id', $payment->society_id)
            ->pluck('id')
            ->toArray();
        
        // Notify all users about the payment reversal
        foreach ($societyUsers as $userId) {
            NotificationService::notify(
                $userId,
                'Payment Reversed',
                "Payment of {$formattedAmount} (Receipt No: {$payment->receipt_no}) has been reversed by {$reverserName}.",
                'payment_reversal',
                ['receipt_no' => $payment->receipt_no, 'amount' => $payment->amount, 'reversed_by' => $reverserName]
            );
        }

        return response()->json(['status' => true, 'message' => 'Payment reversed and deleted successfully.']);
    }

    private function resolveBillStatus(bool $isPastDue, float $totalPaid, float $closingBalance): string
    {
        if ($closingBalance <= 0) {
            return 'paid';
        }

        if ($totalPaid > 0) {
            return $isPastDue ? 'overdue' : 'partial';
        }

        return $isPastDue ? 'overdue' : 'unpaid';
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
