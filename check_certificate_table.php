<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Certificate;

echo "=== CERTIFICATE TABLE STRUCTURE ===\n\n";

$columns = Schema::getColumnListing('certificates');

echo "Columns in certificates table:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

echo "\n=== CERTIFICATE RECORD #5 ===\n";

$cert = Certificate::find(5);

if ($cert) {
    echo "ID: {$cert->id}\n";
    echo "Registration ID: {$cert->registration_id}\n";
    
    // Try different possible field names
    $possibleFields = ['serial_number', 'certificate_number', 'file_path', 'pdf_path', 'path', 'issued_at', 'generated_at', 'created_at'];
    
    foreach ($possibleFields as $field) {
        if (isset($cert->$field)) {
            echo "{$field}: " . ($cert->$field ?? 'NULL') . "\n";
        }
    }
    
    echo "\nRaw attributes:\n";
    print_r($cert->getAttributes());
} else {
    echo "Certificate #5 not found!\n";
}
