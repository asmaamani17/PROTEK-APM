@extends('layouts.main')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>DASHBOARD BILIK GERAKAN</h2>
</div>

  <div class="row g-3">
    @php
        $totalCases = $cases->count();
        $activeCases = $cases->where('status', '!=', 'completed')->count();
        $completedCases = $cases->where('status', 'completed')->count();
        $totalVictims = count($victimsWithCoordinates ?? []);
    @endphp
    
    @foreach([
      ['label' => 'Belum Ambil Tindakan', 'bg' => 'primary', 'count' => $stats['pending'] ?? 0],
      ['label' => 'Sedang Diselamatkan', 'bg' => 'warning', 'count' => $stats['in_progress'] ?? 0],
      ['label' => 'Telah Diselamatkan', 'bg' => 'success', 'count' => $stats['rescued'] ?? 0],
      ['label' => 'Tidak Ditemui', 'bg' => 'danger', 'count' => $stats['not_found'] ?? 0],
      ['label' => 'Kes Selesai', 'bg' => 'dark', 'count' => $stats['completed'] ?? 0],
      ['label' => 'Jumlah Kes', 'bg' => 'secondary', 'count' => $stats['total'] ?? 0],
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
                $status = $victim['status'] ?? 'pending';
                $statusMap = [
                  'pending' => ['Belum Ambil Tindakan','primary'],
                  'in_progress' => ['Sedang Diselamatkan','warning'],
                  'rescued' => ['Diselamatkan','success'],
                  'not_found' => ['Tidak Ditemui','danger'],
                  'completed' => ['Kes Selesai','dark'],
                ];
                $statusText = $statusMap[$status][0] ?? $status;
                $statusClass = 'bg-' . ($statusMap[$status][1] ?? 'secondary');
              @endphp
              <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
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
