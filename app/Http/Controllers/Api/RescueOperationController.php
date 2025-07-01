<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SOSRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RescueOperationController extends Controller
{
    /**
     * Update the status of a rescue operation
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', [
                SOSRequest::STATUS_IN_ACTION,
                SOSRequest::STATUS_RESCUING,
                SOSRequest::STATUS_COMPLETED,
                SOSRequest::STATUS_CANCELLED
            ]),
        ]);

        $sosRequest = SOSRequest::findOrFail($id);
        
        // Only allow updates to active requests
        if (in_array($sosRequest->status, [SOSRequest::STATUS_COMPLETED, SOSRequest::STATUS_CANCELLED])) {
            return response()->json([
                'message' => 'Cannot update status of a completed or cancelled request'
            ], 400);
        }

        // Update status and set responder if this is the first status update
        $updates = ['status' => $request->status];
        
        if ($sosRequest->status === SOSRequest::STATUS_REQUESTED && 
            in_array($request->status, [SOSRequest::STATUS_IN_ACTION, SOSRequest::STATUS_RESCUING])) {
            $updates['responded_by'] = Auth::id();
            $updates['responded_at'] = now();
        }

        $sosRequest->update($updates);

        // Log the status update
        Log::info("Rescue operation #{$sosRequest->id} status updated to: {$request->status}");

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $sosRequest->fresh()
        ]);
    }

    /**
     * Get the current status of a rescue operation
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus($id)
    {
        $sosRequest = SOSRequest::select('id', 'status', 'responded_at', 'responded_by')
            ->with('responder:id,name')
            ->findOrFail($id);

        return response()->json([
            'data' => $sosRequest
        ]);
    }
}
