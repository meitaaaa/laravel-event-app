<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactMessageController extends Controller
{
    /**
     * Store a new contact message from public form
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $message = ContactMessage::create($validator->validated());

            return response()->json([
                'message' => 'Pesan berhasil dikirim. Tim kami akan merespons dalam 1x24 jam.',
                'data' => $message
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim pesan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all contact messages (Admin only)
     */
    public function index(Request $request)
    {
        try {
            $query = ContactMessage::query()->orderBy('created_at', 'desc');

            // Filter by read status
            if ($request->has('status')) {
                if ($request->status === 'unread') {
                    $query->unread();
                } elseif ($request->status === 'read') {
                    $query->read();
                }
            }

            // Search by name, email, or subject
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%");
                });
            }

            $messages = $query->paginate($request->get('per_page', 15));

            // Get statistics
            $stats = [
                'total' => ContactMessage::count(),
                'unread' => ContactMessage::unread()->count(),
                'read' => ContactMessage::read()->count()
            ];

            return response()->json([
                'messages' => $messages,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data pesan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single message detail (Admin only)
     */
    public function show($id)
    {
        try {
            $message = ContactMessage::findOrFail($id);
            
            // Auto-mark as read when viewed
            if (!$message->is_read) {
                $message->markAsRead();
            }

            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pesan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mark message as read (Admin only)
     */
    public function markAsRead($id)
    {
        try {
            $message = ContactMessage::findOrFail($id);
            $message->markAsRead();

            return response()->json([
                'message' => 'Pesan ditandai sebagai sudah dibaca',
                'data' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menandai pesan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete message (Admin only)
     */
    public function destroy($id)
    {
        try {
            $message = ContactMessage::findOrFail($id);
            $message->delete();

            return response()->json([
                'message' => 'Pesan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus pesan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
