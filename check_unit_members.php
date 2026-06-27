<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UnitMember;

echo "Unit Members in Database: " . UnitMember::count() . "\n\n";

if (UnitMember::count() > 0) {
    foreach (UnitMember::all() as $member) {
        echo "Unit: {$member->unit?->unit_number} - User: {$member->user?->name} ({$member->member_type})\n";
    }
} else {
    echo "No unit members recorded yet.\n";
    echo "This table is empty because members are typically added via the API\n";
    echo "when a unit is created or when a new occupant is assigned.\n";
}
