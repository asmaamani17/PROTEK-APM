@extends('layouts.main')

@section('title', 'Dashboard')

@push('styles')
<style>
    .cursor-pointer { cursor: pointer; }
    .case-details { white-space: pre-line; }
    
    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
    }
    
    /* Toast Notifications */
    .toast {
        margin-bottom: 0.5rem;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
    }
    
    .toast.show {
        display: block;
    }
    
    /* Status Update Dropdown */
    .status-update select {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Info Window Styling */
    .info-window {
        min-width: 200px;
        padding: 0.5rem;
    }
    
    .info-window h5 {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        color: #333;
    }
    
    .info-window p {
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
    }
    
    /* Chatbot Styles */
    #chatbot-container {
        display: none;
    }
    #chatbot-container.show {
        display: block;
    }
    .chat-message {
        padding: 10px 15px;
        margin: 5px 0;
        border-radius: 10px;
        max-width: 80%;
    }
    .chat-message.user {
        background-color: #007bff;
        color: white;
        margin-left: auto;
    }
    .chat-message.bot {
        background-color: #f8f9fa;
        color: #333;
    }
    #chatbot-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>DASHBOARD BILIK GERAKAN</h2>
    <div class="d-flex align-items-center gap-3">
        <div id="chatbot-toggle" class="btn btn-primary">
            <i class="fas fa-comments"></i> Chatbot
        </div>
    </div>
</div>

<!-- Chatbot Container -->
<div id="chatbot-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1000; display: none;">
    <div class="card" style="width: 400px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chatbot Bantuan</h5>
            <button id="close-chatbot" class="btn btn-sm btn-danger">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chatbot-messages" class="card-body" style="height: 400px; overflow-y: auto;">
            <!-- Messages will be appended here -->
        </div>
        <div class="card-footer">
            <form id="chatbot-form" class="d-flex gap-2">
                <input type="text" id="chatbot-input" class="form-control" placeholder="Tulis pesan anda...">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<div class="row g-3">
    @php
        $totalCases = $cases->count();
        $activeCases = $cases->where('status', '!=', 'completed')->count();
        $completedCases = $cases->where('status', 'completed')->count();
        $totalVictims = count($victimsWithCoordinates ?? []);
    @endphp
    
    @foreach([
      ['label' => 'Mohon Bantuan', 'bg' => 'danger', 'count' => $stats['mohon_bantuan'] ?? 0],
      ['label' => 'Dalam Tindakan', 'bg' => 'primary', 'count' => $stats['dalam_tindakan'] ?? 0],
      ['label' => 'Sedang Diselamatkan', 'bg' => 'warning', 'count' => $stats['sedang_diselamatkan'] ?? 0],
      ['label' => 'Bantuan Selesai', 'bg' => 'success', 'count' => $stats['bantuan_selesai'] ?? 0],
      ['label' => 'Tidak Ditemui', 'bg' => 'dark', 'count' => $stats['tidak_ditemui'] ?? 0],
      ['label' => 'Jumlah Kes', 'bg' => 'secondary', 'count' => $stats['jumlah_kes'] ?? 0],
    ] as $stat)
    <div class="col-12 col-md-4 col-lg-2">
      <div class="p-3 text-white rounded text-center" style="background-color: var(--bs-{{ $stat['bg'] }});">
        <div>{{ $stat['label'] }}</div>
        <div class="fs-4 fw-bold">{{ $stat['count'] }}</div>
      </div>
    </div>
    @endforeach
</div>

