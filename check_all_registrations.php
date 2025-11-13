<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;

echo "\n=== CHECK ALL REGISTRATIONS ===\n\n";

$registrations = Registration::with(['event', 'certificate', 'user'])->get();

echo "Total registrations: " . $registrations->count() . "\n\n";

$withCert = 0;
$withoutCert = 0;

foreach ($registrations as $reg) {
    if ($reg->certificate) {
        $withCert++;
    } else {
        $withoutCert++;
        echo "⚠️  Registration #{$reg->id} - {$reg->user->name} - {$reg->event->title} - NO CERTIFICATE\n";
    }
}

echo "\n";
echo "✅ With certificate: {$withCert}\n";
echo "❌ Without certificate: {$withoutCert}\n";

if ($withoutCert > 0) {
    echo "\n⚠️  WARNING: Some registrations don't have certificates!\n";
    echo "This might cause issues in the API response.\n";
}

echo "\n";
