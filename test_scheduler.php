<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Console\Scheduling\Schedule;

echo "=== Testing Scheduler ===\n";

try {
    // Get the Console Kernel
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    echo "Kernel class: " . get_class($kernel) . "\n";
    
    // Check if schedule method exists
    $reflection = new ReflectionClass($kernel);
    if ($reflection->hasMethod('schedule')) {
        echo "Schedule method exists: yes\n";
        
        // Try to get schedule
        $schedule = new Schedule();
        echo "Schedule object created: " . get_class($schedule) . "\n";
        
        // Check if we can add commands
        $schedule->command('otp:clean-expired')->hourly();
        echo "Command added to schedule\n";
        
    } else {
        echo "Schedule method exists: no\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== End Testing ===\n";
