<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RescueCase;
use App\Models\User;
use App\Helpers\DaerahCoordinates;
use Illuminate\Support\Facades\DB;

class RescuerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get active SOS cases with victim information from vulnerable_groups
        $cases = DB::table('rescue_cases')
            ->join('vulnerable_groups', 'rescue_cases.victim_id', '=', 'vulnerable_groups.id')
            ->where('rescue_cases.status', '!=', 'completed')
            ->where('vulnerable_groups.district', $user->daerah)
            ->select(
                'rescue_cases.id',
                'rescue_cases.status',
                'rescue_cases.created_at',
                'rescue_cases.updated_at',
                'vulnerable_groups.name as victim_name',
                'vulnerable_groups.district as victim_daerah',
                'vulnerable_groups.phone_number as victim_no_phone',
                'vulnerable_groups.disability_category as victim_category',
                'vulnerable_groups.latitude as victim_lat',
                'vulnerable_groups.longitude as victim_lng'
            )
            ->get()
            ->map(function($case) {
                return (object)[
                    'id' => $case->id,
                    'victim' => (object)[
                        'name' => $case->victim_name,
                        'daerah' => $case->victim_daerah,
                        'phone_number' => $case->victim_no_phone, 
                        'category' => $case->victim_category,
                        'lat' => $case->victim_lat,
                        'lng' => $case->victim_lng,
                    ],
                    'status' => $case->status,
                    'created_at' => $case->created_at,
                    'formatted_created_at' => date('d/m/Y H:i', strtotime($case->created_at)),
                    'updated_at' => $case->updated_at,
                ];
            });
        
        $rescuerCoordinates = DaerahCoordinates::getCoordinates($user->daerah);
        $allDaerah = DaerahCoordinates::getAllCoordinates();
        
        return view('rescuer.dashboard', compact(
            'cases', 
            'rescuerCoordinates', 
            'user',
            'allDaerah'
        ));
    }

    public function accept($id)
    {
        $case = RescueCase::findOrFail($id);
        $case->rescuer_id = auth()->id();
        $case->status = 'assigned';
        $case->save();

        return back()->with('success', 'Case Accepted!');
    }

    public function updateStatus($id, $status)
    {
        $case = RescueCase::findOrFail($id);
        $case->status = $status;
        $case->save();

        return back()->with('success', 'Status Updated!');
    }
    
    /**
     * Mark a rescue case as completed
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete($id)
    {
        try {
            $case = RescueCase::findOrFail($id);
            
            // Verify the current user is the assigned rescuer
            if ($case->rescuer_id !== auth()->id()) {
                return back()->with('error', 'Anda tidak dibenarkan untuk melengkapkan penyelamatan ini.');
            }
            
            // Update status to 'rescued' and set completed_at timestamp
            $case->update([
                'status' => 'rescued',
                'completed_at' => now(),
            ]);
            
            return back()->with('success', 'Kes penyelamatan telah berjaya ditandakan sebagai selesai!');
        } catch (\Exception $e) {
            \Log::error('Error completing rescue case: ' . $e->getMessage());
            return back()->with('error', 'Ralat semasa menandakan kes selesai. Sila cuba lagi.');
        }
    }
}
