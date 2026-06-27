<?php

namespace App\Console\Commands;

use App\Models\BillingCycle;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceBill;
use App\Models\Society;
use App\Models\Unit;
use App\Services\MaintenanceBillingService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyMaintenanceBills extends Command
{
    protected $signature = 'maintenance:generate-monthly
                            {--date= : Run for a specific date (YYYY-MM-DD)}
                            {--society= : Society ID to target (bypasses billing_day filter)}
                            {--last-month : Use last month instead of current date}';

    protected $description = 'Generate monthly maintenance billing cycles and dues for societies whose billing day matches the run date.';

    public function __construct(private readonly MaintenanceBillingService $billingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // Resolve run date
        if ($this->option('date')) {
            $runDate = Carbon::parse((string) $this->option('date'))->startOfDay();
        } elseif ($this->option('last-month')) {
            $runDate = now()->subMonthNoOverflow()->startOfDay();
        } else {
            $runDate = now()->startOfDay();
        }

        $societyId = $this->option('society');

        // When a specific society is requested, skip the billing_day filter.
        if ($societyId) {
            $societies = Society::query()->where('id', $societyId)->get();
            if ($societies->isEmpty()) {
                $this->error("Society with ID {$societyId} not found.");
                return self::FAILURE;
            }
            $this->info(sprintf('Manual run for society ID %s using date %s.', $societyId, $runDate->toDateString()));
        } else {
            $societies = Society::query()
                ->where('billing_day', $runDate->day)
                ->get();

            if ($societies->isEmpty()) {
                $this->info(sprintf('No societies scheduled for billing on %s.', $runDate->toDateString()));
                return self::SUCCESS;
            }
        }

        foreach ($societies as $society) {
            $this->line('');
            $this->info("Processing {$society->name}...");

            $billingCycle = BillingCycle::query()->firstOrCreate(
                [
                    'society_id' => $society->id,
                    'month' => $runDate->month,
                    'year' => $runDate->year,
                ],
                [
                    'title' => $runDate->format('F Y') . ' Maintenance',
                    'due_date' => $this->resolveDueDate($society->id, $runDate)->toDateString(),
                    'status' => 'generated',
                ],
            );

            if ($billingCycle->status !== 'generated') {
                $billingCycle->update(['status' => 'generated']);
            }

            $units = Unit::query()
                ->with(['unitType', 'members.user'])
                ->where('society_id', $society->id)
                ->where('status', 'active')
                ->get();

            if ($units->isEmpty()) {
                $this->warn('Skipped: no active units found.');
                continue;
            }

            $generatedCount = 0;
            $skippedCount = 0;
            $plan = $this->resolvePlan($society->id, $runDate);

            if ($plan) {
                if ($plan->mode === 'same_for_all') {
                    $result = $this->billingService->generateBills(
                        $billingCycle,
                        $units,
                        [[
                            'charge_name' => 'Monthly Maintenance',
                            'charge_code' => 'MONTHLY_MAINTENANCE',
                            'amount' => (float) $plan->default_amount,
                        ]],
                        $runDate->toDateString(),
                        $billingCycle->due_date->toDateString(),
                    );

                    $generatedCount += count($result['generatedBills']);
                    $skippedCount += $result['skippedCount'];
                } else {
                    $rateMap = $plan->rates
                        ->mapWithKeys(fn ($rate) => [
                            strtolower(trim((string) $rate->unit_type)) => (float) $rate->amount,
                        ]);

                    foreach ($units as $unit) {
                        $resolvedType = $unit->unitType?->name ?? $unit->unit_type;
                        $unitTypeKey = strtolower(trim((string) ($resolvedType ?? '')));
                        $amount = $rateMap[$unitTypeKey] ?? null;

                        if ($amount === null) {
                            $this->warn("Skipped unit {$unit->unit_number}: no maintenance rate for unit type '{$resolvedType}'.");
                            continue;
                        }

                        $result = $this->billingService->generateBills(
                            $billingCycle,
                            collect([$unit]),
                            [[
                                'charge_name' => 'Monthly Maintenance',
                                'charge_code' => 'MONTHLY_MAINTENANCE',
                                'amount' => $amount,
                            ]],
                            $runDate->toDateString(),
                            $billingCycle->due_date->toDateString(),
                        );

                        $generatedCount += count($result['generatedBills']);
                        $skippedCount += $result['skippedCount'];
                    }
                }
            } else {
                $templateItems = $this->resolveTemplateChargeItems($society->id);
                if ($templateItems === []) {
                    $this->warn('Skipped: no maintenance plan and no previous bill template found.');
                    continue;
                }

                $result = $this->billingService->generateBills(
                    $billingCycle,
                    $units,
                    $templateItems,
                    $runDate->toDateString(),
                    $billingCycle->due_date->toDateString(),
                );

                $generatedCount += count($result['generatedBills']);
                $skippedCount += $result['skippedCount'];
            }

            $this->info("Cycle: {$billingCycle->title}");
            $this->line("Generated bills: {$generatedCount}");
            $this->line("Skipped existing: {$skippedCount}");

            // Notify all members of units that got a new bill.
            if ($generatedCount > 0) {
                $notifiedUserIds = [];
                foreach ($units as $unit) {
                    foreach ($unit->members as $member) {
                        $user = $member->user;
                        if (! $user || in_array($user->id, $notifiedUserIds, true)) {
                            continue;
                        }
                        $notifiedUserIds[] = $user->id;
                        NotificationService::notify(
                            $user->id,
                            'Maintenance Bill Generated',
                            "Your maintenance bill for {$billingCycle->title} has been generated. Please pay before the due date.",
                            'maintenance'
                        );
                    }
                }
                $this->line('Notifications sent to ' . count($notifiedUserIds) . ' member(s).');
            }
        }

        $this->line('');
        $this->info('Monthly maintenance generation completed.');

        return self::SUCCESS;
    }

    private function resolvePlan(int $societyId, Carbon $runDate): ?MaintenancePlan
    {
        return MaintenancePlan::query()
            ->with('rates')
            ->where('society_id', $societyId)
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', $runDate->toDateString())
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array<int, array{charge_name:string, charge_code:?string, amount:string}>
     */
    private function resolveTemplateChargeItems(int $societyId): array
    {
        $bill = MaintenanceBill::query()
            ->with('items')
            ->where('society_id', $societyId)
            ->whereHas('items')
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->first();

        if (! $bill) {
            return [];
        }

        return $bill->items
            ->sortBy('sort_order')
            ->map(fn ($item) => [
                'charge_name' => $item->charge_name,
                'charge_code' => $item->charge_code,
                'amount' => $item->amount,
            ])
            ->values()
            ->all();
    }

    private function resolveDueDate(int $societyId, Carbon $runDate): Carbon
    {
        $previousCycle = BillingCycle::query()
            ->where('society_id', $societyId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->first();

        if (! $previousCycle) {
            return $runDate->copy();
        }

        $dueDay = min($previousCycle->due_date->day, $runDate->copy()->endOfMonth()->day);

        return $runDate->copy()->day($dueDay);
    }
}
