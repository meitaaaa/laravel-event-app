 <?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Registration;
use App\Jobs\GenerateCertificatePdfJob;

// Find registration 14
$registration = Registration::with(['user', 'event'])->find(14);

if (!$registration) {
    echo "Registration not found!\n";
    exit;
}

echo "Generating certificate for:\n";
echo "User: {$registration->user->name}\n";
echo "Event: {$registration->event->title}\n\n";

// Generate certificate synchronously
try {
    $job = new GenerateCertificatePdfJob($registration, 'random');
    $job->handle();
    
    echo "✅ Certificate generated successfully!\n";
    
    // Check result
    $cert = $registration->certificate;
    if ($cert) {
        echo "\nCertificate Details:\n";
        echo "Serial: {$cert->serial_number}\n";
        echo "File: {$cert->file_path}\n";
        echo "Download URL: " . url("/api/certificates/{$cert->id}/download") . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
