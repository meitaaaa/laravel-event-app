<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;
use App\Http\Controllers\Api\CertificateController;
use Illuminate\Http\Request;

echo "=== TESTING DIRECT DOWNLOAD ===\n\n";

$cert = Certificate::find(3);

if (!$cert) {
    echo "Certificate not found!\n";
    exit;
}

echo "Certificate ID: {$cert->id}\n";
echo "Serial: {$cert->serial_number}\n";
echo "File Path: {$cert->file_path}\n\n";

// Test download controller
try {
    $controller = new CertificateController();
    $response = $controller->download($cert);
    
    echo "✅ Download controller works!\n";
    echo "Response type: " . get_class($response) . "\n";
    echo "Status: " . $response->getStatusCode() . "\n";
    
    $headers = $response->headers->all();
    echo "\nHeaders:\n";
    foreach ($headers as $key => $value) {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        echo "  $key: $value\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DONE ===\n";
