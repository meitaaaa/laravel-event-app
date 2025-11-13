<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Certificate;
use App\Models\Registration;
use App\Models\Attendance;
use App\Models\User;

echo "=== CHECKING CERTIFICATES DATA ===\n\n";

// Check total certificates
$totalCerts = Certificate::count();
echo "Total Certificates: $totalCerts\n\n";

if ($totalCerts > 0) {
    echo "=== CERTIFICATE DETAILS ===\n";
    $certificates = Certificate::with(['registration.user', 'registration.event'])->get();
    
    foreach ($certificates as $cert) {
        echo "Certificate ID: {$cert->id}\n";
        echo "Serial Number: {$cert->serial_number}\n";
        echo "User: {$cert->registration->user->name} (ID: {$cert->registration->user_id})\n";
        echo "Event: {$cert->registration->event->title}\n";
        echo "File Path: {$cert->file_path}\n";
        echo "Issued At: {$cert->issued_at}\n";
        
        // Check if file exists
        $fullPath = storage_path('app/public/' . $cert->file_path);
        $fileExists = file_exists($fullPath);
        echo "File Exists: " . ($fileExists ? "YES" : "NO") . "\n";
        if ($fileExists) {
            echo "File Size: " . filesize($fullPath) . " bytes\n";
        }
        echo "---\n";
    }
} else {
    echo "No certificates found in database.\n\n";
    
    // Check registrations with attendance
    echo "=== CHECKING REGISTRATIONS WITH ATTENDANCE ===\n";
    $attendedRegs = Registration::whereHas('attendance', function($q) {
        $q->where('status', 'present');
    })->with(['user', 'event', 'attendance'])->get();
    
    echo "Total Attended Registrations: " . $attendedRegs->count() . "\n\n";
    
    if ($attendedRegs->count() > 0) {
        echo "These registrations are eligible for certificates:\n";
        foreach ($attendedRegs as $reg) {
            echo "- Registration ID: {$reg->id}\n";
            echo "  User: {$reg->user->name} (ID: {$reg->user_id})\n";
            echo "  Event: {$reg->event->title}\n";
            echo "  Attended At: {$reg->attendance->attended_at}\n";
            echo "  Has Certificate: " . ($reg->certificate ? "YES" : "NO") . "\n";
            echo "\n";
        }
    }
}

// Check users
echo "\n=== USERS IN DATABASE ===\n";
$users = User::all();
foreach ($users as $user) {
    echo "User ID: {$user->id} - {$user->name} ({$user->email})\n";
    $userCerts = Certificate::whereHas('registration', function($q) use ($user) {
        $q->where('user_id', $user->id);
    })->count();
    echo "  Certificates: $userCerts\n";
}

echo "\n=== DONE ===\n";
