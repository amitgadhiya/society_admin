<?php

namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\MaintenanceBill;
use App\Models\MaintenancePlan;
use App\Models\PaymentAllocation;
use App\Models\PaymentReceipt;
use App\Models\Society;
use App\Models\Unit;
use App\Models\UnitMember;
use App\Models\UnitType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoMaintenanceScenarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $today = now()->startOfDay();
            $monthStart = $today->copy()->startOfMonth();
            $month = (int) $monthStart->month;
            $year = (int) $monthStart->year;

            $society = Society::query()->updateOrCreate(
                ['code' => 'DEMO-SOCIETY-001'],
                [
                    'name' => 'Orange City Demo Society',
                    'address' => 'Demo Address Line 1',
                    'city' => 'Nagpur',
                    'state' => 'Maharashtra',
                    'pincode' => '440001',
                    'billing_day' => 1,
                ],
            );

            $admin = User::query()->updateOrCreate(
                ['mobile' => '9000000001'],
                [
                    'name' => 'Demo Admin',
                    'email' => 'demo.admin@orangecity.local',
                    'password' => Hash::make('password'),
                    'society_id' => $society->id,
                    'role' => 'admin',
                    'status' => 'active',
                ],
            );

            $owner1 = User::query()->updateOrCreate(
                ['mobile' => '9000000002'],
                [
                    'name' => 'Owner 1 (1BHK)',
                    'email' => 'owner1@orangecity.local',
                    'password' => Hash::make('password'),
                    'society_id' => $society->id,
                    'role' => 'owner',
                    'status' => 'active',
                ],
            );

            $owner2 = User::query()->updateOrCreate(
                ['mobile' => '9000000003'],
                [
                    'name' => 'Owner 2 (2BHK)',
                    'email' => 'owner2@orangecity.local',
                    'password' => Hash::make('password'),
                    'society_id' => $society->id,
                    'role' => 'owner',
                    'status' => 'active',
                ],
            );

            $type1bhk = UnitType::query()->updateOrCreate(
                [
                    'society_id' => $society->id,
                    'name' => '1BHK',
                ],
                [
                    'status' => 'active',
                    'created_by' => $admin->id,
                ],
            );

            $type2bhk = UnitType::query()->updateOrCreate(
                [
                    'society_id' => $society->id,
                    'name' => '2BHK',
                ],
                [
                    'status' => 'active',
                    'created_by' => $admin->id,
                ],
            );

            $unit101 = Unit::query()->updateOrCreate(
                [
                    'society_id' => $society->id,
                    'unit_number' => 'A-101',
                ],
                [
                    'wing_id' => null,
                    'unit_type_id' => $type1bhk->id,
                    'unit_type' => '1BHK',
                    'floor' => 1,
                    'area_sqft' => 650,
                    'maintenance_scheme' => 'fixed',
                    'status' => 'active',
                ],
            );

            $unit201 = Unit::query()->updateOrCreate(
                [
                    'society_id' => $society->id,
                    'unit_number' => 'B-201',
                ],
                [
                    'wing_id' => null,
                    'unit_type_id' => $type2bhk->id,
                    'unit_type' => '2BHK',
                    'floor' => 2,
                    'area_sqft' => 950,
                    'maintenance_scheme' => 'fixed',
                    'status' => 'active',
                ],
            );

            UnitMember::query()->updateOrCreate(
                [
                    'society_id' => $society->id,
                    'unit_id' => $unit101->id,
                    'user_id' => $owner1->id,
                ],
                [
                    'member_type' => 'owner',
                    'is_primary' => true,
                    'start_date' => $monthStart->toDateString(),
                ],
            );

            UnitMember::query()->updateOrCreate(
                [
                    'society_id' => $society->id,
                    'unit_id' => $unit201->id,
                    'user_id' => $owner2->id,
                ],
                [
                    'member_type' => 'owner',
                    'is_primary' => true,
                    'start_date' => $monthStart->toDateString(),
                ],
            );

            $plan = MaintenancePlan::query()->updateOrCreate(
                [
                    'society_id' => $society->id,
                    'effective_from' => $monthStart->toDateString(),
                ],
                [
                    'mode' => 'by_unit_type',
                    'default_amount' => null,
                    'status' => 'active',
                    'created_by' => $admin->id,
                ],
            );

            $plan->rates()->updateOrCreate(
                ['unit_type' => '1BHK'],
                ['amount' => 1000],
            );

            $plan->rates()->updateOrCreate(
                ['unit_type' => '2BHK'],
                ['amount' => 2000],
            );

            $dueDate = Carbon::create($year, $month, 10)->toDateString();

            $cycle = BillingCycle::query()->updateOrCreate(
                [
                    'society_id' => $society->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'title' => $monthStart->format('F Y') . ' Maintenance',
                    'due_date' => $dueDate,
                    'status' => 'generated',
                ],
            );

            $bill1 = $this->upsertMaintenanceBill(
                $society->id,
                $cycle->id,
                $unit101,
                $monthStart->toDateString(),
                $dueDate,
                1000,
                1000,
                'paid',
            );

            $bill2 = $this->upsertMaintenanceBill(
                $society->id,
                $cycle->id,
                $unit201,
                $monthStart->toDateString(),
                $dueDate,
                2000,
                0,
                'unpaid',
            );

            $receipt = PaymentReceipt::query()->updateOrCreate(
                ['receipt_no' => sprintf('DEMO-RCP-%d%02d-001', $year, $month)],
                [
                    'society_id' => $society->id,
                    'user_id' => $owner1->id,
                    'unit_id' => $unit101->id,
                    'payment_date' => $today->toDateString(),
                    'amount' => 1000,
                    'payment_mode' => 'upi',
                    'reference_no' => 'DEMO-UPI-1000',
                    'notes' => 'Demo payment for 1BHK maintenance',
                    'status' => 'cleared',
                ],
            );

            PaymentAllocation::query()->updateOrCreate(
                [
                    'payment_receipt_id' => $receipt->id,
                    'maintenance_bill_id' => $bill1->id,
                ],
                [
                    'allocated_amount' => 1000,
                ],
            );

            $bill1->update([
                'total_paid' => 1000,
                'closing_balance' => 0,
                'status' => 'paid',
            ]);

            $bill2->update([
                'total_paid' => 0,
                'closing_balance' => 2000,
                'status' => 'unpaid',
            ]);
        });
    }

    private function upsertMaintenanceBill(
        int $societyId,
        int $billingCycleId,
        Unit $unit,
        string $billDate,
        string $dueDate,
        float $charges,
        float $totalPaid,
        string $status,
    ): MaintenanceBill {
        $closingBalance = max($charges - $totalPaid, 0);

        $bill = MaintenanceBill::query()->updateOrCreate(
            [
                'billing_cycle_id' => $billingCycleId,
                'unit_id' => $unit->id,
            ],
            [
                'society_id' => $societyId,
                'bill_no' => sprintf(
                    'DEMO-BILL-%d-%02d-%d',
                    (int) substr($billDate, 0, 4),
                    (int) substr($billDate, 5, 2),
                    $unit->id,
                ),
                'bill_date' => $billDate,
                'due_date' => $dueDate,
                'opening_balance' => 0,
                'total_charges' => $charges,
                'total_discount' => 0,
                'late_fee' => 0,
                'total_paid' => $totalPaid,
                'closing_balance' => $closingBalance,
                'status' => $status,
            ],
        );

        $bill->items()->updateOrCreate(
            [
                'charge_code' => 'MONTHLY_MAINTENANCE',
                'sort_order' => 1,
            ],
            [
                'charge_name' => 'Monthly Maintenance',
                'amount' => $charges,
            ],
        );

        return $bill;
    }
}
