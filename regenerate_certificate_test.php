<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Models\Certificate;
use App\Jobs\GenerateCertificatePdfJob;

echo "=== REGENERATE CERTIFICATE WITH NEW DESIGN ===\n\n";

// Delete old certificate
$oldCert = Certificate::where('registration_id', 9)->first();
if ($oldCert) {
    echo "Deleting old certificate #" . $oldCert->id . "...\n";
    
    // Delete file
    $filePath = storage_path('app/public/' . $oldCert->file_path);
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "  ✅ Old PDF file deleted\n";
    }
    
    // Delete record
    $oldCert->delete();
    echo "  ✅ Old certificate record deleted\n\n";
}

// Get registration
$reg = Registration::with(['event', 'user'])->find(9);

if (!$reg) {
    echo "❌ Registration #9 not found!\n";
    exit;
}

echo "Generating new certificate for:\n";
echo "  Registration ID: {$reg->id}\n";
echo "  User: {$reg->user->name}\n";
echo "  Event: {$reg->event->title}\n\n";

try {
    // Generate certificate synchronously
    GenerateCertificatePdfJob::dispatchSync($reg, 'random');
    
    echo "✅ Certificate generation job completed!\n\n";
    
    // Verify new certificate
    $newCert = Certificate::where('registration_id', 9)->first();
    
    if ($newCert) {
        echo "NEW CERTIFICATE DETAILS:\n";
        echo "  ID: {$newCert->id}\n";
        echo "  Serial: {$newCert->serial_number}\n";
        echo "  File: {$newCert->file_path}\n";
        echo "  Issued: {$newCert->issued_at}\n";
        
        $filePath = storage_path('app/public/' . $newCert->file_path);
        if (file_exists($filePath)) {
            $fileSize = filesize($filePath);
            echo "  File Size: " . number_format($fileSize) . " bytes\n";
            echo "\n✅ Certificate PDF exists!\n";
            echo "\nDownload URL:\n";
            echo "http://127.0.0.1:8000/storage/{$newCert->file_path}\n";
        } else {
            echo "\n❌ Certificate PDF not found!\n";
        }
    } else {
        echo "❌ Certificate record not created!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
