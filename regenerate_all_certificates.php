<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;
use App\Models\Registration;
use App\Jobs\GenerateCertificatePdfJob;
use Illuminate\Support\Facades\Storage;

echo "\n=== REGENERATE ALL CERTIFICATES WITH NEW TEMPLATE ===\n\n";

// Get all certificates
$certificates = Certificate::with(['registration.user', 'registration.event'])->get();

if ($certificates->isEmpty()) {
    echo "❌ No certificates found in database.\n";
    exit;
}

echo "Found " . $certificates->count() . " certificates to regenerate.\n\n";

$success = 0;
$failed = 0;

foreach ($certificates as $cert) {
    $registration = $cert->registration;
    
    if (!$registration || !$registration->user || !$registration->event) {
        echo "⚠️  Certificate #{$cert->id} - Missing registration/user/event data. SKIPPED.\n";
        $failed++;
        continue;
    }
    
    echo "Processing Certificate #{$cert->id}:\n";
    echo "  User: {$registration->user->name}\n";
    echo "  Event: {$registration->event->title}\n";
    echo "  Serial: {$cert->serial_number}\n";
    
    try {
        // Delete old PDF file if exists
        if ($cert->file_path && Storage::disk('public')->exists($cert->file_path)) {
            Storage::disk('public')->delete($cert->file_path);
            echo "  ✅ Old PDF deleted\n";
        }
        
        // Delete certificate record
        $oldId = $cert->id;
        $cert->delete();
        echo "  ✅ Old certificate record deleted\n";
        
        // Generate new certificate with new template
        GenerateCertificatePdfJob::dispatchSync($registration, 'random');
        
        // Get the newly created certificate
        $newCert = Certificate::where('registration_id', $registration->id)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($newCert && Storage::disk('public')->exists($newCert->file_path)) {
            $fileSize = Storage::disk('public')->size($newCert->file_path);
            echo "  ✅ New certificate generated!\n";
            echo "     New ID: {$newCert->id}\n";
            echo "     Serial: {$newCert->serial_number}\n";
            echo "     File: {$newCert->file_path}\n";
            echo "     Size: " . number_format($fileSize) . " bytes\n";
            $success++;
        } else {
            echo "  ❌ Failed to generate new certificate\n";
            $failed++;
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total certificates: " . $certificates->count() . "\n";
echo "✅ Successfully regenerated: {$success}\n";
echo "❌ Failed: {$failed}\n";

if ($success > 0) {
    echo "\n🎉 All certificates have been regenerated with the NEW TEMPLATE!\n";
    echo "Users can now download the updated certificates.\n";
}

echo "\n";
