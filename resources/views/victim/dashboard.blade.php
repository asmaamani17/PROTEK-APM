@extends('layouts.main')
@section('title', 'Senarai Mangsa')
@section('content')
  <div class="mb-4 text-center">
    <!-- Button SOS -->
    <form action="{{ url('/victim/sos') }}" method="POST" id="sosForm" class="d-inline-block mb-3">
      @csrf
      <input type="hidden" name="lat" id="lat">
      <input type="hidden" name="lng" id="lng">
      <input type="hidden" name="victim_id" value="{{ $victim->id ?? '' }}">
      <button id="btn-sos" type="submit" class="btn btn-danger btn-lg px-5 py-3 fw-bold shadow" style="font-size: 2rem; border-radius: 1.5rem;">SOS</button>
    </form>
    <!-- Nama Mangsa -->
    <div class="mt-2 fs-5"><strong>Nama Mangsa:</strong> {{ $victim->name ?? 'N/A' }}</div>
     <!-- Status Bantuan -->
     @php
        // Get the latest case status or default to 'tiada_bantuan'
        $latestCase = $user->rescueCases()->latest()->first();
        $status = $latestCase->status ?? 'tiada_bantuan';
        
        // Status text and styling
        $statusInfo = [
            'tiada_bantuan' => [
                'text' => 'Tiada Bantuan',
                'class' => 'bg-secondary',
                'icon' => 'fa-info-circle',
                'description' => 'Belum ada permintaan bantuan'
            ],
            'mohon_bantuan' => [
                'text' => 'Mohon Bantuan',
                'class' => 'bg-warning',
                'icon' => 'fa-exclamation-triangle',
                'description' => 'Menunggu pengesahan admin'
            ],
            'dalam_tindakan' => [
                'text' => 'Dalam Tindakan',
                'class' => 'bg-primary',
                'icon' => 'fa-people-carry',
                'description' => 'Penyelamat sedang dalam perjalanan'
            ],
            'sedang_diselamatkan' => [
                'text' => 'Sedang Diselamatkan',
                'class' => 'bg-info',
                'icon' => 'fa-ambulance',
                'description' => 'Anda sedang dalam proses penyelamatan'
            ],
            'bantuan_selesai' => [
                'text' => 'Bantuan Selesai',
                'class' => 'bg-success',
                'icon' => 'fa-check-circle',
                'description' => 'Bantuan telah selesai diberikan'
            ],
            'tidak_ditemui' => [
                'text' => 'Tidak Ditemui',
                'class' => 'bg-dark',
                'icon' => 'fa-question-circle',
                'description' => 'Penyelamat tidak dapat menemui lokasi'
            ]
        ][$status] ?? [
            'text' => 'Tiada Bantuan',
            'class' => 'bg-secondary',
            'icon' => 'fa-info-circle',
            'description' => 'Status tidak dikenali'
        ];
    @endphp
    <div class="mt-3">
        <div class="alert alert-{{ str_replace(['bg-'], '', $statusInfo['class']) }} d-flex align-items-center" role="alert">
            <i class="fas {{ $statusInfo['icon'] }} me-2 fs-4"></i>
            <div>
                <h5 class="alert-heading mb-1">Status: {{ $statusInfo['text'] }}</h5>
                <p class="mb-0 small">{{ $statusInfo['description'] }}</p>
                @if($latestCase && $latestCase->rescuers->isNotEmpty())
                    <div class="mt-2">
                        <strong>Penyelamat:</strong>
                        @foreach($latestCase->rescuers as $rescuer)
                            <div class="d-flex align-items-center mt-1">
                                <i class="fas fa-user-shield me-2"></i>
                                <span>{{ $rescuer->user->name }} ({{ $rescuer->user->no_telefon }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
  </div>

  <!-- Peta Lokasi -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white fw-bold">Lokasi Mangsa</div>
    <div class="card-body p-0">
      <div id="map-mangsa" style="height: 350px; width: 100%; border-radius: 0.5rem;"></div>
    </div>
  </div>



  <!-- Chatbot Button (Floating) -->
  <button id="btn-chatbot" class="btn btn-floating d-flex align-items-center justify-content-center shadow" 
    style="background-color: #f76b15; color: white; position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; border-radius: 50%; z-index: 1000; padding: 0;">
    <i class="fas fa-comments fa-lg"></i>
  </button>

  <!-- Emergency Contact Button (Floating) -->
  <button class="btn btn-danger btn-floating d-flex align-items-center justify-content-center shadow" 
    data-bs-toggle="modal" data-bs-target="#contactModal" 
    style="position: fixed; bottom: 90px; right: 20px; width: 60px; height: 60px; border-radius: 50%; z-index: 1000; padding: 0;">
    <i class="fas fa-phone-alt fa-lg"></i>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-white text-danger border border-danger" style="font-size: 0.6rem; padding: 0.25em 0.4em;">
      <i class="fas fa-exclamation"></i>
    </span>
  </button>

  <!-- Contact Modal -->
  <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-phone-alt me-2"></i>Hubungi Bantuan Kecemasan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Gunakan nombor kecemasan dengan bijak.</strong> Hubungi hanya untuk kecemasan sebenar.
          </div>
          
          <div class="list-group">
            <a href="tel:999" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="fas fa-phone-volume text-danger me-2"></i>Pusat Kawalan Bencana</h6>
                <span class="badge bg-danger rounded-pill"><i class="fas fa-phone-alt me-1"></i> 999</span>
              </div>
              <small class="text-muted">Untuk kecemasan dan bantuan bencana</small>
            </a>
            
            <a href="tel:994" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="fas fa-fire-extinguisher text-danger me-2"></i>Bomba & Penyelamat</h6>
                <span class="badge bg-danger rounded-pill"><i class="fas fa-phone-alt me-1"></i> 994</span>
              </div>
              <small class="text-muted">Untuk kebakaran dan penyelamatan</small>
            </a>
            
            <a href="tel:999" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="fas fa-shield-alt text-primary me-2"></i>Polis</h6>
                <span class="badge bg-primary rounded-pill"><i class="fas fa-phone-alt me-1"></i> 999</span>
              </div>
              <small class="text-muted">Untuk kecemasan polis</small>
            </a>
            
            <a href="tel:999" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="fas fa-ambulance text-danger me-2"></i>Hospital</h6>
                <span class="badge bg-danger rounded-pill"><i class="fas fa-phone-alt me-1"></i> 999</span>
              </div>
              <small class="text-muted">Untuk kecemasan perubatan</small>
            </a>
          </div>
          
          <div class="mt-3">
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              <small>Tekan pada nombor untuk terus menghubungi. Pastikan peranti anda menyokong panggilan telefon.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
<style>
  .btn-sos-active {
    animation: pulse 1.5s infinite;
    transform: scale(1.05);
    box-shadow: 0 0 0 10px rgba(220, 53, 69, 0.3);
  }
  @keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 15px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
  }
  .sos-sent {
    background-color: #dc3545 !important;
    color: white !important;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.botpress.cloud/webchat/v3.0/inject.js"></script>
<script src="https://files.bpcontent.cloud/2025/06/22/18/20250622185105-PX8MXXTO.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    // Initialize Pusher
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
        encrypted: true
    });

    // Subscribe to the user's private channel
    const channel = pusher.subscribe('private-victim-{{ auth()->id() }}');

    // Listen for status updates
    channel.bind('RescueCaseStatusUpdated', function(data) {
        console.log('Status updated:', data);
        
        // Show notification
        const statusInfo = getStatusInfo(data.status);
        showToast(`Status bantuan telah dikemas kini: ${statusInfo.text}`, 
                 data.status === 'bantuan_selesai' ? 'success' : 'info');
        
        // Reload the page to show updated status
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    });

    // Helper function to get status info
    function getStatusInfo(status) {
        const statuses = {
            'tiada_bantuan': { text: 'Tiada Bantuan', class: 'secondary' },
            'mohon_bantuan': { text: 'Mohon Bantuan', class: 'warning' },
            'dalam_tindakan': { text: 'Dalam Tindakan', class: 'primary' },
            'sedang_diselamatkan': { text: 'Sedang Diselamatkan', class: 'info' },
            'bantuan_selesai': { text: 'Bantuan Selesai', class: 'success' },
            'tidak_ditemui': { text: 'Tidak Ditemui', class: 'dark' }
        };
        return statuses[status] || { text: 'Status Tidak Dikenali', class: 'secondary' };
    }
