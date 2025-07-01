<?php

namespace App\Http\Controllers;

use App\Models\RescueCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Helpers\DaerahCoordinates;
use Illuminate\Http\Request;
use App\Models\RescueCase as RescueCaseModel;

class AdminController extends Controller
{
    /**
     * Get active victims for the map
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveVictims()
    {
        try {
            $victims = RescueCase::with(['user', 'rescuer'])
                ->whereIn('status', ['requested', 'in_progress', 'on_hold'])
                ->get()
                ->map(function ($case) {
                    return [
                        'id' => $case->id,
                        'name' => $case->user->name,
                        'lat' => (float)$case->latitude,
                        'lng' => (float)$case->longitude,
                        'status' => $case->status,
                        'category' => $this->mapCategory(
                            $case->disability_type ?? '',
                            $case->age_group ?? '',
                            $case->oku_status ?? 'TIDAK'
                        ),
                        'created_at' => $case->created_at->toDateTimeString(),
                        'updated_at' => $case->updated_at->toDateTimeString(),
                        'rescuer' => $case->rescuer ? [
                            'id' => $case->rescuer->id,
                            'name' => $case->rescuer->name,
                            'phone' => $case->rescuer->phone
                        ] : null
                    ];
                });

            return response()->json($victims);
        } catch (\Exception $e) {
            \Log::error('Error fetching active victims: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch active victims',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Map database categories to our application categories
     */
    protected function mapCategory($disability, $ageGroup, $okuStatus)
    {
        // If OKU status is 'YA', categorize as OKU
        if ($okuStatus === 'YA') {
            return 'OKU';
        }
        
        // Check age group
        if ($ageGroup === 'WARGA EMAS') {
            return 'Warga Emas';
        } elseif ($ageGroup === 'KANAK-KANAK') {
            return 'Anak Kecil';
        }
        
        // Check disability category for pregnant women
        if (stripos($disability, 'MENGANDUNG') !== false) {
            return 'Ibu Mengandung';
        }
        
        // Default category
        return 'Lain-lain';
    }
    
    /**
     * Update case status and assign rescuer
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $caseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCaseStatus(Request $request, $caseId)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:' . implode(',', [
                    'mohon_bantuan',
                    'dalam_tindakan',
                    'sedang_diselamatkan',
                    'bantuan_selesai',
                    'tidak_ditemui'
                ]),
                'rescuer_id' => 'nullable|exists:users,id,role,rescuer',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Find the case with victim relationship
            $case = RescueCase::with('victim')->findOrFail($caseId);
            $admin = auth()->user();
            
            // Verify admin has access to this case (same district)
            if ($case->victim->daerah !== $admin->daerah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak mempunyai akses untuk mengemas kini kes ini.'
                ], 403);
            }

            // Update case status and rescuer
            $case->status = $validated['status'];
            
            // Update rescuer if provided
            if (isset($validated['rescuer_id'])) {
                $case->rescuer_id = $validated['rescuer_id'];
                
                // Get rescuer name
                $rescuer = User::find($validated['rescuer_id']);
                if ($rescuer) {
                    $case->rescuer_name = $rescuer->name;
                }
            }
            
            // Update notes if provided
            if (isset($validated['notes'])) {
                $case->notes = $validated['notes'];
            }
            
            // Set accepted_at timestamp when status changes to DALAM_TINDAKAN
            if ($validated['status'] === 'dalam_tindakan' && !$case->accepted_at) {
                $case->accepted_at = now();
            }
            
            $case->save();

            // Log the status update
            \Log::info('Case status updated', [
                'case_id' => $case->id,
                'status' => $case->status,
                'rescuer_id' => $case->rescuer_id,
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status kes berjaya dikemas kini',
                'data' => $case->load(['victim', 'rescuer'])
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating case status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengemas kini status kes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of rescuers for the admin's district
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRescuers()
    {
        try {
            $rescuers = User::where('role', 'rescuer')
                ->where('daerah', auth()->user()->daerah)
                ->get(['id', 'name', 'phone']);
            
            return response()->json($rescuers);
        } catch (\Exception $e) {
            \Log::error('Error fetching rescuers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan senarai penyelamat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of cases for the admin's district
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCases()
    {
        try {
            $cases = RescueCase::with(['victim', 'rescuer'])
                ->whereHas('victim', function($query) {
                    $query->where('daerah', auth()->user()->daerah);
                })
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $cases
            ]);
                
        } catch (\Exception $e) {
            \Log::error('Error fetching cases: ' . $e->getMessage(), [
                'error' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan senarai kes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle incoming webhook from Botpress.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleBotpressWebhook(Request $request)
    {
        \Log::info('Botpress webhook received:', $request->all());

        $payload = $request->json()->all();
        $caseId = $payload['caseId'] ?? null;
        $transcript = $payload['transcript'] ?? null;

        if (!$caseId || !$transcript) {
            \Log::warning('Botpress webhook: Missing caseId or transcript.', ['payload' => $payload]);
            return response()->json(['success' => false, 'message' => 'Invalid payload.'], 400);
        }

        try {
            $case = RescueCaseModel::findOrFail($caseId);

            // Append the transcript to the existing notes
            $newNote = "--- Perbualan Chatbot ---\n" . $transcript;
            $case->notes = ($case->notes ? $case->notes . "\n\n" : '') . $newNote;
            $case->save();

            \Log::info('Botpress transcript added to case notes.', ['case_id' => $caseId]);

            return response()->json(['success' => true, 'message' => 'Webhook processed successfully.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Botpress webhook: Case not found.', ['case_id' => $caseId]);
            return response()->json(['success' => false, 'message' => 'Case not found.'], 404);
        } catch (\Exception $e) {
            \Log::error('Error processing Botpress webhook:', [
                'case_id' => $caseId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }

    /**
     * Get display text for status
     * 
     * @param string $status
     * @return string
     */
    protected function getStatusText($status)
    {
        $statusMap = [
            'tiada_bantuan' => 'Tiada Bantuan',
            'mohon_bantuan' => 'Mohon Bantuan',
            'dalam_tindakan' => 'Dalam Tindakan',
            'sedang_diselamatkan' => 'Sedang Diselamatkan',
            'bantuan_selesai' => 'Bantuan Selesai',
            'tidak_ditemui' => 'Tidak Ditemui'
        ];
        
        return $statusMap[$status] ?? $status;
    }

