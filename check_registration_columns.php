<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Registration;

echo "=== REGISTRATION TABLE STRUCTURE ===\n\n";

$columns = Schema::getColumnListing('registrations');

echo "Columns in registrations table:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

echo "\n=== REGISTRATION #9 DATA ===\n";

$reg = Registration::find(9);

if ($reg) {
    echo "\nAll attributes:\n";
    foreach ($reg->getAttributes() as $key => $value) {
        echo "  {$key}: " . ($value ?? 'NULL') . "\n";
    }
} else {
    echo "Registration #9 not found!\n";
}
