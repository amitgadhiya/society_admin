<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Society;
use App\Models\Wing;
use App\Models\Unit;
use App\Models\UnitMember;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use App\Models\IncomeEntry;
use App\Models\ExpenseEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create societies
        $society1 = Society::create([
            'name' => 'Green Valley Apartments',
            'code' => 'GVA-001',
            'address' => '123 Green Lane',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'billing_day' => 1,
        ]);

        $society2 = Society::create([
            'name' => 'Sunrise Towers',
            'code' => 'ST-002',
            'address' => '456 Sunrise Boulevard',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'billing_day' => 5,
        ]);

        // Create users for Society 1
        $admin1 = User::create([
            'name' => 'Rajesh Kumar (Admin)',
            'email' => 'admin@greenvalley.com',
            'mobile' => '9876543210',
            'password' => Hash::make('password'),
            'society_id' => $society1->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $treasurer1 = User::create([
            'name' => 'Priya Sharma (Treasurer)',
            'email' => 'treasurer@greenvalley.com',
            'mobile' => '9876543211',
            'password' => Hash::make('password'),
            'society_id' => $society1->id,
            'role' => 'treasurer',
            'status' => 'active',
        ]);

        $secretary1 = User::create([
            'name' => 'Amit Patel (Secretary)',
            'email' => 'secretary@greenvalley.com',
            'mobile' => '9876543212',
            'password' => Hash::make('password'),
            'society_id' => $society1->id,
            'role' => 'secretary',
            'status' => 'active',
        ]);

        $owner1 = User::create([
            'name' => 'Vinay Desai (Owner)',
            'email' => 'owner@greenvalley.com',
            'mobile' => '9876543213',
            'password' => Hash::make('password'),
            'society_id' => $society1->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        // Create users for Society 2
        $admin2 = User::create([
            'name' => 'Neha Singh (Admin)',
            'email' => 'admin@sunrise.com',
            'mobile' => '9876543214',
            'password' => Hash::make('password'),
            'society_id' => $society2->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $treasurer2 = User::create([
            'name' => 'Suresh Menon (Treasurer)',
            'email' => 'treasurer@sunrise.com',
            'mobile' => '9876543215',
            'password' => Hash::make('password'),
            'society_id' => $society2->id,
            'role' => 'treasurer',
            'status' => 'active',
        ]);

        // Create wings and units for Society 1
        $wingA = Wing::create([
            'society_id' => $society1->id,
            'name' => 'Wing A',
        ]);

        $wingB = Wing::create([
            'society_id' => $society1->id,
            'name' => 'Wing B',
        ]);

        $unit101 = Unit::create([
            'society_id' => $society1->id,
            'wing_id' => $wingA->id,
            'unit_number' => 'A-101',
            'floor' => 1,
            'unit_type' => '2BHK',
            'area_sqft' => 950.00,
            'maintenance_scheme' => 'percentage',
            'status' => 'active',
        ]);

        $unit102 = Unit::create([
            'society_id' => $society1->id,
            'wing_id' => $wingA->id,
            'unit_number' => 'A-102',
            'floor' => 1,
            'unit_type' => '2BHK',
            'area_sqft' => 1050.00,
            'maintenance_scheme' => 'percentage',
            'status' => 'active',
        ]);

        $unit201 = Unit::create([
            'society_id' => $society1->id,
            'wing_id' => $wingB->id,
            'unit_number' => 'B-201',
            'floor' => 2,
            'unit_type' => '3BHK',
            'area_sqft' => 900.00,
            'maintenance_scheme' => 'percentage',
            'status' => 'active',
        ]);

        // Create income categories for Society 1
        $incomeRent = IncomeCategory::create([
            'society_id' => $society1->id,
            'name' => 'Membership Fee',
            'code' => 'MF-001',
        ]);

        $incomeParking = IncomeCategory::create([
            'society_id' => $society1->id,
            'name' => 'Parking Fee',
            'code' => 'PF-001',
        ]);

        $incomeOther = IncomeCategory::create([
            'society_id' => $society1->id,
            'name' => 'Other Income',
            'code' => 'OI-001',
        ]);

        // Create expense categories for Society 1
        $expenseMaintenance = ExpenseCategory::create([
            'society_id' => $society1->id,
            'name' => 'Building Maintenance',
            'code' => 'BM-001',
        ]);

        $expenseUtilities = ExpenseCategory::create([
            'society_id' => $society1->id,
            'name' => 'Utilities',
            'code' => 'UT-001',
        ]);

        $expenseStaff = ExpenseCategory::create([
            'society_id' => $society1->id,
            'name' => 'Staff Salaries',
            'code' => 'SS-001',
        ]);

        $expenseRepairs = ExpenseCategory::create([
            'society_id' => $society1->id,
            'name' => 'Repairs & Painting',
            'code' => 'RP-001',
        ]);

        // Create vendors for Society 1
        $vendor1 = Vendor::create([
            'society_id' => $society1->id,
            'name' => 'ABC Painting Services',
            'mobile' => '9988776655',
            'service_type' => 'Painting',
            'address' => 'Mumbai, Maharashtra',
        ]);

        $vendor2 = Vendor::create([
            'society_id' => $society1->id,
            'name' => 'XYZ Plumbing Solutions',
            'mobile' => '9988776656',
            'service_type' => 'Plumbing',
            'address' => 'Mumbai, Maharashtra',
        ]);

        $vendor3 = Vendor::create([
            'society_id' => $society1->id,
            'name' => 'Bright Electrical Works',
            'mobile' => '9988776657',
            'service_type' => 'Electrical',
            'address' => 'Mumbai, Maharashtra',
        ]);

        // Create sample income entries for Society 1
        IncomeEntry::create([
            'society_id' => $society1->id,
            'income_category_id' => $incomeRent->id,
            'unit_id' => $unit101->id,
            'title' => 'February Membership Fee - A-101',
            'amount' => 5000.00,
            'entry_date' => now()->subDays(30),
            'description' => 'Monthly membership fee from Unit A-101',
            'visibility' => 'member',
            'created_by' => $treasurer1->id,
        ]);

        IncomeEntry::create([
            'society_id' => $society1->id,
            'income_category_id' => $incomeParking->id,
            'title' => 'Parking Fee - Extra Vehicle',
            'amount' => 1500.00,
            'entry_date' => now()->subDays(25),
            'description' => 'Additional parking fee for extra vehicle',
            'visibility' => 'member',
            'created_by' => $secretary1->id,
        ]);

        IncomeEntry::create([
            'society_id' => $society1->id,
            'income_category_id' => $incomeRent->id,
            'unit_id' => $unit102->id,
            'title' => 'February Membership Fee - A-102',
            'amount' => 5500.00,
            'entry_date' => now()->subDays(20),
            'description' => 'Monthly membership fee from Unit A-102',
            'visibility' => 'member',
            'created_by' => $admin1->id,
        ]);

        IncomeEntry::create([
            'society_id' => $society1->id,
            'income_category_id' => $incomeOther->id,
            'title' => 'Generator Fuel Reimbursement',
            'amount' => 2000.00,
            'entry_date' => now()->subDays(15),
            'description' => 'Received fuel reimbursement for society generator',
            'visibility' => 'admin',
            'created_by' => $treasurer1->id,
        ]);

        // Create sample expense entries for Society 1
        ExpenseEntry::create([
            'society_id' => $society1->id,
            'expense_category_id' => $expenseMaintenance->id,
            'vendor_id' => $vendor1->id,
            'title' => 'Wall Painting - Common Area',
            'amount' => 15000.00,
            'entry_date' => now()->subDays(28),
            'description' => 'Painting work for common area walls',
            'visibility' => 'member',
            'payment_mode' => 'UPI',
            'reference_no' => 'UPI-001',
            'created_by' => $secretary1->id,
        ]);

        ExpenseEntry::create([
            'society_id' => $society1->id,
            'expense_category_id' => $expenseUtilities->id,
            'title' => 'Electricity Bill',
            'amount' => 8500.00,
            'entry_date' => now()->subDays(22),
            'description' => 'Monthly electricity bill for common areas',
            'visibility' => 'member',
            'payment_mode' => 'CHEQUE',
            'reference_no' => 'CHQ-12345',
            'created_by' => $treasurer1->id,
        ]);

        ExpenseEntry::create([
            'society_id' => $society1->id,
            'expense_category_id' => $expenseStaff->id,
            'title' => 'Security Guard Salary - February',
            'amount' => 12000.00,
            'entry_date' => now()->subDays(18),
            'description' => 'Monthly salary for security personnel',
            'visibility' => 'admin',
            'payment_mode' => 'BANK_TRANSFER',
            'reference_no' => 'TRF-00789',
            'created_by' => $admin1->id,
        ]);

        ExpenseEntry::create([
            'society_id' => $society1->id,
            'expense_category_id' => $expenseRepairs->id,
            'vendor_id' => $vendor2->id,
            'title' => 'Plumbing Repairs - Wing A',
            'amount' => 7500.00,
            'entry_date' => now()->subDays(10),
            'description' => 'Pipe leakage repairs in Wing A staircase',
            'visibility' => 'member',
            'payment_mode' => 'UPI',
            'reference_no' => 'UPI-002',
            'created_by' => $secretary1->id,
        ]);

        ExpenseEntry::create([
            'society_id' => $society1->id,
            'expense_category_id' => $expenseUtilities->id,
            'title' => 'Water Supply Bill',
            'amount' => 3500.00,
            'entry_date' => now()->subDays(5),
            'description' => 'Monthly water supply charges',
            'visibility' => 'member',
            'payment_mode' => 'CHEQUE',
            'reference_no' => 'CHQ-12346',
            'created_by' => $treasurer1->id,
        ]);

        // === Society 2 Data ===
        $wingC = Wing::create([
            'society_id' => $society2->id,
            'name' => 'Tower 1',
        ]);

        $unit301 = Unit::create([
            'society_id' => $society2->id,
            'wing_id' => $wingC->id,
            'unit_number' => 'T1-301',
            'floor' => 3,
            'unit_type' => '3BHK',
            'area_sqft' => 1200.00,
            'maintenance_scheme' => 'percentage',
            'status' => 'active',
        ]);

        $unit302 = Unit::create([
            'society_id' => $society2->id,
            'wing_id' => $wingC->id,
            'unit_number' => 'T1-302',
            'floor' => 3,
            'unit_type' => '2BHK',
            'area_sqft' => 1100.00,
            'maintenance_scheme' => 'percentage',
            'status' => 'active',
        ]);

        // Create income categories for Society 2
        $income2Rent = IncomeCategory::create([
            'society_id' => $society2->id,
            'name' => 'Maintenance Charge',
            'code' => 'MC-001',
        ]);

        $income2Other = IncomeCategory::create([
            'society_id' => $society2->id,
            'name' => 'Late Fee',
            'code' => 'LF-001',
        ]);

        // Create expense categories for Society 2
        $expense2Maintenance = ExpenseCategory::create([
            'society_id' => $society2->id,
            'name' => 'Common Area Maintenance',
            'code' => 'CAM-001',
        ]);

        $expense2Security = ExpenseCategory::create([
            'society_id' => $society2->id,
            'name' => 'Security Services',
            'code' => 'SEC-001',
        ]);

        // Create vendors for Society 2
        $vendor4 = Vendor::create([
            'society_id' => $society2->id,
            'name' => 'Elite Security Services',
            'mobile' => '9988776658',
            'service_type' => 'Security',
            'address' => 'Pune, Maharashtra',
        ]);

        // Sample income entries for Society 2
        IncomeEntry::create([
            'society_id' => $society2->id,
            'income_category_id' => $income2Rent->id,
            'unit_id' => $unit301->id,
            'title' => 'Maintenance Charge - T1-301',
            'amount' => 6000.00,
            'entry_date' => now()->subDays(35),
            'description' => 'Monthly maintenance charge',
            'visibility' => 'member',
            'created_by' => $treasurer2->id,
        ]);

        IncomeEntry::create([
            'society_id' => $society2->id,
            'income_category_id' => $income2Other->id,
            'title' => 'Late Payment Fee',
            'amount' => 500.00,
            'entry_date' => now()->subDays(12),
            'description' => 'Late fee for overdue maintenance charges',
            'visibility' => 'admin',
            'created_by' => $admin2->id,
        ]);

        // Sample expense entries for Society 2
        ExpenseEntry::create([
            'society_id' => $society2->id,
            'expense_category_id' => $expense2Security->id,
            'vendor_id' => $vendor4->id,
            'title' => 'Security Services - Monthly',
            'amount' => 25000.00,
            'entry_date' => now()->subDays(30),
            'description' => 'Security personnel and CCTV monitoring',
            'visibility' => 'admin',
            'payment_mode' => 'BANK_TRANSFER',
            'reference_no' => 'TRF-01001',
            'created_by' => $admin2->id,
        ]);

        ExpenseEntry::create([
            'society_id' => $society2->id,
            'expense_category_id' => $expense2Maintenance->id,
            'title' => 'Lobby Cleaning Supplies',
            'amount' => 2500.00,
            'entry_date' => now()->subDays(8),
            'description' => 'Monthly cleaning materials and supplies',
            'visibility' => 'member',
            'payment_mode' => 'UPI',
            'reference_no' => 'UPI-003',
            'created_by' => $treasurer2->id,
        ]);
    }
}