<div class="card mt-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <div>
        <i class="fas fa-map-marked-alt me-2"></i>
        <span>Peta Kedudukan Mangsa - {{ auth()->user()->daerah ?? 'Kota Tinggi' }}</span>
      </div>
      <button id="refreshMapBtn" class="btn btn-sm btn-light" title="Muat semula peta">
        <i class="fas fa-sync-alt"></i> Muat Semula
      </button>
    </div>
    <div class="card-body p-0 position-relative" style="min-height: 500px;">
      <!-- Map Container -->
      <div id="map" style="height: 500px; width: 100%;"></div>
      
      <!-- Loading Overlay -->
      <div id="mapLoading" class="position-absolute top-0 start-0 w-100 h-100 bg-white d-flex justify-content-center align-items-center" style="z-index: 1;">
        <div class="text-center">
          <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Memuatkan peta...</span>
          </div>
          <h5 class="text-muted">Memuatkan Peta</h5>
          <p class="text-muted small">Sila tunggu sebentar...</p>
        </div>
      </div>
      
      <!-- Error Message -->
      <div id="mapError" class="alert alert-danger m-3" style="display: none; position: absolute; top: 10px; left: 10px; right: 10px; z-index: 2; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);">
        <div class="d-flex align-items-center">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <div class="flex-grow-1">
            <strong id="mapErrorTitle">Ralat</strong>
            <div id="mapErrorMessage" class="small">Terdapat masalah ketika memuat peta.</div>
          </div>
          <button type="button" class="btn-close" onclick="document.getElementById('mapError').style.display='none'"></button>
        </div>
      </div>
      
      <!-- Map Legend -->
      <div class="position-absolute bottom-0 end-0 m-3" style="z-index: 1;">
        <div class="card shadow-sm">
          <div class="card-header py-1 px-2 bg-white">
            <small class="fw-bold"><i class="fas fa-legend me-1"></i> Legenda</small>
          </div>
          <div class="card-body p-2">
            <div class="d-flex align-items-center mb-1">
              <img src="{{ asset('images/marker-mohon_bantuan.png') }}" width="20" height="20" class="me-2" alt="Mohon Bantuan">
              <small>Mohon Bantuan</small>
            </div>
            <div class="d-flex align-items-center mb-1">
              <img src="{{ asset('images/marker-dalam_tindakan.png') }}" width="20" height="20" class="me-2" alt="Dalam Tindakan">
              <small>Dalam Tindakan</small>
            </div>
            <div class="d-flex align-items-center mb-1">
              <img src="{{ asset('images/marker-sedang_diselamatkan.png') }}" width="20" height="20" class="me-2" alt="Sedang Diselamatkan">
              <small>Sedang Diselamatkan</small>
            </div>
            <div class="d-flex align-items-center">
              <img src="{{ asset('images/marker-bantuan_selesai.png') }}" width="20" height="20" class="me-2" alt="Bantuan Selesai">
              <small>Bantuan Selesai</small>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer bg-light py-2">
      <small class="text-muted">
        <i class="fas fa-info-circle me-1"></i> Klik pada penanda untuk maklumat lanjut
      </small>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-primary text-white">Senarai Status Golongan Rentan</div>
    <div class="card-body p-0">
      <table class="table table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($victims as $victim)
          @php
            $status = $victim['status'] ?? 'tiada_bantuan';
            $statusMap = [
              'mohon_bantuan' => ['Mohon Bantuan','danger'],
              'dalam_tindakan' => ['Dalam Tindakan','primary'],
              'sedang_diselamatkan' => ['Sedang Diselamatkan','warning'],
              'bantuan_selesai' => ['Bantuan Selesai','success'],
              'tidak_ditemui' => ['Tidak Ditemui','dark'],
            ];
            $statusText = $statusMap[$status][0] ?? $status;
            $statusClass = 'bg-' . ($statusMap[$status][1] ?? 'secondary');
          @endphp
          <tr data-bs-toggle="modal" data-bs-target="#updateStatusModal" 
              data-case-id="{{ $victim['id'] }}"
              data-victim-name="{{ $victim['name'] ?? 'Mangsa' }}"
              data-current-status="{{ $statusText }}">
            <td>{{ $victim['serial_number'] ?? '-' }}</td>
            <td>{{ $victim['name'] ?? '-' }}</td>
            <td>{{ $victim['disability_category'] ?? $victim['category'] ?? '-' }}</td>
            <td class="cursor-pointer">
              <div class="info-window">
                <h5>{{ $victim['name'] ?? 'Mangsa' }}</h5>
                <div class="status-badge {{ $statusClass }} text-white p-1 rounded mb-2">
                    {{ $statusText }}
                </div>
                <div class="status-update mb-2">
                    <select class="form-select form-select-sm" onchange="updateStatus({{ $victim['id'] }}, this.value)" data-request-id="{{ $victim['id'] }}">
                        <option value="mohon_bantuan" {{ $victim['status'] == 'mohon_bantuan' ? 'selected' : '' }}>Mohon Bantuan</option>
                        <option value="dalam_tindakan" {{ $victim['status'] == 'dalam_tindakan' ? 'selected' : '' }}>Dalam Tindakan</option>
                        <option value="sedang_diselamatkan" {{ $victim['status'] == 'sedang_diselamatkan' ? 'selected' : '' }}>Sedang Diselamatkan</option>
                        <option value="bantuan_selesai" {{ $victim['status'] == 'bantuan_selesai' ? 'selected' : '' }}>Bantuan Selesai</option>
                    </select>
                </div>
              </div>
              @if(!empty($victim['notes']))
                <i class="fas fa-info-circle ms-1" title="{{ $victim['notes'] }}"></i>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="text-center">Tiada rekod mangsa dijumpai</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-primary text-white">Senarai Status Penyelamat</div>
    <div class="card-body p-0">
      <table class="table table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>No. Telefon</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rescuers as $rescuer)
          <tr>
            <td>{{ $rescuer->id }}</td>
            <td>{{ $rescuer->name }}</td>
            <td>{{ $rescuer->phone_number ?? '-' }}</td>
            <td>
              @php
                $status = $rescuer->status ?? 'available';
                $statusMap = [
                  'available' => ['Sedia', 'primary'],
                  'assigned' => ['Bertugas', 'warning'],
                ];
                $statusText = $statusMap[$status][0] ?? ucfirst($status);
                $statusClass = 'bg-' . ($statusMap[$status][1] ?? 'secondary');
              @endphp
              <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="text-center">Tiada rekod penyelamat dijumpai</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <!-- Toasts will be dynamically inserted here -->
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Kemas Kini Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStatusForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="case_id" id="caseId">
                    
                    <div class="mb-3">
                        <label class="form-label">Mangsa</label>
                        <input type="text" class="form-control" id="victimName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Semasa</label>
                        <input type="text" class="form-control" id="currentStatus" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status Baru</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="mohon_bantuan">Mohon Bantuan</option>
                            <option value="dalam_tindakan">Dalam Tindakan</option>
                            <option value="sedang_diselamatkan">Sedang Diselamatkan</option>
                            <option value="bantuan_selesai">Bantuan Selesai</option>
                            <option value="tidak_ditemui">Tidak Ditemui</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rescuer_id" class="form-label">Penyelamat</label>
                        <select class="form-select" id="rescuer_id" name="rescuer_id">
                            <option value="">-- Pilih Penyelamat --</option>
                            @foreach($rescuers as $rescuer)
                                <option value="{{ $rescuer->id }}">{{ $rescuer->name }} ({{ $rescuer->no_telefon ?? 'Tiada No. Telefon' }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Nota (Termasuk Perbualan Chatbot)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="5" placeholder="Nota akan dipaparkan di sini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="d-none spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Global variables
if (typeof map === 'undefined') {
    var map;
}
if (typeof markers === 'undefined') {
    var markers = [];
}
if (typeof infoWindows === 'undefined') {
    var infoWindows = [];
}
if (typeof districtBoundary === 'undefined') {
    var districtBoundary = null;
}

// Function to initialize the map
function initMap() {
    console.log('initMap() called');
    const mapElement = document.getElementById('map');
    const mapLoading = document.getElementById('mapLoading');
    const mapError = document.getElementById('mapError');
    
    if (!mapElement) {
        console.error('Map element not found');
        if (mapError) {
            document.getElementById('mapErrorTitle').textContent = 'Ralat Peta';
            document.getElementById('mapErrorMessage').textContent = 'Elemen peta tidak ditemukan. Sila muat semula halaman.';
            mapError.style.display = 'block';
        }
        if (mapLoading) mapLoading.style.display = 'none';
        return;
    }
    
    // Ensure Google Maps API is loaded
    if (!window.google || !window.google.maps) {
        console.error('Google Maps API not loaded');
        if (mapError) {
            document.getElementById('mapErrorTitle').textContent = 'Ralat Peta';
            document.getElementById('mapErrorMessage').textContent = 'Gagal memuat Google Maps API. Sila muat semula halaman.';
            mapError.style.display = 'block';
        }
        if (mapLoading) mapLoading.style.display = 'none';
        return;
    }
    
    try {
        // Show loading state
        if (mapLoading) mapLoading.style.display = 'flex';
        if (mapError) mapError.style.display = 'none';
        
        // Clear existing map if it exists
        if (window.map) {
            window.map = null;
        }
        
        // Initialize the map
        window.map = new google.maps.Map(mapElement, {
            zoom: 12,
            center: { lat: 1.7333, lng: 103.9333 }, // Default to Johor Bahru
            mapTypeId: 'roadmap',
            streetViewControl: false,
            fullscreenControl: true,
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                },
                {
                    featureType: 'transit',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });
        
        // Initialize markers array
        if (!window.markers) {
            window.markers = [];
        }
        
        // Initialize info windows array
        if (!window.infoWindows) {
            window.infoWindows = [];
        }
        
        // Add district boundary if available
        @if(isset($districtBoundary) && is_array($districtBoundary))
            try {
                console.log('Adding district boundary');
                if (window.districtBoundary) {
                    window.districtBoundary.setMap(null);
                }
                window.districtBoundary = new google.maps.Polygon({
                    paths: @json($districtBoundary),
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.1
                });
                window.districtBoundary.setMap(window.map);
                console.log('District boundary added successfully');
            } catch (boundaryError) {
                console.error('Error adding district boundary:', boundaryError);
            }
        @endif
        
        console.log('Map initialized successfully');
        
        // Add victim markers
        console.log('Adding victim markers...');
        addVictimMarkers();
        
    } catch (error) {
        console.error('Error initializing map:', error);
        if (mapError) {
            document.getElementById('mapErrorTitle').textContent = 'Ralat Peta';
            document.getElementById('mapErrorMessage').textContent = 'Gagal memulakan peta. Sila muat semula halaman.';
            mapError.style.display = 'block';
        }
    } finally {
        // Hide loading state
        if (mapLoading) {
            console.log('Hiding loading state');
            mapLoading.style.display = 'none';
        }
    }
    
    // Function to add victim markers to the map
    async function addVictimMarkers() {
        console.log('addVictimMarkers() called');
        
        if (!window.map) {
            console.error('Map not initialized in addVictimMarkers');
            return;
        }
        
        const mapLoading = document.getElementById('mapLoading');
        const mapError = document.getElementById('mapError');
        
        try {
            // Show loading state
            if (mapLoading) {
                console.log('Showing loading state');
                mapLoading.style.display = 'flex';
            }
            if (mapError) mapError.style.display = 'none';
            
            // Clear existing markers first
            console.log('Clearing existing markers');
            clearMarkers();
            
            // Get victim data from the server
            console.log('Fetching victim data from /api/victims/active');
            const response = await fetch('/api/victims/active');
            
            if (!response.ok) {
                const errorMsg = `HTTP error! status: ${response.status}`;
                console.error(errorMsg);
                throw new Error(`Gagal memuat data mangsa: ${response.statusText}`);
            }
            
            const victims = await response.json();
            console.log(`Received ${victims.length} victims from API`);
            
            if (!Array.isArray(victims)) {
                const errorMsg = 'Invalid response format from server - expected array';
                console.error(errorMsg, victims);
                throw new Error('Format data mangsa tidak sah. Sila cuba lagi.');
            }
            
            const bounds = new google.maps.LatLngBounds();
            let hasValidMarkers = false;
            let markersAdded = 0;
            
            // Add a marker for each victim
            console.log('Adding markers for victims...');
            victims.forEach((victim, index) => {
                try {
                    if (!victim.latitude || !victim.longitude) {
                        console.warn(`Skipping victim ${victim.id} - missing coordinates`);
                        return;
                    }
                    
                    const position = {
                        lat: parseFloat(victim.latitude),
                        lng: parseFloat(victim.longitude)
                    };
                    
                    if (isNaN(position.lat) || isNaN(position.lng)) {
                        console.warn(`Skipping victim ${victim.id} - invalid coordinates`, position);
                        return;
                    }
                    
                    // Create a marker for this victim
                    const marker = new google.maps.Marker({
                        position: position,
                        map: window.map,
                        title: victim.name || 'Mangsa',
                        icon: getMarkerIcon(victim.status),
                        animation: google.maps.Animation.DROP
                    });
                    
                    // Add to markers array for later reference
                    window.markers.push(marker);
                    
                    // Create an info window for this marker
                    const contentString = `
                        <div class="info-window">
                            <h6>${victim.name || 'Nama tidak diketahui'}</h6>
                            <p>No. Telefon: ${victim.phone || 'Tiada'}</p>
                            <p>Status: ${getStatusText(victim.status)}</p>
                            <p>Lokasi: ${victim.location || 'Tidak dinyatakan'}</p>
                            <button class="btn btn-sm btn-primary mt-2" onclick="updateStatus('${victim.id}')">
                                Kemas Kini Status
                            </button>
                        </div>
                    `;
                    
                    const infoWindow = new google.maps.InfoWindow({
                        content: contentString
                    });
                    
                    // Add to infoWindows array
                    window.infoWindows.push(infoWindow);
                    
                    // Add click event to show info window
                    marker.addListener('click', () => {
                        // Close other info windows
                        window.infoWindows.forEach(iw => iw.close());
                        
                        // Open this info window
                        infoWindow.open(window.map, marker);
                        
                        // Center the map on the clicked marker
                        window.map.panTo(marker.getPosition());
                    });
                    
                    // Extend the bounds to include this marker's position
                    bounds.extend(position);
                    hasValidMarkers = true;
                    markersAdded++;
                } catch (error) {
                    console.error(`Error adding marker for victim ${victim.id}: ${error.message}`, error);
                }
            });
            
            console.log(`Successfully added ${markersAdded} markers to the map`);
            
            // If we have valid markers, fit the map to them
            if (hasValidMarkers) {
                window.map.fitBounds(bounds);
                
                // Set a maximum zoom level to prevent over-zooming
                const listener = google.maps.event.addListener(window.map, 'idle', function() {
                    if (window.map.getZoom() > 15) {
                        window.map.setZoom(15);
                    }
                    google.maps.event.removeListener(listener);
                });
            } else {
                // If no valid markers, center on default location
                window.map.setCenter({ lat: 1.7381, lng: 103.8999 });
                window.map.setZoom(12);
            }
        } catch (error) {
            const errorMsg = `Error loading victim markers: ${error.message}`;
            console.error(errorMsg, error);
            
            // Show error message
            if (mapError) {
                document.getElementById('mapErrorTitle').textContent = 'Ralat';
                document.getElementById('mapErrorMessage').innerHTML = `
                    <p>Terdapat masalah ketika memuat data mangsa. Sila muat semula halaman.</p>
                    <p class="small text-muted mb-0">Ralat: ${error.message}</p>
                `;
                mapError.style.display = 'block';
            }
        } finally {
            // Hide loading state
            if (mapLoading) {
                console.log('Hiding loading state after adding markers');
                mapLoading.style.display = 'none';
            }
        }
    }
    
    // Helper function to get marker icon based on status
    function getMarkerIcon(status) {
        // Create a red marker for all statuses
        return {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: '#FF0000',
            fillOpacity: 0.9,
            strokeWeight: 1,
            strokeColor: '#FFFFFF',
            scale: 10
        };
    }
    
    // Helper function to get status text
    function getStatusText(status) {
        const statusMap = {
            'mohon_bantuan': 'Mohon Bantuan',
            'dalam_tindakan': 'Dalam Tindakan',
            'sedang_diselamatkan': 'Sedang Diselamatkan',
            'bantuan_selesai': 'Bantuan Selesai',
            'tidak_ditemui': 'Tidak Ditemui'
        };
        return statusMap[status] || status;
    }
    
    // Clear all markers from the map
    function clearMarkers() {
        if (!window.markers) return;
        
        // Remove all markers from the map
        for (let i = 0; i < window.markers.length; i++) {
            if (window.markers[i] && window.markers[i].setMap) {
                window.markers[i].setMap(null);
            }
        }
        window.markers = [];
        
        // Close all info windows
        if (window.infoWindows) {
            for (let i = 0; i < window.infoWindows.length; i++) {
                if (window.infoWindows[i] && typeof window.infoWindows[i].close === 'function') {
                    window.infoWindows[i].close();
                }
            }
            window.infoWindows = [];
        }
    }

    // Function to load Google Maps API
    window.loadGoogleMaps = function() {
        return new Promise((resolve, reject) => {
            // Check if Google Maps is already loaded
            if (window.google && window.google.maps) {
                resolve();
                return;
            }

            // Get API key from Laravel config
            const apiKey = '{{ config('services.google.maps.key') }}';
            
            // Check if API key is available
            if (!apiKey) {
                const error = new Error('Google Maps API key is missing');
                console.error(error.message);
                
                // Show error to user
                const mapError = document.getElementById('mapError');
                if (mapError) {
                    document.getElementById('mapErrorTitle').textContent = 'Ralat Konfigurasi';
                    document.getElementById('mapErrorMessage').textContent = 'Kunci API Google Maps tidak ditemui. Sila hubungi pentadbir sistem.';
                    mapError.style.display = 'block';
                }
                
                // Hide loading state if exists
                const mapLoading = document.getElementById('mapLoading');
                if (mapLoading) mapLoading.style.display = 'none';
                
                reject(error);
                return;
            }
            
            // Show loading state
            const mapLoading = document.getElementById('mapLoading');
            if (mapLoading) mapLoading.style.display = 'flex';
            
            // Create script element
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`;
            script.async = true;
            script.defer = true;
            
            // Handle script load success
            script.onload = () => {
                if (window.google && window.google.maps) {
                    // Hide loading state
                    if (mapLoading) mapLoading.style.display = 'none';
                    resolve();
                } else {
                    const error = new Error('Google Maps API failed to load');
                    console.error(error.message);
                    
                    // Show error to user
                    const mapError = document.getElementById('mapError');
                    if (mapError) {
                        document.getElementById('mapErrorTitle').textContent = 'Ralat Peta';
                        document.getElementById('mapErrorMessage').textContent = 'Gagal memuat Google Maps API. Sila muat semula halaman.';
                        mapError.style.display = 'block';
                    }
                    
                    // Hide loading state
                    if (mapLoading) mapLoading.style.display = 'none';
                    
                    reject(error);
                }
            };
            
            // Handle script load error
            script.onerror = (error) => {
                console.error('Error loading Google Maps API:', error);
                
                // Show error to user
                const mapError = document.getElementById('mapError');
                if (mapError) {
                    document.getElementById('mapErrorTitle').textContent = 'Ralat Peta';
                    document.getElementById('mapErrorMessage').textContent = 'Gagal memuat Google Maps API. Sila periksa sambungan internet anda dan muat semula halaman.';
                    mapError.style.display = 'block';
                }
                
                // Hide loading state
                if (mapLoading) mapLoading.style.display = 'none';
                
                reject(error);
            };
            
            // Add script to document
            document.head.appendChild(script);
        });
    };
</script>

<!-- Initialize Application -->
<script>
// Global variables
let map = null;
let markers = [];
let infoWindows = [];
let districtBoundary = null;

// Function to test the API endpoint
async function testVictimsApi() {
    try {
        console.log('Testing /api/victims/active endpoint...');
        const response = await fetch('/api/victims/active');
        const data = await response.json();
        console.log('API Response:', data);
        return { success: true, data };
    } catch (error) {
        console.error('API Test Error:', error);
        return { success: false, error: error.message };
    }
}

// Initialize components
function initializeComponents() {
    console.log('Initializing components...');
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.forEach(function(popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl);
    });
}

// Initialize the map
window.initializeMap = async function() {
    const mapElement = document.getElementById('map');
    const mapLoading = document.getElementById('mapLoading');
    const mapError = document.getElementById('mapError');
    
    if (!mapElement) return;
    
    try {
        // Show loading state
        if (mapLoading) mapLoading.style.display = 'flex';
        if (mapError) mapError.style.display = 'none';
        
        // Load Google Maps API
        await window.loadGoogleMaps();
        
        // Initialize the map
        initMap();
        
        // Load victim markers
        await testVictimsApi();
        
    } catch (error) {
        console.error('Map initialization error:', error);
        if (mapError) {
            document.getElementById('mapErrorTitle').textContent = 'Ralat Peta';
            document.getElementById('mapErrorMessage').textContent = 'Gagal memuat peta. Sila muat semula halaman.';
            mapError.style.display = 'block';
        }
    } finally {
        if (mapLoading) mapLoading.style.display = 'none';
    }
}

// Initialize the application when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', async function() {
    console.log('DOM fully loaded');
    
    // Show loading state for map
    const mapElement = document.getElementById('map');
    const mapLoading = document.getElementById('mapLoading');
    const mapError = document.getElementById('mapError');
    
    if (mapElement) {
        if (mapLoading) mapLoading.style.display = 'flex';
        if (mapError) mapError.style.display = 'none';
    }
    
    // Initialize components
    initializeComponents();
    
    // Initialize refresh button
    const refreshMapBtn = document.getElementById('refreshMapBtn');
    if (refreshMapBtn && !refreshMapBtn._listenerAdded) {
        refreshMapBtn._listenerAdded = true;
        refreshMapBtn.addEventListener('click', async function() {
            try {
                // Show loading state
                if (mapLoading) mapLoading.style.display = 'flex';
                if (mapError) mapError.style.display = 'none';
                
                // Clear existing markers
                clearMarkers();
                
                // Re-initialize the map
                await window.initializeMap();
            } catch (error) {
                console.error('Error refreshing map:', error);
                if (mapError) {
                    document.getElementById('mapErrorTitle').textContent = 'Ralat Peta';
                    document.getElementById('mapErrorMessage').textContent = 'Gagal menyegarkan peta. Sila cuba lagi.';
                    mapError.style.display = 'block';
                }
            } finally {
                if (mapLoading) mapLoading.style.display = 'none';
            }
        });
    }
    
    // Initialize the map
    try {
        await window.initializeMap();
    } catch (error) {
        console.error('Error initializing map:', error);
        if (mapError) {
            document.getElementById('mapErrorTitle').textContent = 'Ralat Peta';
            document.getElementById('mapErrorMessage').textContent = 'Gagal memuat peta. Sila muat semula halaman.';
            mapError.style.display = 'block';
        }
    } finally {
        if (mapLoading) mapLoading.style.display = 'none';
    }
});
    
// No need for immediate loading here - it's handled in the DOMContentLoaded event

// Chatbot functionality
const chatbotContainer = document.getElementById('chatbot-container');
const chatbotToggle = document.getElementById('chatbot-toggle');
const closeChatbot = document.getElementById('close-chatbot');
const chatbotForm = document.getElementById('chatbot-form');
const chatbotInput = document.getElementById('chatbot-input');
const chatbotMessages = document.getElementById('chatbot-messages');

// Add message to chat
function addMessage(text, type) {
    if (!chatbotMessages) return;
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${type}`;
    messageDiv.textContent = text;
    chatbotMessages.appendChild(messageDiv);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

// Initialize chat functionality
function initializeChat() {
    if (chatbotToggle && chatbotContainer) {
        chatbotToggle.addEventListener('click', () => {
            chatbotContainer.classList.toggle('show');
            if (chatbotInput) chatbotInput.focus();
        });
    }

    if (closeChatbot) {
        closeChatbot.addEventListener('click', () => {
            if (chatbotContainer) chatbotContainer.classList.remove('show');
        });
    }

    if (chatbotForm && chatbotInput && chatbotMessages) {
        chatbotForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = chatbotInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage(message, 'user');
            chatbotInput.value = '';

            // Simulate bot response
            setTimeout(() => {
                addMessage('Saya akan membantu anda...', 'bot');
            }, 1000);
        });
    }

    // Handle new rescue case broadcast
    if (typeof Echo !== 'undefined') {
        Echo.channel('rescue-cases')
            .listen('NewRescueCase', (event) => {
                showToast('warning', 'Mohon Bantuan Baru', `
                    <p><strong>Mangsa:</strong> ${event.rescueCase.victim_name}</p>
                    <p><strong>Lokasi:</strong> ${event.rescueCase.lat}, ${event.rescueCase.lng}</p>
                `);
                
                // Auto-show chatbot for new cases
                if (chatbotContainer) {
                    chatbotContainer.classList.add('show');
                    addMessage('Ada permohonan bantuan baru. Saya siap membantu anda.', 'bot');
                }
            });
    }
}

// Initialize chat when DOM is loaded
// Initialize chat when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeChat);
} else {
    initializeChat();
}



// Clear all markers from the map
function clearMarkers() {
    if (!window.markers) return;
    
    // Remove all markers from the map
    for (let i = 0; i < window.markers.length; i++) {
        if (window.markers[i] && window.markers[i].setMap) {
            window.markers[i].setMap(null);
        }
    }
    window.markers = [];
    
    // Close all info windows
    if (window.infoWindows) {
        for (let i = 0; i < window.infoWindows.length; i++) {
            if (window.infoWindows[i]) {
                window.infoWindows[i].close();
            }
        }
        window.infoWindows = [];
    }
}

// Show toast notification
function showToast(type, title, message) {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Auto remove toast after 5 seconds
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 150);
        }
    }, 5000);
}
</script>
@endpush

@endsection