@extends('layouts.main')
@section('title', 'Senarai Mangsa')
@section('content')
  <div class="mb-4 text-center">
    <!-- Button SOS -->
    <form action="{{ url('/victim/sos') }}" method="POST" id="sosForm" class="d-inline-block mb-3">
      @csrf
      <input type="hidden" name="lat" id="lat">
      <input type="hidden" name="lng" id="lng">
      <button id="btn-sos" type="submit" class="btn btn-danger btn-lg px-5 py-3 fw-bold shadow" style="font-size: 2rem; border-radius: 1.5rem;">SOS</button>
    </form>
    <!-- Nama Mangsa -->
    <div class="mt-2 fs-5"><strong>Nama Mangsa:</strong> {{ $user->name ?? 'Nama Mangsa' }}</div>
    
    <!-- Status Bantuan -->
    @php
        $status = $user->status ?? 'mohon_bantuan'; // Default status if not set
        $statusText = [
            'mohon_bantuan' => 'Mohon Bantuan',
            'dalam_tindakan' => 'Dalam Tindakan',
            'bantuan_selesai' => 'Bantuan Selesai'
        ][$status] ?? 'Status Tidak Diketahui';
        
        $statusClass = [
            'mohon_bantuan' => 'bg-warning',
            'dalam_tindakan' => 'bg-primary',
            'bantuan_selesai' => 'bg-success'
        ][$status] ?? 'bg-secondary';
    @endphp
    <div class="mt-2">
        <span class="badge {{ $statusClass }} text-white fs-6 p-2">
            <i class="fas fa-info-circle me-1"></i> Status: {{ $statusText }}
        </span>
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
                <span class="badge bg-danger rounded-pill"><i class="fas fa-phone-alt me-1"></i> 999</span>
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

