<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Society;
use App\Models\User;
use App\Models\Wing;
use App\Models\Unit;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use App\Models\IncomeEntry;
use App\Models\ExpenseEntry;

echo "\n================== DATABASE INTEGRITY CHECK ==================\n\n";

// 1. SOCIETIES
echo "1️⃣  SOCIETIES (" . Society::count() . " records)\n";
foreach (Society::all() as $society) {
    echo "   ├─ " . $society->name . " (ID: {$society->id}, Code: {$society->code})\n";
}
echo "\n";

// 2. USERS
echo "2️⃣  USERS (" . User::count() . " records)\n";
foreach (Society::all() as $society) {
    echo "   ├─ " . $society->name . ": " . $society->users()->count() . " users\n";
    foreach ($society->users as $user) {
        echo "   │  ├─ {$user->name} [{{$user->role}}] (ID: {$user->id})\n";
    }
}
echo "\n";

// 3. WINGS & UNITS
echo "3️⃣  WINGS & UNITS\n";
foreach (Society::all() as $society) {
    echo "   ├─ " . $society->name . ": " . $society->wings()->count() . " wings, " . $society->units()->count() . " units\n";
    foreach ($society->wings as $wing) {
        echo "   │  ├─ {$wing->name}: " . $wing->units()->count() . " units\n";
        foreach ($wing->units as $unit) {
            echo "   │  │  ├─ {$unit->unit_number} (ID: {$unit->id}, Type: {$unit->unit_type})\n";
        }
    }
}
echo "\n";

// 4. INCOME CATEGORIES
echo "4️⃣  INCOME CATEGORIES\n";
foreach (Society::all() as $society) {
    echo "   ├─ " . $society->name . ": " . $society->incomeCategories()->count() . " categories\n";
    foreach ($society->incomeCategories as $cat) {
        echo "   │  ├─ {$cat->name} (Code: {$cat->code})\n";
    }
}
echo "\n";

// 5. EXPENSE CATEGORIES
echo "5️⃣  EXPENSE CATEGORIES\n";
foreach (Society::all() as $society) {
    echo "   ├─ " . $society->name . ": " . $society->expenseCategories()->count() . " categories\n";
    foreach ($society->expenseCategories as $cat) {
        echo "   │  ├─ {$cat->name} (Code: {$cat->code})\n";
    }
}
echo "\n";

// 6. VENDORS
echo "6️⃣  VENDORS\n";
foreach (Society::all() as $society) {
    echo "   ├─ " . $society->name . ": " . $society->vendors()->count() . " vendors\n";
    foreach ($society->vendors as $vendor) {
        echo "   │  ├─ {$vendor->name} (Type: {$vendor->service_type})\n";
    }
}
echo "\n";

// 7. INCOME ENTRIES
echo "7️⃣  INCOME ENTRIES (" . IncomeEntry::count() . " total)\n";
foreach (Society::all() as $society) {
    echo "   ├─ " . $society->name . ": " . $society->incomeEntries()->count() . " entries\n";
    foreach ($society->incomeEntries->take(2) as $entry) {
        $cat = $entry->category?->name ?? 'N/A';
        $unit = $entry->unit?->unit_number ?? 'N/A';
        $creator = $entry->creator?->name ?? 'N/A';
        echo "   │  ├─ [{$cat}] {$entry->title}\n";
        echo "   │  │  Amount: ₹{$entry->amount}, Unit: {$unit}, Created By: {$creator}\n";
    }
}
echo "\n";

// 8. EXPENSE ENTRIES
echo "8️⃣  EXPENSE ENTRIES (" . ExpenseEntry::count() . " total)\n";
foreach (Society::all() as $society) {
    echo "   ├─ " . $society->name . ": " . $society->expenseEntries()->count() . " entries\n";
    foreach ($society->expenseEntries->take(2) as $entry) {
        $cat = $entry->category?->name ?? 'N/A';
        $vendor = $entry->vendor?->name ?? 'N/A';
        $creator = $entry->creator?->name ?? 'N/A';
        echo "   │  ├─ [{$cat}] {$entry->title}\n";
        echo "   │  │  Amount: ₹{$entry->amount}, Vendor: {$vendor}, Created By: {$creator}\n";
    }
}
echo "\n";

// 9. RELATIONSHIP VALIDATION
echo "9️⃣  RELATIONSHIP VALIDATION\n";
$issues = [];

// Check Users have Society
foreach (User::all() as $user) {
    if (!$user->society_id) {
        $issues[] = "User '{$user->name}' has no society_id";
    }
}

// Check Units have Society
foreach (Unit::all() as $unit) {
    if (!$unit->society_id) {
        $issues[] = "Unit '{$unit->unit_number}' has no society_id";
    }
}

// Check Income Entries have Category, Society, and Creator
foreach (IncomeEntry::all() as $entry) {
    if (!$entry->society_id) {
        $issues[] = "IncomeEntry '{$entry->title}' has no society_id";
    }
    if (!$entry->income_category_id) {
        $issues[] = "IncomeEntry '{$entry->title}' has no income_category_id";
    }
    if (!$entry->created_by) {
        $issues[] = "IncomeEntry '{$entry->title}' has no created_by user";
    }
    if (!$entry->creator) {
        $issues[] = "IncomeEntry '{$entry->title}' creator relationship is broken (invalid user ID)";
    }
}

// Check Expense Entries have Category, Society, and Creator
foreach (ExpenseEntry::all() as $entry) {
    if (!$entry->society_id) {
        $issues[] = "ExpenseEntry '{$entry->title}' has no society_id";
    }
    if (!$entry->expense_category_id) {
        $issues[] = "ExpenseEntry '{$entry->title}' has no expense_category_id";
    }
    if (!$entry->created_by) {
        $issues[] = "ExpenseEntry '{$entry->title}' has no created_by user";
    }
    if (!$entry->creator) {
        $issues[] = "ExpenseEntry '{$entry->title}' creator relationship is broken (invalid user ID)";
    }
}

// Check Categories have Society
foreach (IncomeCategory::all() as $cat) {
    if (!$cat->society_id) {
        $issues[] = "IncomeCategory '{$cat->name}' has no society_id";
    }
}

foreach (ExpenseCategory::all() as $cat) {
    if (!$cat->society_id) {
        $issues[] = "ExpenseCategory '{$cat->name}' has no society_id";
    }
}

// Check Vendors have Society
foreach (Vendor::all() as $vendor) {
    if (!$vendor->society_id) {
        $issues[] = "Vendor '{$vendor->name}' has no society_id";
    }
}

if (count($issues) === 0) {
    echo "   ✅ ALL RELATIONSHIPS ARE VALID\n";
    echo "   No data integrity issues found!\n";
} else {
    echo "   ⚠️  ISSUES FOUND (" . count($issues) . "):\n";
    foreach ($issues as $issue) {
        echo "   │  ├─ $issue\n";
    }
}

echo "\n================== CHECK COMPLETE ==================\n\n";