</script>
<script>
  // Global map variables
  let map;
  let currentMarker;
  let isSosActive = false;

  // Initialize the map
  function initMap() {
    // Check if map element exists
    if (!document.getElementById('map-mangsa')) {
      console.error('Map container not found');
      return;
    }

    // Victim info from controller
    const victim = @json($victim);
    
    // Default center position (Kota Tinggi)
    const defaultCenter = { lat: 1.7317, lng: 103.8997 }; 
    // Get current location from victim data
    const currentLat = parseFloat(victim?.lat || victim?.latitude || 0);
    const currentLng = parseFloat(victim?.lng || victim?.longitude || 0);
    
    // Determine center position - use current location if available, otherwise use default
    let centerPosition = defaultCenter;
    if (!isNaN(currentLat) && !isNaN(currentLng)) {
      centerPosition = { lat: currentLat, lng: currentLng };
    }

    try {
      // Create map with initial zoom level 6 (more zoomed out)
      map = new google.maps.Map(document.getElementById('map-mangsa'), {
        zoom: 14,
        center: centerPosition,
        mapTypeId: 'roadmap',
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: true,
        zoomControlOptions: {
          position: google.maps.ControlPosition.RIGHT_BOTTOM,
          style: 'SMALL'
        },
        styles: [
          {
            featureType: 'all',
            elementType: 'labels',
            stylers: [{ visibility: 'off' }]
          },
          {
            featureType: 'road',
            elementType: 'geometry',
            stylers: [{ color: '#f5f5f5' }]
          },
          {
            featureType: 'road.local',
            elementType: 'geometry',
            stylers: [{ color: '#ffffff' }]
          },
          {
            featureType: 'water',
            elementType: 'geometry',
            stylers: [{ color: '#e0e0e0' }]
          },
          {
            featureType: 'landscape',
            elementType: 'geometry',
            stylers: [{ color: '#f9f9f9' }]
          },
          {
            featureType: 'poi',
            elementType: 'geometry',
            stylers: [{ visibility: 'off' }]
          }
        ]
      });

      // Add current location marker if available
      if (!isNaN(currentLat) && !isNaN(currentLng)) {
        // Create marker for current location
        const currentMarker = new google.maps.Marker({
          position: { lat: currentLat, lng: currentLng },
          map: map,
          title: (victim.name || 'Mangsa') + ' (Kategori: ' + victim.disability_category + ')',
          icon: {
            url: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"%3E%3Cpath d="M24 4c-7.73 0-14 6.27-14 14 0 10.5 14 26 14 26s14-15.5 14-26c0-7.73-6.27-14-14-14zm0 7c3.86 0 7 3.14 7 7s-3.14 7-7 7-7-3.14-7-7 3.14-7 7-7z" fill="%23ea4335"/%3E%3Ccircle cx="24" cy="18" r="5" fill="%23ffffff"/%3E%3C/svg%3E',
            scaledSize: new google.maps.Size(40, 40),
            anchor: new google.maps.Point(20, 40)
          },
          animation: google.maps.Animation.DROP
        });
        
        const infoWindowCurrent = new google.maps.InfoWindow({
          content: `<b>${victim.name || 'Mangsa'}</b><br>ID: ${victim.id}<br>Kategori: ${victim.disability_category}<br>${currentLat.toFixed(6)}, ${currentLng.toFixed(6)}`
        });
        
        currentMarker.addListener('click', () => {
          infoWindowCurrent.setPosition({ lat: currentLat, lng: currentLng });
          infoWindowCurrent.open(map);
        });
        infoWindowCurrent.setPosition({ lat: currentLat, lng: currentLng });
        infoWindowCurrent.open(map);
        

      }

      // Set map center to the current location with the defined zoom level
      if (!isNaN(currentLat) && !isNaN(currentLng)) {
        map.setCenter({ lat: currentLat, lng: currentLng });
      }

    } catch (error) {
      console.error('Map initialization error:', error);
      alert('Error initializing map: ' + error.message);
      
      // Show error message in place of map
      const mapElement = document.getElementById('map-mangsa');
      if (mapElement) {
        mapElement.innerHTML = `
          <div style="padding: 20px; text-align: center; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
            <h4>Error loading map</h4>
            <p>Please check your internet connection and make sure you have a valid Google Maps API key with billing enabled.</p>
          </div>
        `;
      }
    }
  }

  // Error handling for Google Maps API
  window.gm_authFailure = function() {
    alert('Google Maps API error: Please check your API key and billing status.');
    
    // Show error message in place of map
    const mapElement = document.getElementById('map-mangsa');
    if (mapElement) {
      mapElement.innerHTML = `
        <div style="padding: 20px; text-align: center; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
          <h4>Google Maps API Error</h4>
          <p>Please check your API key and billing status in Google Cloud Console.</p>
        </div>
      `;
    }
  };

  // Add toast container to the page
  const toastContainer = document.createElement('div');
  toastContainer.id = 'toast-container';
  toastContainer.className = 'position-fixed top-0 end-0 p-3';
  toastContainer.style.zIndex = '1100';
  document.body.appendChild(toastContainer);

  // Function to show toast
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast show ${type === 'error' ? 'bg-danger' : 'bg-success'} text-white mb-2`;
    toast.role = 'alert';
    toast.style.minWidth = '300px';
    
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          <i class="fas ${type === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle'} me-2"></i>
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    
    document.getElementById('toast-container').appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
      toast.remove();
    }, 5000);
    
    return toast;
  }

  // Handle form submission
  document.addEventListener('DOMContentLoaded', function() {
    const sosButton = document.getElementById('btn-sos');
    const sosForm = document.getElementById('sosForm');
    
    if (sosButton && sosForm) {
      // Handle form submission
      sosForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Prevent multiple clicks
        if (isSosActive) return;
        
        try {
          // Visual feedback for SOS activation
          isSosActive = true;
          sosButton.classList.add('btn-sos-active', 'sos-sent');
          const originalText = sosButton.innerHTML;
          sosButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menghantar SOS...';
          
          // Get current location with timeout and high accuracy
          const position = await new Promise((resolve, reject) => {
            if (navigator.geolocation) {
              const options = {
                enableHighAccuracy: true,  // Request high accuracy
                timeout: 10000,           // 10 seconds timeout
                maximumAge: 0             // Force fresh location
              };
              
              navigator.geolocation.getCurrentPosition(
                (position) => {
                  console.log('Got position:', position);
                  resolve({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy
                  });
                },
                (error) => {
                  console.error('Geolocation error:', error);
                  let errorMessage = 'Gagal mendapatkan lokasi';
                  switch(error.code) {
                    case error.PERMISSION_DENIED:
                      errorMessage = 'Akses lokasi ditolak. Sila benarkan akses lokasi untuk aplikasi ini.';
                      break;
                    case error.POSITION_UNAVAILABLE:
                      errorMessage = 'Maklumat lokasi tidak tersedia. Sila pastikan GPS dihidupkan.';
                      break;
                    case error.TIMEOUT:
                      errorMessage = 'Masa untuk mendapatkan lokasi telah tamat. Sila cuba lagi.';
                      break;
                  }
                  reject(new Error(errorMessage));
                },
                options
              );
            } else {
              reject(new Error('Pelayar anda tidak menyokong geolokasi'));
            }
          });

          // Update form with location data
          document.getElementById('lat').value = position.lat;
          document.getElementById('lng').value = position.lng;
          
          // Show sending message
          sosButton.innerHTML = '<i class="fas fa-check-circle me-2"></i> SOS Dihantar!';
          
          // Get CSRF token from meta tag or form input
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                           document.querySelector('input[name="_token"]')?.value;
          
          if (!csrfToken) {
            console.error('CSRF token not found');
            showToast('Ralat sistem. Sila muat semula halaman.', 'error');
            return;
          }
          
          // Submit the form via fetch to handle the response
          const formData = new FormData(sosForm);
          
          fetch(sosForm.action, {
            method: 'POST',
            body: formData,
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': csrfToken
            }
          })
          .then(response => {
            if (!response.ok) {
              return response.json().then(err => { throw err; });
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              showToast(data.message || 'Permintaan bantuan berjaya dihantar!', 'success');
              
              // Redirect to case status if case_id is provided
              if (data.case_id) {
                window.location.href = `/victim/case/${data.case_id}`;
              }
            } else {
              showToast(data.message || 'Ralat menghantar permintaan bantuan. Sila cuba lagi.', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            const errorMessage = error.message || 'Ralat menghantar permintaan bantuan. Sila cuba lagi.';
            showToast(errorMessage, 'error');
          })
          .finally(() => {
            // Reset button state after 2 seconds
            setTimeout(() => {
              sosButton.innerHTML = originalText;
              sosButton.classList.remove('sos-sent', 'btn-sos-active');
              isSosActive = false;
            }, 2000);
          });
          
        } catch (error) {
          console.error('Error getting location:', error);
          
          // Show error state
          sosButton.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> Ralat! Cuba Lagi';
          sosButton.classList.remove('btn-sos-active');
          sosButton.classList.add('btn-danger');
          
          // Reset button after delay
          setTimeout(() => {
            sosButton.innerHTML = originalText;
            sosButton.classList.remove('sos-sent');
            isSosActive = false;
          }, 2000);
          
          // Show specific error message
          console.error('Location error details:', error);
          showToast(error.message || 'Gagal mendapatkan lokasi. Sila pastikan GPS dihidupkan dan akses lokasi dibenarkan.', 'error');
          
          // Reset button state immediately
          sosButton.innerHTML = originalText;
          sosButton.classList.remove('sos-sent', 'btn-sos-active');
          isSosActive = false;
        }
      });
    }
  });
</script>

<script>
// Initialize Google Maps API if not already loaded
if (!window.googleMapsAPI) {
    window.googleMapsAPI = new Promise((resolve) => {
        if (window.google && window.google.maps) {
            resolve(window.google.maps);
            return;
        }
        
        // Store the original initMap if it exists
        const originalInitMap = window.initMap;
        
        window.initMap = function() {
            if (originalInitMap) originalInitMap();
            resolve(window.google.maps);
        };
        
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&libraries=marker,places`;
        script.async = true;
        script.defer = true;
        script.onerror = () => {
            console.error('Failed to load Google Maps API');
            resolve(null);
        };
        
        document.head.appendChild(script);
    });
}

// Initialize the map when the API is ready
window.googleMapsAPI.then((googleMaps) => {
    if (!googleMaps) {
        console.error('Google Maps API failed to load');
        return;
    }
    
    // Call the original initMap function if it exists
    if (typeof initMap === 'function') {
        initMap();
    }
});
</script>
@endpush

@push('styles')
<style>
  .btn-sos {
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 80px;
    height: 80px;
    font-size: 1.2em;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    transition: background-color 0.3s;
  }
  .btn-sos:hover {
    background-color: #c82333;
  }
  
  .btn-floating {
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
  }
  
  .btn-floating:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
  }
</style>
@endpush