@push('scripts')
<script src="https://cdn.botpress.cloud/webchat/v3.0/inject.js"></script>
<script src="https://files.bpcontent.cloud/2025/06/22/18/20250622185105-PX8MXXTO.js"></script>
<script>
  // Initialize chatbot
  window.botpress.init({
    "botId": "690f8920-8ec6-4626-aa50-bb256d1b9da2",
    "hideWidget": true, // Hide the default launcher
    "configuration": {
      "version": "v1",
      "botName": "AkakPROTEK",
      "botAvatar": "https://files.bpcontent.cloud/2025/06/23/00/20250623001049-OJ0WLQ8J.jpeg",
      "color": "#f76b15",
      "variant": "solid",
      "headerVariant": "solid",
      "themeMode": "light",
      "fontFamily": "inter",
      "radius": 4,
      "feedbackEnabled": false,
      "footer": "[⚡ by Botpress](https://botpress.com/?from=webchat)"
    },
    "clientId": "7067c78f-b738-4cbd-b2d7-ceca8fce2d43"
  });

  // Add event listener to custom button to open chatbot
  document.addEventListener('DOMContentLoaded', function() {
    const chatbotButton = document.getElementById('btn-chatbot');
    if (chatbotButton) {
      chatbotButton.addEventListener('click', function() {
        window.botpress.open();
      });
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

@push('scripts')
<script>
  // Google Map and markers
  let map;
  let currentMarker;
  let profileMarker;

  function initMap() {
    // Victim info
    const victim = @json($victims)[0];
    // Current location (from SOS/RescueCase)
    const currentLat = parseFloat(victim.lat || victim.latitude);
    const currentLng = parseFloat(victim.lng || victim.longitude);
    // Profile location (from vulnerable_groups)
    const profileLat = parseFloat(victim.profile_lat || victim.profile_latitude || victim.latitude);
    const profileLng = parseFloat(victim.profile_lng || victim.profile_longitude || victim.longitude);
    
    // Default to current location if available, else profile
    const centerPosition = !isNaN(currentLat) && !isNaN(currentLng)
      ? { lat: currentLat, lng: currentLng }
      : { lat: profileLat, lng: profileLng };

    // Initialize map
    map = new google.maps.Map(document.getElementById('map-mangsa'), {
      center: centerPosition,
      zoom: 14
    });

    // Place current location marker (red)
    if (!isNaN(currentLat) && !isNaN(currentLng)) {
      currentMarker = new google.maps.Marker({
        position: { lat: currentLat, lng: currentLng },
        map: map,
        title: (victim.name || 'Mangsa') + ' (Lokasi Semasa)',
        icon: {
          url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
          scaledSize: new google.maps.Size(32, 32)
        }
      });
      const infoWindowCurrent = new google.maps.InfoWindow({
        content: `<b>${victim.name || 'Mangsa'}</b><br>Lokasi Semasa (SOS):<br>${currentLat.toFixed(6)}, ${currentLng.toFixed(6)}`
      });
      currentMarker.addListener('click', () => infoWindowCurrent.open(map, currentMarker));
      infoWindowCurrent.open(map, currentMarker);
    }

    // Place profile location marker (blue)
    if (!isNaN(profileLat) && !isNaN(profileLng)) {
      profileMarker = new google.maps.Marker({
        position: { lat: profileLat, lng: profileLng },
        map: map,
        title: (victim.name || 'Mangsa') + ' (Alamat Profil)',
        icon: {
          url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
          scaledSize: new google.maps.Size(32, 32)
        }
      });
      const infoWindowProfile = new google.maps.InfoWindow({
        content: `<b>${victim.name || 'Mangsa'}</b><br>Alamat Profil (vulnerable_groups):<br>${profileLat.toFixed(6)}, ${profileLng.toFixed(6)}`
      });
      profileMarker.addListener('click', () => infoWindowProfile.open(map, profileMarker));
    }

    // Fit map to show both markers
    const bounds = new google.maps.LatLngBounds();
    if (!isNaN(currentLat) && !isNaN(currentLng)) bounds.extend({ lat: currentLat, lng: currentLng });
    if (!isNaN(profileLat) && !isNaN(profileLng)) bounds.extend({ lat: profileLat, lng: profileLng });
    if (!bounds.isEmpty()) map.fitBounds(bounds);
  }
  let defaultPosition = { lat: {{ $coordinates['lat'] ?? 2.6485 }}, lng: {{ $coordinates['lng'] ?? 103.8350 }} };
  let userPosition = null;
  
  // Victims data from controller
  const victims = @json($victims);
  
  // Error handling for Google Maps API
  window.gm_authFailure = function() {
    alert('Google Maps API error: Please check your API key and billing status.');
  };

  // Initialize the map
  function initMap() {
    try {
      // Create map centered on default position
      map = new google.maps.Map(document.getElementById('map-mangsa'), {
        zoom: 12,
        center: defaultPosition,
        mapTypeId: 'roadmap',
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true,
        zoomControl: true,
        zoomControlOptions: {
          position: google.maps.ControlPosition.RIGHT_BOTTOM
        },
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

      // Add victim markers to the map
      addVictimMarkers();

      // Try to get user's current location
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function(position) {
            userPosition = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            
            // Update hidden form fields with current location
            document.getElementById('lat').value = userPosition.lat;
            document.getElementById('lng').value = userPosition.lng;
            
            // Add/update user location marker
            updateUserLocationMarker();
            
            // Center map to show both user and victim if available
            centerMapOnLocations();
          },
          function(error) {
            console.error('Geolocation error:', error);
            handleLocationError();
          },
          {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
          }
        );
      } else {
        // Browser doesn't support geolocation
        console.log('Geolocation is not supported by this browser');
        handleLocationError();
      }
    } catch (error) {
      console.error('Map initialization error:', error);
      document.getElementById('map-mangsa').innerHTML = `
        <div style="padding: 20px; text-align: center; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
          <h4>Error loading map</h4>
          <p>Please check your internet connection and make sure you have a valid Google Maps API key with billing enabled.</p>
        </div>
      `;
    }
  }
  
  // Add or update user location marker
  function updateUserLocationMarker() {
    if (!userPosition) return;
    
    if (userMarker) {
      userMarker.setPosition(userPosition);
    } else {
      userMarker = new google.maps.Marker({
        position: userPosition,
        map: map,
        title: 'Lokasi Semasa: {{ $user->name ?? "Anda" }}',
        icon: {
          url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
          scaledSize: new google.maps.Size(30, 30)
        }
      });
      
      // Add info window for user location
      const infoWindow = new google.maps.InfoWindow({
        content: `
          <div style="padding: 10px;">
            <h6 style="margin: 0 0 5px 0; color: #d9534f;">Lokasi Semasa</h6>
            <p style="margin: 0;">{{ $user->name ?? "Anda" }}</p>
            <p style="margin: 5px 0 0 0; font-size: 0.8em; color: #666;">
              ${userPosition.lat.toFixed(6)}, ${userPosition.lng.toFixed(6)}
            </p>
          </div>
        `
      });
      
      userMarker.addListener('click', function() {
        infoWindow.open(map, userMarker);
      });
      
      // Open info window by default
      infoWindow.open(map, userMarker);
    }
  }
  
  // Center map to show both user and victim locations
  function centerMapOnLocations() {
    const bounds = new google.maps.LatLngBounds();
    let hasLocations = false;
    
    // Add victim location if available
    if (victims && victims.length > 0) {
      const victim = victims[0];
      const lat = victim.lat || victim.latitude;
      const lng = victim.lng || victim.longitude;
      
      if (lat && lng) {
        bounds.extend({
          lat: parseFloat(lat),
          lng: parseFloat(lng)
        });
        hasLocations = true;
        console.log('Added victim location to bounds:', { lat, lng });
      } else {
        console.error('Invalid victim coordinates in centerMapOnLocations:', victim);
      }
    }
    
    // Add user location if available
    if (userPosition) {
      bounds.extend(userPosition);
      hasLocations = true;
    }
    
    // Fit bounds if we have valid locations
    if (hasLocations) {
      map.fitBounds(bounds);
      
      // Don't zoom in too far if points are close
      const zoom = map.getZoom();
      if (zoom > 14) {
        map.setZoom(14);
      }
    } else {
      // Fallback to default position
      map.setCenter(defaultPosition);
      map.setZoom(10);
    }
  }
  
  // Add marker for the specific victim from the database
  function addVictimMarkers() {
    console.log('Victims data:', victims);
    
    if (!victims || victims.length === 0) {
      console.log('No victim data found in the database');
      return;
    }
    
    // Get the first (and only) victim from the array
    const victim = victims[0];
    
    // Defensive: parse and validate coordinates
    const lat = parseFloat(victim.lat || victim.latitude);
    const lng = parseFloat(victim.lng || victim.longitude);
    console.log('Victim object:', victim);
    console.log('Parsed lat/lng:', lat, lng);
    
    if (isNaN(lat) || isNaN(lng)) {
      console.error('Invalid victim coordinates:', victim);
      return;
    }
    
    const victimPosition = { lat, lng };
    
    console.log('Adding marker for victim:', victim.name || 'Unnamed', 'at', victimPosition);
    
    // Create marker for the victim
    const marker = new google.maps.Marker({
      position: victimPosition,
      map: map,
      title: `${victim.name || 'Mangsa'} (${victim.disability_category || 'Tiada maklumat'})`,
      icon: {
        url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
        scaledSize: new google.maps.Size(30, 30)
      }
    });
    
    // Create info window content
    const infoContent = `
      <div style="padding: 10px; min-width: 200px;">
        <h6 style="margin: 0 0 5px 0; color: #0d6efd;">${victim.name || 'N/A'}</h6>
        <p style="margin: 0 0 5px 0; font-size: 0.9em;">
          <strong>No. Telefon:</strong> ${victim.phone_number || 'Tiada'}<br>
          <strong>Kategori:</strong> ${victim.disability_category || 'Tiada maklumat'}
        </p>
        <p style="margin: 5px 0 0 0; font-size: 0.8em; color: #666;">
          ${victimPosition.lat.toFixed(6)}, ${victimPosition.lng.toFixed(6)}
        </p>
      </div>
    `;
    
    const infoWindow = new google.maps.InfoWindow({
      content: infoContent
    });
    
    // Show info window on marker click
    marker.addListener('click', () => {
      if (window.currentInfoWindow) {
        window.currentInfoWindow.close();
      }
      infoWindow.open(map, marker);
      window.currentInfoWindow = infoWindow;
    });
    
    // Open info window by default
    infoWindow.open(map, marker);
    window.currentInfoWindow = infoWindow;
  }
  
  function handleLocationError() {
    // If we have a victim, center on their location
    if (victims && victims.length > 0 && victims[0].lat && victims[0].lng) {
      const victimPosition = {
        lat: parseFloat(victims[0].lat),
        lng: parseFloat(victims[0].lng)
      };
      map.setCenter(victimPosition);
      map.setZoom(14);
      console.log('Centered on victim location due to geolocation error');
    } else {
      // Fallback to default position
      map.setCenter(defaultPosition);
      map.setZoom(10);
      console.log('Using default position - no valid victim location');
    }
    
    // Clear any existing user marker since we couldn't get the user's location
    if (userMarker) userMarker.setMap(null);
    userMarker = null;
  }

  // Handle SOS form submission
  document.addEventListener('DOMContentLoaded', function() {
    const sosForm = document.getElementById('sosForm');
    if (sosForm) {
      sosForm.addEventListener('submit', function(e) {
        if (!confirm('Adakah anda pasti ingin menghantar isyarat kecemasan?')) {
          e.preventDefault();
        } else if (!userPosition) {
          alert('Tidak dapat menentukan lokasi anda. Pastikan GPS dihidupkan.');
          e.preventDefault();
        }
      });
    }
  });
</script>
<script async defer
  src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap">
</script>
@endpush