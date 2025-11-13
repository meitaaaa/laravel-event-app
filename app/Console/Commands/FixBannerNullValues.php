<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Banner;

class FixBannerNullValues extends Command
{
    protected $signature = 'banners:fix-null';
    protected $description = 'Fix banner fields that contain string "null" instead of actual NULL';

    public function handle()
    {
        $this->info('Fixing banner null values...');
        
        $count = 0;
        $banners = Banner::all();
        
        foreach ($banners as $banner) {
            $updated = false;
            
            // Fix title
            if ($banner->title === 'null' || $banner->title === '' || trim($banner->title) === '') {
                $banner->title = null;
                $updated = true;
            }
            
            // Fix description
            if ($banner->description === 'null' || $banner->description === '' || trim($banner->description) === '') {
                $banner->description = null;
                $updated = true;
            }
            
            // Fix button_text
            if ($banner->button_text === 'null' || $banner->button_text === '' || trim($banner->button_text) === '') {
                $banner->button_text = null;
                $updated = true;
            }
            
            // Fix button_link
            if ($banner->button_link === 'null' || $banner->button_link === '' || trim($banner->button_link) === '') {
                $banner->button_link = null;
                $updated = true;
            }
            
            if ($updated) {
                $banner->save();
                $count++;
                $this->line("Fixed banner ID: {$banner->id}");
            }
        }
        
        $this->info("✅ Fixed {$count} banner(s)");
        
        return 0;
    }
}
