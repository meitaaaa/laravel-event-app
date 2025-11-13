<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index()
    {
        $user = Auth::user();
        
        $wishlists = Wishlist::where('user_id', $user->id)
            ->with(['event' => function ($query) {
                $query->where('is_published', true);
            }])
            ->latest()
            ->get();

        // Filter out wishlists where event is null (unpublished or deleted)
        $wishlists = $wishlists->filter(function ($wishlist) {
            return $wishlist->event !== null;
        });

        return response()->json([
            'success' => true,
            'data' => $wishlists->map(function ($wishlist) {
                return [
                    'id' => $wishlist->id,
                    'event' => [
                        'id' => $wishlist->event->id,
                        'title' => $wishlist->event->title,
                        'description' => $wishlist->event->description,
                        'event_date' => $wishlist->event->event_date,
                        'start_time' => $wishlist->event->start_time,
                        'end_time' => $wishlist->event->end_time,
                        'location' => $wishlist->event->location,
                        'price' => $wishlist->event->price,
                        'is_free' => $wishlist->event->is_free,
                        'category' => $wishlist->event->category,
                        'flyer_url' => $wishlist->event->flyer_path 
                            ? asset('storage/' . $wishlist->event->flyer_path)
                            : null,
                    ],
                    'added_at' => $wishlist->created_at->format('Y-m-d H:i:s'),
                ];
            })->values()
        ]);
    }

    /**
     * Add event to wishlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $user = Auth::user();
        $eventId = $request->event_id;

        // Check if already in wishlist
        $exists = Wishlist::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Event sudah ada di wishlist',
            ], 400);
        }

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'event_id' => $eventId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil ditambahkan ke wishlist',
            'data' => $wishlist,
        ], 201);
    }

    /**
     * Remove event from wishlist
     */
    public function destroy($eventId)
    {
        $user = Auth::user();

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan di wishlist',
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dihapus dari wishlist',
        ]);
    }

    /**
     * Check if event is in user's wishlist
     */
    public function check($eventId)
    {
        $user = Auth::user();

        $isWishlisted = Wishlist::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->exists();

        return response()->json([
            'success' => true,
            'is_wishlisted' => $isWishlisted,
        ]);
    }
}
