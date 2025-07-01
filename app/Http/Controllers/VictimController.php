<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RescueCase;
use App\Models\VulnerableGroup;
use App\Helpers\DaerahCoordinates;
use Illuminate\Support\Arr;
use GuzzleHttp\Client;

class VictimController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
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
            ->first(['id', 'name', 'phone_number', 'disability_category', 'latitude as lat', 'longitude as lng', 'serial_number']);
            
        // Convert to array format expected by the view
        $victimData = $victim ? [
            'id' => $victim->id,
            'name' => $victim->name,
            'phone_number' => $victim->phone_number,
            'disability_category' => $victim->disability_category,
            'lat' => $victim->lat,
            'lng' => $victim->lng,
            'profile_lat' => $victim->latitude,
            'profile_lng' => $victim->longitude,
            'serial_number' => $victim->serial_number
        ] : null;

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
        \DB::beginTransaction();
        
        try {
            $user = $request->user();
            
            // Get victim_id from the request
            $victimId = $request->input('victim_id');
            
            if (!$victimId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID mangsa tidak ditemui dalam permintaan'
                ], 400);
            }
            
            // Find the vulnerable person by ID
            $vulnerablePerson = VulnerableGroup::find($victimId);
            
            if (!$vulnerablePerson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maklumat mangsa tidak dijumpai. Sila pastikan nombor siri anda didaftarkan dalam sistem.'
                ], 404);
            }
            
            // Check if user already has active SOS
            $activeCase = RescueCase::where('victim_id', $vulnerablePerson->id)
                ->whereIn('status', [
                    RescueCase::STATUS_MOHON_BANTUAN,
                    RescueCase::STATUS_DALAM_TINDAKAN,
                    RescueCase::STATUS_SEDANG_DISELAMATKAN
                ])
                ->first();
                
            if ($activeCase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah mempunyai permintaan bantuan yang aktif!',
                    'case_id' => $activeCase->id
                ], 400);
            }

            // Validate request
            $validated = $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);
            
            // Log validated data (without sensitive info)
            \Log::info('SOS Request Received', [
                'user_id' => $user->id,
                'vulnerable_person_id' => $vulnerablePerson->id,
                'serial_number' => $vulnerablePerson->serial_number,
                'has_location' => !empty($validated['lat']) && !empty($validated['lng']),
                'accuracy' => $validated['accuracy'] ?? null,
                'district' => $vulnerablePerson->district ?? 'N/A'
            ]);

            // Get address from coordinates (reverse geocoding)
            $address = $this->getAddressFromCoordinates(
                $validated['lat'], 
                $validated['lng']
            );

            // Create new rescue case with additional data
            $rescueCaseData = [
                'victim_id' => $vulnerablePerson->id,
                'victim_name' => $vulnerablePerson->name,
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'accuracy' => $validated['accuracy'] ?? null,
                'district' => $vulnerablePerson->district ?? $validated['district'],
                'address' => $address,
                'status' => RescueCase::STATUS_MOHON_BANTUAN,
                'requested_at' => now(),
                'notes' => $validated['notes'] ?? null,
                'metadata' => [
                    'device' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                    'request_time' => now()->toIso8601String(),
                    'vulnerable_person_id' => $vulnerablePerson->id,
                    'user_id' => $user->id
                ]
            ];
            
            // Create the rescue case
            $rescueCase = RescueCase::create($rescueCaseData);
            
            if (!$rescueCase) {
                throw new \Exception('Gagal membuat rekod bantuan baru.');
            }
            
            // Add initial status history
            $rescueCase->statusHistory()->create([
                'status' => RescueCase::STATUS_MOHON_BANTUAN,
                'changed_by' => $vulnerablePerson->id,
                'notes' => 'Permintaan bantuan dihantar melalui aplikasi',
                'metadata' => [
                    'location' => [
                        'lat' => $validated['lat'],
                        'lng' => $validated['lng'],
                        'accuracy' => $validated['accuracy'] ?? null,
                        'address' => $address
                    ]
                ]
            ]);
            
            // Log successful creation
            \Log::info('Rescue Case created successfully', [
                'case_id' => $rescueCase->id,
                'status' => $rescueCase->status,
                'location' => [
                    'lat' => $rescueCase->lat,
                    'lng' => $rescueCase->lng,
                    'accuracy' => $rescueCase->accuracy
                ]
            ]);
            
            // Update vulnerable person's last known location
            $vulnerablePerson->update([
                'last_latitude' => $validated['lat'],
                'last_longitude' => $validated['lng'],
                'last_location_updated_at' => now()
            ]);
            
            // Broadcast the new rescue case event
            event(new \App\Events\NewRescueCase($rescueCase));
            
            // Commit the transaction
            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permintaan bantuan kecemasan berjaya dihantar! Pasukan penyelamat akan menghubungi anda tidak lama lagi.',
                'data' => [
                    'id' => $rescueCase->id,
                    'status' => $rescueCase->status,
                    'status_text' => $this->getStatusText($rescueCase->status),
                    'created_at' => $rescueCase->created_at->toIso8601String(),
                    'location' => [
                        'lat' => $rescueCase->lat,
                        'lng' => $rescueCase->lng,
                        'accuracy' => $rescueCase->accuracy,
                        'address' => $rescueCase->address
                    ]
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            \Log::error('SOS Validation Error: ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'user_id' => $request->user()?->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak sah: ' . implode(' ', Arr::flatten($e->errors()))
            ], 422);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('SOS Request Error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'error' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token'])
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ralat dalam menghantar permintaan bantuan: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Get the display text for a status
     *
     * @param string $status
     * @return string
     */
    /**
     * Get address from coordinates using reverse geocoding
     *
     * @param float $latitude
     * @param float $longitude
     * @return string
     */
    protected function getAddressFromCoordinates($latitude, $longitude)
    {
        try {
            // Check if we have Google Maps API key
            $apiKey = config('services.google.maps.key');
            if (empty($apiKey)) {
                // Fallback to the other possible config key
                $apiKey = config('services.google_maps.api_key');
                
                if (empty($apiKey)) {
                    \Log::warning('Google Maps API key not configured');
                    return 'Lokasi tidak diketahui';
                }
            }
            
            // Build the URL for reverse geocoding
            $url = sprintf(
                'https://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&key=%s&language=ms&region=my',
                $latitude,
                $longitude,
                $apiKey
            );
            \Log::debug('Reverse geocoding URL:', ['url' => preg_replace('/key=[^&]+/', 'key=***', $url)]);
            
            // Make the request
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);
            
            // Parse the response
            if (!empty($data['results'][0]['formatted_address'])) {
                return $data['results'][0]['formatted_address'];
            }
            
            \Log::warning('No address found for coordinates', [
                'lat' => $latitude,
                'lng' => $longitude,
                'response' => $data
            ]);
            
            return 'Lokasi tidak diketahui';
            
        } catch (\Exception $e) {
            \Log::error('Reverse geocoding failed: ' . $e->getMessage(), [
                'lat' => $latitude,
                'lng' => $longitude,
                'error' => $e->getTraceAsString()
            ]);
            
            return 'Lokasi tidak diketahui';
        }
    }
    
    /**
     * Get the display text for a status
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
    
    /**
     * Update the status of a rescue case.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $caseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $caseId)
    {
        $case = RescueCase::findOrFail($caseId);
        
        // Check if the authenticated user is the victim or has permission
        if (auth()->user()->id !== $case->victim_id && !auth()->user()->hasRole(['admin', 'rescuer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dibenarkan mengemas kini status kes ini.'
            ], 403);
        }
        
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
