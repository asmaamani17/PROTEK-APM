<?php

namespace App\Http\Controllers;

use App\Models\RescueCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Helpers\DaerahCoordinates;
use Illuminate\Http\Request;

class AdminController extends Controller
{
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
    
    public function index()
    {
        // Get current admin's area
        $admin = auth()->user();
        $adminArea = $admin->daerah;
        $activeTab = request()->get('tab', 'dashboard');

        // Load cases with related victim and rescuer
        $cases = RescueCase::with(['victim', 'rescuer'])
            ->whereHas('victim', function($query) use ($adminArea) {
                $query->where('daerah', $adminArea);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'pending' => $cases->where('status', 'mohon_bantuan')->count(),
            'in_progress' => $cases->where('status', 'in_progress')->count(),
            'rescued' => $cases->where('status', 'rescued')->count(),
            'not_found' => $cases->where('status', 'not_found')->count(),
            'completed' => $cases->where('status', 'completed')->count(),
            'total' => $cases->count(),
        ];

        // Get vulnerable groups for admin's area from database
        $vulnerableGroups = \App\Models\VulnerableGroup::where('district', $adminArea)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function($item) use ($cases) {
                // Find if this victim has an active case
                $case = $cases->where('victim_id', $item->user_id)
                    ->where('status', '!=', 'completed')
                    ->first();

                // Map database fields to the format expected by the view
                return [
                    'serial_number' => $item->serial_number,
                    'name' => $item->name,
                    'lat' => (float)$item->latitude,
                    'lng' => (float)$item->longitude,
                    'gender' => $item->gender,
                    'disability_category' => $item->disability_category,
                    'category' => $this->mapCategory($item->disability_category, $item->age_group, $item->oku_status),
                    'status' => $case ? $case->status : null,
                    'case_id' => $case ? $case->id : null,
                    'user_id' => $item->user_id
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

        // Get center coordinates for admin's area
        $areaCenter = [
            'BATU PAHAT' => ['lat' => 1.85, 'lng' => 102.93],
            'SEGAMAT' => ['lat' => 2.50, 'lng' => 102.81],
            'KOTA TINGGI' => ['lat' => 1.73, 'lng' => 103.90],
            'KLUANG' => ['lat' => 2.03, 'lng' => 103.32]
        ][$adminArea] ?? ['lat' => 2.03, 'lng' => 103.32]; // Default to Kluang if area not found

        // Convert victims to array for map markers
        $victimsWithCoordinates = $victims;

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

        // Pass all variables to the view
        return view('admin.dashboard', compact('cases', 'victims', 'victimsWithCoordinates', 'activeTab', 'rescuers', 'stats'));
    }

}
