<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;

class BannerController extends Controller
{
    /**
     * Get active banners for public display
     */
    public function index()
    {
        try {
            $banners = Banner::active()
                ->ordered()
                ->get();
            
            // Debug logging
            \Log::info('Fetching public banners', [
                'total_banners' => $banners->count(),
                'banners' => $banners->toArray()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $banners
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching banners: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch banners: ' . $e->getMessage()
            ], 500);
        }
    }
}
