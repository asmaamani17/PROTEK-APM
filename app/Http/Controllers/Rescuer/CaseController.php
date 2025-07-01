<?php

namespace App\Http\Controllers\Rescuer;

use App\Http\Controllers\Controller;
use App\Models\EmergencyCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
    /**
     * Update the status of a case.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $caseId
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $caseId)
    {
        $request->validate([
            'status' => 'required|in:dalam_tindakan,sedang_diselamatkan,bantuan_selesai,tidak_ditemui',
        ]);

        $case = EmergencyCase::with(['victim', 'rescuer'])->findOrFail($caseId);
        
        // Verify the rescuer is assigned to this case
        if ($case->rescuer_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Update status and set timestamps
        $case->status = $request->status;
        
        // Set appropriate timestamps based on status
        if ($request->status === 'sedang_diselamatkan' && !$case->rescue_started_at) {
            $case->rescue_started_at = now();
        } elseif ($request->status === 'bantuan_selesai' && !$case->rescue_completed_at) {
            $case->rescue_completed_at = now();
        }
        
        $case->save();

        // Broadcast status update event
        event(new \App\Events\CaseStatusUpdated($case));

        return response()->json([
            'message' => 'Status kes berjaya dikemas kini',
            'case' => $case,
        ]);
    }
}
