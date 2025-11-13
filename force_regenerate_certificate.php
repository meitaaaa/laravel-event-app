<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;

echo "=== FORCE REGENERATE CERTIFICATE ===\n\n";

$certId = 3;
$cert = Certificate::find($certId);

if (!$cert) {
    echo "Certificate not found!\n";
    exit;
}

echo "Certificate ID: {$cert->id}\n";
echo "Serial: {$cert->serial_number}\n";
echo "File Path: {$cert->file_path}\n\n";

// Delete old file
$fullPath = storage_path('app/public/' . $cert->file_path);
if (file_exists($fullPath)) {
    echo "Deleting old file: $fullPath\n";
    unlink($fullPath);
    echo "✅ Old file deleted\n\n";
} else {
    echo "⚠️ Old file not found\n\n";
}

// Force regenerate by calling download
echo "Regenerating certificate...\n";

$registration = $cert->registration()->with(['user','event'])->first();

echo "User: {$registration->user->name}\n";
echo "Event: {$registration->event->title}\n";
echo "Has custom template: " . ($registration->event->certificate_template_path ? 'YES' : 'NO') . "\n\n";

if ($registration->event->certificate_template_path) {
    $templatePath = storage_path('app/public/' . $registration->event->certificate_template_path);
    echo "Template path: $templatePath\n";
    echo "Template exists: " . (file_exists($templatePath) ? 'YES' : 'NO') . "\n\n";
}

// Generate new PDF
$controller = new \App\Http\Controllers\Api\CertificateController();
$response = $controller->download($cert);

echo "✅ Certificate regenerated!\n\n";

// Verify new file
if (file_exists($fullPath)) {
    $fileSize = filesize($fullPath);
    echo "New file created: $fullPath\n";
    echo "File size: " . number_format($fileSize) . " bytes\n";
    
    // Check PDF header
    $handle = fopen($fullPath, 'r');
    $header = fread($handle, 4);
    fclose($handle);
    
    if ($header === '%PDF') {
        echo "✅ Valid PDF file\n";
    } else {
        echo "❌ Invalid PDF file\n";
    }
} else {
    echo "❌ New file not created!\n";
}

echo "\n=== DONE ===\n";
echo "\nNow download from browser:\n";
echo "http://localhost:3000/profile?section=certificates\n";
echo "Or direct: http://127.0.0.1:8000/api/certificates/3/download\n";
