@extends('layouts.main')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>DASHBOARD BILIK GERAKAN</h2>
</div>

  <div class="row g-3 mb-4">
    @foreach([
      ['label' => 'Mohon Bantuan', 'bg' => 'danger', 'count' => $stats['pending'] ?? 0, 'icon' => 'exclamation-triangle'],
      ['label' => 'Sedang Diselamatkan', 'bg' => 'warning', 'count' => $stats['in_progress'] ?? 0, 'icon' => 'people-carry'],
      ['label' => 'Telah Diselamatkan', 'bg' => 'success', 'count' => $stats['rescued'] ?? 0, 'icon' => 'user-shield'],
      ['label' => 'Tidak Ditemui', 'bg' => 'secondary', 'count' => $stats['not_found'] ?? 0, 'icon' => 'user-slash'],
      ['label' => 'Kes Selesai', 'bg' => 'dark', 'count' => $stats['completed'] ?? 0, 'icon' => 'check-circle'],
      ['label' => 'Jumlah Kes', 'bg' => 'primary', 'count' => $stats['total'] ?? 0, 'icon' => 'list-ol'],
    ] as $stat)
    <div class="col-6 col-md-4 col-lg-2">
      <div class="p-3 text-white rounded text-center h-100 d-flex flex-column justify-content-center" style="background-color: var(--bs-{{ $stat['bg'] }});">
        <div class="d-flex align-items-center justify-content-center mb-2">
          <i class="fas fa-{{ $stat['icon'] }} me-2"></i>
          <div>{{ $stat['label'] }}</div>
        </div>
        <div class="display-6 fw-bold">{{ $stat['count'] }}</div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="card mt-4">
    <div class="card-header bg-primary text-white">Peta Kedudukan Mangsa</div>
    <div class="card-body p-0">
      <div id="map" class="w-100" style="height: 400px;"></div>
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
            <th>Tindakan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($victims as $victim)
          <tr>
            <td>{{ $victim['serial_number'] ?? '-' }}</td>
            <td>{{ $victim['name'] ?? '-' }}</td>
            <td>{{ $victim['disability_category'] ?? $victim['category'] ?? '-' }}</td>
            <td>
              @php
                $status = $victim['status'] ?? null;
                $statusMap = [
                  'mohon_bantuan' => ['Mohon Bantuan', 'danger', 'exclamation-triangle'],
                  'in_progress' => ['Sedang Diselamatkan', 'warning', 'people-carry'],
                  'rescued' => ['Telah Diselamatkan', 'success', 'user-shield'],
                  'not_found' => ['Tidak Ditemui', 'secondary', 'user-slash'],
                  'completed' => ['Kes Selesai', 'dark', 'check-circle']
                ];
                
                if (!$status) {
                    $statusText = 'Tiada Kes Aktif';
                    $statusClass = 'bg-light text-dark';
                    $statusIcon = 'times';
                } else {
                    $statusText = $statusMap[$status][0] ?? ucfirst(str_replace('_', ' ', $status));
                    $statusClass = 'bg-' . ($statusMap[$status][1] ?? 'secondary');
                    $statusIcon = $statusMap[$status][2] ?? 'info-circle';
                }
              @endphp
              <span class="badge {{ $statusClass }} d-flex align-items-center">
                <i class="fas fa-{{ $statusIcon }} me-1"></i>
                <span>{{ $statusText }}</span>
              </span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center">Tiada rekod mangsa dijumpai</td>
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
                  'available' => ['Sedia', 'success'],
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

@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer></script>
<script>
  function initMap() {
    var defaultCenter = { lat: 2.6485, lng: 103.8350 };
    var victims = @json($victimsWithCoordinates ?? []);
    var map = new google.maps.Map(document.getElementById('map'), {
      zoom: 13,
      center: defaultCenter,
      mapTypeId: 'roadmap',
      streetViewControl: false,
      fullscreenControl: false
    });
    var bounds = new google.maps.LatLngBounds();
    victims.forEach(function(victim) {
      if (victim.lat && victim.lng) {
        var position = { lat: parseFloat(victim.lat), lng: parseFloat(victim.lng) };
        var marker = new google.maps.Marker({
          position: position,
          map: map,
          title: victim.name || 'Mangsa'
        });
        var content = '<strong>' + (victim.name || 'Mangsa') + '</strong><br>' + (victim.disability_category || '') + '<br><span style="font-size:0.9em;color:#888">' + victim.lat + ', ' + victim.lng + '</span>';
        var infoWindow = new google.maps.InfoWindow({ content: content });
        marker.addListener('click', function() {
          infoWindow.open(map, marker);
        });
        bounds.extend(position);
      }
    });
    if (victims.length > 0) {
      map.fitBounds(bounds);
    }
  }
</script>
@endpush
