<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\IncomeEntry;
use App\Models\ExpenseEntry;

echo "\n📊 CHECKING CREATED_BY RELATIONSHIPS:\n\n";

echo "Income Entries:\n";
$incomes = IncomeEntry::all();
foreach ($incomes as $entry) {
    echo "  - {$entry->title}\n";
    echo "    created_by ID: {$entry->created_by}\n";
    echo "    Creator: " . ($entry->creator?->name ?? 'NULL') . "\n";
}

echo "\nExpense Entries:\n";
$expenses = ExpenseEntry::all();
foreach ($expenses as $entry) {
    echo "  - {$entry->title}\n";
    echo "    created_by ID: {$entry->created_by}\n";
    echo "    Creator: " . ($entry->creator?->name ?? 'NULL') . "\n";
}
