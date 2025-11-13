<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;

echo "=== TESTING CERTIFICATE DOWNLOAD ===\n\n";

$certificate = Certificate::find(3);

if (!$certificate) {
    echo "Certificate not found!\n";
    exit;
}

echo "Certificate ID: {$certificate->id}\n";
echo "Serial: {$certificate->serial_number}\n";
echo "File Path: {$certificate->file_path}\n\n";

// Check if file exists
$fullPath = storage_path('app/public/' . $certificate->file_path);
echo "Full Path: $fullPath\n";
echo "File Exists: " . (file_exists($fullPath) ? "YES" : "NO") . "\n";

if (file_exists($fullPath)) {
    $fileSize = filesize($fullPath);
    echo "File Size: " . number_format($fileSize) . " bytes\n";
    
    // Check if it's a valid PDF
    $handle = fopen($fullPath, 'r');
    $header = fread($handle, 5);
    fclose($handle);
    
    echo "File Header: " . bin2hex($header) . "\n";
    echo "Is PDF: " . ($header === '%PDF-' ? "YES" : "NO") . "\n";
    
    if ($fileSize < 1000) {
        echo "\n⚠️ WARNING: File size is very small, might be corrupted\n";
        echo "File contents:\n";
        echo file_get_contents($fullPath);
        echo "\n";
    } else {
        echo "\n✅ Certificate file looks valid!\n";
    }
} else {
    echo "\n❌ Certificate file not found!\n";
}

echo "\n=== DONE ===\n";
