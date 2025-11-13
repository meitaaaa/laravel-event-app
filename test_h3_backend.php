<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;

echo "\n=== TEST H-3 VALIDATION (BACKEND) ===\n\n";

$today = Carbon::today();
echo "Today: " . $today->format('d/m/Y') . " (" . $today->format('Y-m-d') . ")\n\n";

$testCases = [
    ['date' => $today->copy()->addDays(0), 'label' => 'H-0 (Hari ini)'],
    ['date' => $today->copy()->addDays(1), 'label' => 'H-1'],
    ['date' => $today->copy()->addDays(2), 'label' => 'H-2'],
    ['date' => $today->copy()->addDays(3), 'label' => 'H-3'],
    ['date' => $today->copy()->addDays(4), 'label' => 'H-4'],
    ['date' => $today->copy()->addDays(7), 'label' => 'H-7'],
];

echo str_repeat("─", 80) . "\n";
printf("%-15s %-15s %-10s %-10s %-10s\n", "Label", "Date", "diffDays", "Expected", "Result");
echo str_repeat("─", 80) . "\n";

foreach ($testCases as $test) {
    $eventDate = $test['date']->startOfDay();
    $diffDays = $today->diffInDays($eventDate, false);
    $isValid = $diffDays >= 3;
    $expected = in_array($test['label'], ['H-3', 'H-4', 'H-7']) ? 'VALID' : 'INVALID';
    $result = $isValid ? '✅ VALID' : '❌ INVALID';
    $match = ($isValid && $expected === 'VALID') || (!$isValid && $expected === 'INVALID');
    
    printf(
        "%-15s %-15s %-10d %-10s %-10s %s\n",
        $test['label'],
        $eventDate->format('d/m/Y'),
        $diffDays,
        $expected,
        $result,
        $match ? '✅ PASS' : '❌ FAIL'
    );
}

echo str_repeat("─", 80) . "\n\n";

// Test specific case: 09/11/2025
echo "=== SPECIFIC TEST: 09/11/2025 ===\n\n";
$eventDate = Carbon::parse('2025-11-09')->startOfDay();
$diffDays = $today->diffInDays($eventDate, false);
$isValid = $diffDays >= 3;

echo "Today: " . $today->format('d/m/Y') . "\n";
echo "Event Date: " . $eventDate->format('d/m/Y') . "\n";
echo "Diff Days: " . $diffDays . "\n";
echo "Is Valid (>= 3): " . ($isValid ? 'YES ✅' : 'NO ❌') . "\n";
echo "\n";

if ($isValid) {
    echo "✅ Event tanggal 09/11/2025 VALID untuk dibuat hari ini!\n";
} else {
    echo "❌ Event tanggal 09/11/2025 INVALID. Minimal tanggal: " . $today->copy()->addDays(3)->format('d/m/Y') . "\n";
}

echo "\n";
