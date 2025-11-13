<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Banner;

echo "=== Testing Banner Model ===\n\n";

// Get all banners
$allBanners = Banner::all();
echo "Total banners in database: " . $allBanners->count() . "\n\n";

foreach ($allBanners as $banner) {
    echo "Banner ID: {$banner->id}\n";
    echo "Title: {$banner->title}\n";
    echo "Image Path: {$banner->image_path}\n";
    echo "Image URL: {$banner->image_url}\n";
    echo "Is Active: " . ($banner->is_active ? 'Yes' : 'No') . "\n";
    echo "Order: {$banner->order}\n";
    echo "---\n\n";
}

// Get only active banners
$activeBanners = Banner::active()->ordered()->get();
echo "Active banners: " . $activeBanners->count() . "\n\n";

foreach ($activeBanners as $banner) {
    echo "Active Banner ID: {$banner->id}\n";
    echo "Title: {$banner->title}\n";
    echo "Image URL: {$banner->image_url}\n";
    echo "---\n\n";
}

// Check if storage files exist
echo "=== Checking Storage Files ===\n\n";
$storagePath = storage_path('app/public/banners');
echo "Storage path: {$storagePath}\n";
echo "Directory exists: " . (is_dir($storagePath) ? 'Yes' : 'No') . "\n\n";

if (is_dir($storagePath)) {
    $files = scandir($storagePath);
    echo "Files in banners directory:\n";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "- {$file}\n";
        }
    }
}
