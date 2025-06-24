<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RescueCase;
use App\Models\VulnerableGroup;
use App\Helpers\DaerahCoordinates;

class VictimController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $coordinates = DaerahCoordinates::getCoordinates($user->daerah);
        
        // Log user data for debugging
        \Log::info('Victim Dashboard - User Data:', [
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->no_telefon,
            'daerah' => $user->daerah
        ]);
        
        // Helper to normalize phone numbers (remove all non-digits)
        $normalizePhone = function($phone) {
            return preg_replace('/\D/', '', $phone);
        };
        $userPhoneNormalized = $normalizePhone($user->no_telefon);

        // Log normalized phone for debugging
        \Log::info('Victim lookup normalization', [
            'user_name' => $user->name,
            'user_phone_original' => $user->no_telefon,
            'user_phone_normalized' => $userPhoneNormalized,
        ]);

        // Try to find victim with normalized phone and case-insensitive, trimmed name match
        $victim = VulnerableGroup::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))])
            ->whereRaw('REGEXP_REPLACE(phone_number, "[^0-9]", "") = ?', [$userPhoneNormalized])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first(['id', 'name', 'phone_number', 'disability_category', 'latitude as lat', 'longitude as lng']);
            
        // Convert to array format expected by the view
        $victimData = $victim ? [$victim->toArray()] : [];
        $message = null;
        if (empty($victimData)) {
            $message = 'No victim record found for your account.';
        }

        return view('victim.dashboard', [
            'coordinates' => $coordinates,
            'user' => $user,
            'victims' => $victimData,
            'message' => empty($victimData) ? 'No victim record found for your account.' : null,
        ]);
    }

    public function sos(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        RescueCase::create([
            'victim_id' => auth()->id(),
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
        ]);

        return back()->with('success', 'SOS Sent Successfully!');
    }
}
