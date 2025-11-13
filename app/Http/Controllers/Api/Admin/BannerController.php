<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    /**
     * Get all banners (admin)
     */
    public function index()
    {
        try {
            $banners = Banner::ordered()->get();
            
            return response()->json([
                'success' => true,
                'data' => $banners
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch banners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single banner
     */
    public function show(Banner $banner)
    {
        return response()->json([
            'success' => true,
            'data' => $banner
        ]);
    }

    /**
     * Create new banner
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
                'button_text' => 'nullable|string|max:100',
                'button_link' => 'nullable|string|max:500',
                'order' => 'nullable|integer',
                'is_active' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('banners', $imageName, 'public');
            }

            $banner = Banner::create([
                'title' => $request->title,
                'description' => $request->description,
                'image_path' => $imagePath,
                'button_text' => $request->button_text,
                'button_link' => $request->button_link,
                'order' => $request->order ?? 0,
                'is_active' => $request->is_active ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Banner created successfully',
                'data' => $banner
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update banner
     */
    public function update(Request $request, Banner $banner)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'button_text' => 'nullable|string|max:100',
                'button_link' => 'nullable|string|max:500',
                'order' => 'nullable|integer',
                'is_active' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                    Storage::disk('public')->delete($banner->image_path);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('banners', $imageName, 'public');
                $banner->image_path = $imagePath;
            }

            // Update other fields
            // Convert empty strings and "null" strings to null for nullable fields
            // Also trim whitespace to prevent issues
            if ($request->has('title')) {
                $title = trim($request->title);
                $banner->title = ($title === '' || $title === 'null' || $title === null) ? null : $title;
            }
            if ($request->has('description')) {
                $description = trim($request->description);
                $banner->description = ($description === '' || $description === 'null' || $description === null) ? null : $description;
            }
            if ($request->has('button_text')) {
                $buttonText = trim($request->button_text);
                $banner->button_text = ($buttonText === '' || $buttonText === 'null' || $buttonText === null) ? null : $buttonText;
            }
            if ($request->has('button_link')) {
                $buttonLink = trim($request->button_link);
                $banner->button_link = ($buttonLink === '' || $buttonLink === 'null' || $buttonLink === null) ? null : $buttonLink;
            }
            // Ensure order is always saved as integer (even if 0)
            if ($request->has('order')) {
                $banner->order = (int) $request->order;
            }
            if ($request->has('is_active')) {
                // Handle both string and boolean values
                $isActive = $request->is_active;
                if (is_string($isActive)) {
                    $banner->is_active = in_array(strtolower($isActive), ['1', 'true', 'yes', 'on']);
                } else {
                    $banner->is_active = (bool) $isActive;
                }
            }

            $banner->save();
            
            // Refresh the model to ensure all attributes are up to date
            $banner->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Banner updated successfully',
                'data' => $banner
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete banner
     */
    public function destroy(Banner $banner)
    {
        try {
            // Delete image file
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }

            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle banner active status
     */
    public function toggleActive(Banner $banner)
    {
        try {
            $banner->is_active = !$banner->is_active;
            $banner->save();

            return response()->json([
                'success' => true,
                'message' => 'Banner status updated',
                'data' => $banner
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle banner status: ' . $e->getMessage()
            ], 500);
        }
    }
}
