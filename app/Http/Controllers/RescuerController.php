<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RescueCase;
use App\Models\User;
use App\Helpers\DaerahCoordinates;
use Illuminate\Support\Facades\DB;
use App\Models\RescueCase as RescueCaseModel;

class RescuerController extends Controller
{
    /**
     * Get the active case assigned to the authenticated rescuer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveCase()
    {
        try {
            $case = RescueCase::with(['victim'])
                ->where('rescuer_id', auth()->id())
                ->whereIn('status', [
                    RescueCaseModel::STATUS_DALAM_TINDAKAN,
                    RescueCaseModel::STATUS_SEDANG_DISELAMATKAN
                ])
                ->first();

            if (!$case) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Tiada kes aktif buat masa ini.'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $case
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching active case: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan maklumat kes aktif: ' . $e->getMessage()
            ], 500);
        }
    }
    public function index()
    {
        $user = auth()->user();
        
        // Get active SOS cases with victim information from vulnerable_groups
        $cases = DB::table('rescue_cases')
            ->join('vulnerable_groups', 'rescue_cases.victim_id', '=', 'vulnerable_groups.id')
            ->whereIn('rescue_cases.status', [
                RescueCaseModel::STATUS_MOHON_BANTUAN,
                RescueCaseModel::STATUS_DALAM_TINDAKAN,
                RescueCaseModel::STATUS_SEDANG_DISELAMATKAN
            ])
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
                        'lng' => $case->victim_lng
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
        $case->status = RescueCaseModel::STATUS_DALAM_TINDAKAN;
        $case->save();

        return back()->with('success', 'Case Accepted!');
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
            
            // Update status to 'bantuan_selesai' and set completed_at timestamp
            $case->update([
                'status' => RescueCaseModel::STATUS_BANTUAN_SELESAI,
                'completed_at' => now(),
            ]);
            
            return back()->with('success', 'Kes penyelamatan telah berjaya ditandakan sebagai selesai!');
        } catch (\Exception $e) {
            \Log::error('Error completing rescue case: ' . $e->getMessage());
            return back()->with('error', 'Ralat semasa menandakan kes selesai. Sila cuba lagi.');
        }
    }
    
    /**
     * Update case status via API
     *
     * @param Request $request
     * @param int $caseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCaseStatus(Request $request, $caseId)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:' . implode(',', [
                    RescueCaseModel::STATUS_DALAM_TINDAKAN,
                    RescueCaseModel::STATUS_SEDANG_DISELAMATKAN,
                    RescueCaseModel::STATUS_BANTUAN_SELESAI,
                    RescueCaseModel::STATUS_TIDAK_DITEMUI
                ]),
                'notes' => 'nullable|string|max:1000'
            ]);
            
            $case = RescueCase::findOrFail($caseId);
            $user = auth()->user();
            
            // Verify the current user is the assigned rescuer
            if ($case->rescuer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dibenarkan untuk mengemas kini status kes ini.'
                ], 403);
            }
            
            // Update case status and notes
            $case->status = $validated['status'];
            if (isset($validated['notes'])) {
                $case->notes = $validated['notes'];
            }
            
            // Set timestamps based on status
            $now = now();
            if ($validated['status'] === RescueCaseModel::STATUS_SEDANG_DISELAMATKAN && !$case->rescue_started_at) {
                $case->rescue_started_at = $now;
            } elseif ($validated['status'] === RescueCaseModel::STATUS_BANTUAN_SELESAI) {
                $case->completed_at = $now;
            }
            
            $case->save();
            
            // Log the status update
            \Log::info('Case status updated by rescuer', [
                'case_id' => $case->id,
                'status' => $case->status,
                'rescuer_id' => $user->id,
                'rescuer_name' => $user->name
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status kes berjaya dikemas kini.',
                'data' => [
                    'case_id' => $case->id,
                    'status' => $case->status,
                    'status_text' => $this->getStatusText($case->status),
                    'updated_at' => $case->updated_at->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak sah.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kes tidak dijumpai.'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error updating case status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengemas kini status kes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get status text for the given status
     *
     * @param string $status
     * @return string
     */
    private function getStatusText($status)
    {
        $statuses = [
            RescueCaseModel::STATUS_MOHON_BANTUAN => 'Mohon Bantuan',
            RescueCaseModel::STATUS_DALAM_TINDAKAN => 'Dalam Tindakan',
            RescueCaseModel::STATUS_SEDANG_DISELAMATKAN => 'Sedang Diselamatkan',
            RescueCaseModel::STATUS_BANTUAN_SELESAI => 'Bantuan Selesai',
            RescueCaseModel::STATUS_TIDAK_DITEMUI => 'Tidak Ditemui'
        ];
        
        return $statuses[$status] ?? 'Status Tidak Diketahui';
    }
}
