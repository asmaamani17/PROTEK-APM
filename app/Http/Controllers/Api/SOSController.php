<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SOSRequest;
use Illuminate\Support\Facades\DB;

class SOSController extends Controller
{
    /**
     * Handle incoming SOS request
     */
    public function requestHelp(Request $request)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            // Get the authenticated user (victim)
            $user = auth()->user();
            
            // Create new SOS request
            $sosRequest = SOSRequest::create([
                'user_id' => $user->id,
                'status' => 'requested_help',
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'requested_at' => now(),
            ]);

            // Here you can add code to notify admin about the new SOS request
            // For example, using Laravel Notifications or Pusher for real-time updates

            return response()->json([
                'success' => true,
                'message' => 'Permintaan bantuan berjaya dihantar',
                'data' => $sosRequest
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghantar permintaan bantuan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Other controller methods can be added here as needed
}