    public function index()
    {
        // Get current admin's area
        $admin = auth()->user();
        $adminArea = $admin->daerah ?? 'Kota Tinggi'; // Default to Kota Tinggi if null
        $activeTab = request()->get('tab', 'dashboard');
        
        // Get coordinates for the admin's district
        $districtCoords = DaerahCoordinates::getCoordinates($adminArea);
        $defaultLat = $districtCoords['lat'] ?? 1.7381; // Default to Kota Tinggi
        $defaultLng = $districtCoords['lng'] ?? 103.8999;

        // Get all rescuers for the admin's area
        $rescuers = User::where('role', 'rescuer')
            ->where('district', $adminArea)
            ->where('status', 'aktif')
            ->get();

        // Load cases with related victim and rescuer
        $cases = RescueCase::with(['victim', 'rescuer'])
            ->whereHas('victim', function($query) use ($adminArea) {
                $query->where('district', $adminArea);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get vulnerable groups for admin's area from database
        $vulnerableGroups = \App\Models\VulnerableGroup::where('district', $adminArea)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function($item) {
                // Map database fields to the format expected by the view
                return [
                    'serial_number' => $item->serial_number,
                    'name' => $item->name,
                    'lat' => (float)$item->latitude,
                    'lng' => (float)$item->longitude,
                    'gender' => $item->gender, // Include gender for info window
                    'disability_category' => $item->disability_category, // Include original category for coloring
                    'category' => $this->mapCategory($item->disability_category, $item->age_group, $item->oku_status)
                ];
            })
            ->toArray();

        // Group by district (though we're already filtering by district, this maintains the expected structure)
        $victimsByDaerah = [
            $adminArea => $vulnerableGroups
        ];
        
        // If no vulnerable groups found for the district, return empty array to prevent errors
        if (empty($vulnerableGroups)) {
            $victimsByDaerah = [
                $adminArea => []
            ];
        }

        // Get only victims for admin's area and prepare for status list
        $victims = collect($victimsByDaerah[$adminArea] ?? [])->map(function($victim) {
            // Use the original disability category if available, otherwise use the mapped category
            $victim['display_category'] = $victim['disability_category'] ?? $victim['category'];
            return $victim;
        })->toArray();

        // Get daerah coordinates for pin placement
        $daerahCoordinates = DaerahCoordinates::getAllCoordinates();
        
        // Get center coordinates for admin's area
        $areaCenter = [
            'BATU PAHAT' => ['lat' => 1.85, 'lng' => 102.93],
            'SEGAMAT' => ['lat' => 2.50, 'lng' => 102.81],
            'KOTA TINGGI' => ['lat' => 1.73, 'lng' => 103.90],
            'KLUANG' => ['lat' => 2.03, 'lng' => 103.32]
        ][$adminArea] ?? ['lat' => 2.03, 'lng' => 103.32]; // Default to Kluang if area not found

        // Convert victims to array for map markers
        $victimsWithCoordinates = $victims;

        // Pass both variables to maintain backward compatibility
        // Get rescuers for the admin's area
        $rescuers = User::where('role', 'rescuer')
            ->where('daerah', $adminArea)
            ->get();

        // Get IDs of rescuers assigned to active cases in the admin's area
        $assignedRescuerIds = $cases->where('status', '!=', 'completed')
                                   ->whereNotNull('rescuer_id')
                                   ->pluck('rescuer_id')
                                   ->unique();

        // Determine rescuer status
        $rescuers->each(function ($rescuer) use ($assignedRescuerIds) {
            $rescuer->status = $assignedRescuerIds->contains($rescuer->id) ? 'assigned' : 'available';
        });

        // Pass both variables to maintain backward compatibility
        return view('admin.dashboard', compact('cases', 'victims', 'victimsWithCoordinates', 'activeTab', 'rescuers'));
    }

}
