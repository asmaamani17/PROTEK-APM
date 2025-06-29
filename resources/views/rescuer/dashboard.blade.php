@extends('layouts.main')
@section('title', 'Agensi Penyelamat')

@section('content')
<!-- Header Section -->
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">Agensi Penyelamat Bertugas</h2>
        </div>
        <div class="badge bg-primary text-white p-2 rounded-pill">
            <i class="fas fa-user-shield me-1"></i>
            {{ auth()->user()->name ?? 'Penyelamat' }}
        </div>
    </div>
    <hr class="mt-2">
</div>

<!-- Quick Action Buttons -->
{{-- Butang Aksi --}}
<div class="row g-3 mb-4 justify-content-center">
  <div class="col-6 col-md-3"><button class="btn btn-primary w-100" onclick="kemaskiniStatus('Terima Tugasan')">✅ Terima Tugasan</button></div>
  <div class="col-6 col-md-3"><button class="btn btn-success w-100" onclick="kemaskiniStatus('Telah Diselamatkan')">🟢 Telah Diselamatkan</button></div>
  <div class="col-6 col-md-3"><button class="btn btn-danger w-100" onclick="kemaskiniStatus('Tidak Ditemui')">❌ Tidak Ditemui</button></div>
  <div class="col-6 col-md-3"><button class="btn btn-warning w-100" onclick="kemaskiniStatus('Tugasan Baru')">🔄 Tugasan Baru</button></div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function kemaskiniStatus(status) {
    const cases = @json($cases);
    let caseToUpdate = null;

    // This logic assumes a rescuer handles one case at a time.
    // 'Terima Tugasan' applies to a 'new' case.
    // Other actions apply to an 'assigned' case.
    if (status === 'Terima Tugasan') {
        caseToUpdate = cases.find(c => c.status === 'new');
    } else if (status === 'Telah Diselamatkan' || status === 'Tidak Ditemui') {
        caseToUpdate = cases.find(c => c.status === 'assigned');
    }

    if (status === 'Tugasan Baru') {
        Swal.fire({
            title: 'Memuatkan Semula',
            text: 'Mendapatkan senarai tugasan terkini...',
            icon: 'info',
            timer: 1500,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            },
            willClose: () => {
                window.location.reload();
            }
        });
        return;
    }
    
    if (!caseToUpdate) {
        Swal.fire('Tiada Tugasan', 'Tiada tugasan yang sesuai untuk tindakan ini.', 'info');
        return;
    }

    let statusText = '';
    let newStatus = '';

    switch(status) {
        case 'Terima Tugasan':
            statusText = 'menerima tugasan';
            newStatus = 'assigned';
            break;
        case 'Telah Diselamatkan':
            statusText = 'menandakan sebagai "Telah Diselamatkan"';
            newStatus = 'completed';
            break;
        case 'Tidak Ditemui':
            statusText = 'menandakan sebagai "Tidak Ditemui"';
            newStatus = 'failed';
            break;
        default:
            Swal.fire('Ralat', 'Tindakan tidak sah.', 'error');
            return;
    }

    Swal.fire({
        title: 'Anda pasti?',
        text: `Anda akan ${statusText} untuk kes mangsa "${caseToUpdate.victim.name}".`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, teruskan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit a form to follow Laravel's conventions
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/rescuer/cases/${caseToUpdate.id}`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = newStatus;
            form.appendChild(statusInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-map-marked-alt me-2"></i>
            Peta Kes Kecemasan
        </h5>
        <div>
            <button class="btn btn-sm btn-light" onclick="refreshMap()">
                <i class="fas fa-sync-alt me-1"></i> Muat Semula
            </button>
        </div>
    </div>
    <div class="card-body p-0 position-relative">
        <div id="main-map" style="height: 500px; width: 100%;"></div>
        <div id="map-loading" class="map-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Memuatkan peta...</span>
            </div>
            <p class="mt-2 text-muted">Memuatkan peta kawasan...</p>
        </div>
    </div>
</div>

@if(count($cases) > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Senarai Kes Kecemasan
                <span class="badge bg-white text-primary ms-2">{{ count($cases) }} Kes Aktif</span>
            </h5>
            <div class="text-white-50 small">
                <i class="fas fa-info-circle me-1"></i> Terkini: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama Mangsa</th>
                        <th>No. Telefon</th>
                        <th>Lokasi</th>
                        <th>Masa Lapor</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cases as $index => $case)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $case->victim->name ?? 'N/A' }}</strong>
                                @if(isset($case->victim->category))
                                <div class="text-muted small">{{ $case->victim->category }}</div>
                                @endif
                            </td>
                            <td>{{ $case->victim->phone_number ?? 'Tiada' }}</td>
                            <td>{{ $case->victim->daerah ?? 'N/A' }}</td>
                            <td>{{ $case->created_at ?? 'N/A' }}</td>
                            <td>
                                @if(isset($case->status))
                                    @if($case->status == 'new')
                                        <span class="badge bg-warning">Menunggu</span>
                                    @elseif($case->status == 'assigned')
                                        <span class="badge bg-info">Dalam Perjalanan</span>
                                    @elseif($case->status == 'rescued')
                                        <span class="badge bg-success">Diselamatkan</span>
                                    @elseif($case->status == 'not_found')
                                        <span class="badge bg-danger">Tidak Ditemui</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $case->status }}</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(isset($case->status))
                                        @if($case->status == 'new')
                                            <form method="POST" action="{{ url('/rescuer/accept/'.$case->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm" title="Terima Tugasan">
                                                    <i class="fas fa-check-circle"></i> Terima
                                                </button>
                                            </form>
                                        @elseif($case->status == 'assigned')
                                            <form method="POST" action="{{ url('/rescuer/complete/'.$case->id) }}" class="d-inline me-1" onsubmit="return confirm('Adakah anda pasti ingin menandakan kes ini sebagai SELESAI?');">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" title="Tandakan Selesai">
                                                    <i class="fas fa-check-circle me-1"></i> Selesai
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ url('/rescuer/status/'.$case->id.'/not_found') }}" class="d-inline me-1" onsubmit="return confirm('Adakah anda pasti mangsa tidak dijumpai?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm" title="Tidak Dijumpai">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button onclick="showCaseDetails({{ $case->id }})" class="btn btn-info btn-sm" title="Butiran Lanjut">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tiada kes kecemasan aktif buat masa ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="card shadow-sm">
        <div class="card-body text-center p-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            </div>
            <h4 class="text-muted mb-3">Tiada Kes Aktif Buat Masa Ini</h4>
            <p class="text-muted mb-4">Sistem tidak mengesan sebarang kes kecemasan yang memerlukan bantuan anda pada masa ini.</p>
            <button class="btn btn-primary" onclick="window.location.reload()">
                <i class="fas fa-sync-alt me-2"></i> Kemas Kini
            </button>
        </div>
    </div>
@endif

  <!-- Status Modal -->
  <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Status Operasi</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-4">
            <div class="status-icon mb-3">
              <i class="fas fa-user-shield fa-4x text-primary"></i>
            </div>
            <h5>Status Semasa: <span class="badge bg-success" id="currentStatus">Aktif</span></h5>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tukar Status:</label>
            <select class="form-select" id="statusSelect">
              <option value="active" selected>Aktif</option>
              <option value="on_mission">Dalam Misi</option>
              <option value="on_break">Rehat</option>
              <option value="off_duty">Tidak Bertugas</option>
            </select>
          </div>
          
          <div class="mb-3" id="statusNoteContainer" style="display: none;">
            <label class="form-label">Catatan Status:</label>
            <textarea class="form-control" id="statusNote" rows="3" placeholder="Sila masukkan butiran status..."></textarea>
          </div>
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <small>Status anda akan dikemaskini dan boleh dilihat oleh pasukan penyelamat yang lain.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-primary" id="updateStatusBtn">Kemaskini Status</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Send Help Modal -->
  <div class="modal fade" id="sendHelpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title"><i class="fas fa-hands-helping me-2"></i>Hantar Bantuan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Pilih jenis bantuan yang diperlukan untuk dihantar ke lokasi kecemasan.
          </div>
          
          <div class="mb-3">
            <label class="form-label">Jenis Bantuan:</label>
            <div class="list-group">
              <label class="list-group-item">
                <input class="form-check-input me-2" type="checkbox" name="helpType" value="ambulance">
                <i class="fas fa-ambulance text-danger me-2"></i> Ambulans
              </label>
              <label class="list-group-item">
                <input class="form-check-input me-2" type="checkbox" name="helpType" value="fire_brigade">
                <i class="fas fa-fire-extinguisher text-danger me-2"></i> Pasukan Bomba
              </label>
              <label class="list-group-item">
                <input class="form-check-input me-2" type="checkbox" name="helpType" value="police">
                <i class="fas fa-shield-alt text-primary me-2"></i> Polis
              </label>
              <label class="list-group-item">
                <input class="form-check-input me-2" type="checkbox" name="helpType" value="rescue_team">
                <i class="fas fa-users text-success me-2"></i> Pasukan Penyelamat
              </label>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Maklumat Tambahan:</label>
            <textarea class="form-control" id="helpDetails" rows="3" placeholder="Sila berikan maklumat lanjut mengenai bantuan yang diperlukan..."></textarea>
          </div>
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Permintaan bantuan akan dihantar ke pusat kawalan untuk tindakan selanjutnya.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-warning" id="sendHelpBtn">Hantar Permintaan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Contact Modal -->
  <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-phone-alt me-2"></i>Hubungi Bantuan Kecemasan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="list-group">
            <a href="tel:999" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="fas fa-phone-volume text-danger me-2"></i>Pusat Kawalan Bencana</h6>
                <span class="badge bg-danger rounded-pill">999</span>
              </div>
              <small class="text-muted">Untuk kecemasan dan bantuan bencana</small>
            </a>
            
            <a href="tel:994" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="fas fa-fire-extinguisher text-danger me-2"></i>Bomba & Penyelamat</h6>
                <span class="badge bg-danger rounded-pill">994</span>
              </div>
              <small class="text-muted">Untuk kebakaran dan penyelamatan</small>
            </a>
            
            <a href="tel:999" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="fas fa-shield-alt text-primary me-2"></i>Polis</h6>
                <span class="badge bg-primary rounded-pill">999</span>
              </div>
              <small class="text-muted">Untuk kecemasan polis</small>
            </a>
            
            <a href="tel:999" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1"><i class="fas fa-ambulance text-danger me-2"></i>Hospital</h6>
                <span class="badge bg-danger rounded-pill">999</span>
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
      </div>
    </div>
  </div>
  
  @push('scripts')
  <script>
  // Status Modal Functionality
  document.getElementById('statusSelect').addEventListener('change', function() {
    const statusNoteContainer = document.getElementById('statusNoteContainer');
    if (this.value !== 'active') {
      statusNoteContainer.style.display = 'block';
    } else {
      statusNoteContainer.style.display = 'none';
    }
  });
  
  document.getElementById('updateStatusBtn').addEventListener('click', function() {
    const status = document.getElementById('statusSelect').value;
    const note = document.getElementById('statusNote').value;
    
    // Show loading state
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengemaskini...';
    
    // Simulate API call
    setTimeout(() => {
      // In a real app, you would make an AJAX call to update the status
      document.getElementById('currentStatus').textContent = 
        document.querySelector('#statusSelect option:checked').textContent;
      
      // Reset button
      btn.disabled = false;
      btn.innerHTML = 'Kemaskini Status';
      
      // Show success message
      alert('Status berjaya dikemaskini!');
      
      // Close modal after 1 second
      setTimeout(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
        modal.hide();
      }, 1000);
    }, 1000);
  });
  
  // Send Help Modal Functionality
  document.getElementById('sendHelpBtn').addEventListener('click', function() {
    const selectedHelp = Array.from(document.querySelectorAll('input[name="helpType"]:checked'))
      .map(el => el.nextElementSibling.textContent.trim())
      .join(', ');
    const details = document.getElementById('helpDetails').value;
    
    if (!selectedHelp) {
      alert('Sila pilih sekurang-kurangnya satu jenis bantuan');
      return;
    }
    
    // Show loading state
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghantar...';
    
    // Simulate API call
    setTimeout(() => {
      // In a real app, you would make an AJAX call to send the help request
      console.log('Help requested:', { types: selectedHelp, details });
      
      // Reset form
      document.querySelectorAll('input[name="helpType"]').forEach(el => el.checked = false);
      document.getElementById('helpDetails').value = '';
      
      // Reset button
      btn.disabled = false;
      btn.innerHTML = 'Hantar Permintaan';
      
      // Show success message
      alert('Permintaan bantuan berjaya dihantar!');
      
      // Close modal after 1 second
      setTimeout(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('sendHelpModal'));
        modal.hide();
      }, 1000);
    }, 1500);
  });
  </script>
  <script>
  function kemaskiniStatus(status) {
      const cases = @json($cases);
      handleCaseAction(status, cases);
  }

  // This logic assumes a rescuer handles one case at a time.
  // 'Terima Tugasan' applies to a 'new' case.
  // Other actions apply to an 'assigned' case.
  function handleCaseAction(status, cases) {
      let caseToUpdate;
      
      if (status === 'Terima Tugasan') {
          caseToUpdate = cases.find(c => c.status === 'new');
      } else if (status === 'Telah Diselamatkan' || status === 'Tidak Ditemui') {
          caseToUpdate = cases.find(c => c.status === 'assigned');
      }

      if (status === 'Tugasan Baru') {
          Swal.fire({
              title: 'Memuatkan Semula',
              text: 'Mendapatkan senarai tugasan terkini...',
              icon: 'info',
              timer: 1500,
              showConfirmButton: false,
              willOpen: () => {
                  Swal.showLoading();
              },
              willClose: () => {
                  window.location.reload();
              }
          });
          return;
      }
      
      if (!caseToUpdate) {
          Swal.fire('Tiada Tugasan', 'Tiada tugasan yang sesuai untuk tindakan ini.', 'info');
          return;
      }

      let statusText = '';
      let newStatus = '';

      switch(status) {
          case 'Terima Tugasan':
              statusText = 'menerima tugasan';
              newStatus = 'assigned';
              break;
          case 'Telah Diselamatkan':
              statusText = 'menandakan sebagai "Telah Diselamatkan"';
              newStatus = 'completed';
              break;
          case 'Tidak Ditemui':
              statusText = 'menandakan sebagai "Tidak Ditemui"';
              newStatus = 'failed';
              break;
          default:
              Swal.fire('Ralat', 'Tindakan tidak sah.', 'error');
              return;
      }

      Swal.fire({
          title: 'Anda pasti?',
          text: `Anda akan ${statusText} untuk kes mangsa "${caseToUpdate.victim.name}".`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Ya, teruskan!',
          cancelButtonText: 'Batal'
      }).then((result) => {
          if (result.isConfirmed) {
              // Create and submit a form to follow Laravel's conventions
              const form = document.createElement('form');
              form.method = 'POST';
              form.action = `/rescuer/cases/${caseToUpdate.id}`; 

              const csrfInput = document.createElement('input');
              csrfInput.type = 'hidden';
              csrfInput.name = '_token';
              csrfInput.value = '{{ csrf_token() }}';
              form.appendChild(csrfInput);

              const methodInput = document.createElement('input');
              methodInput.type = 'hidden';
              methodInput.name = '_method';
              methodInput.value = 'PUT';
              form.appendChild(methodInput);

              const statusInput = document.createElement('input');
              statusInput.type = 'hidden';
              statusInput.name = 'status';
              statusInput.value = newStatus;
              form.appendChild(statusInput);

              document.body.appendChild(form);
              form.submit();
          }
      });
  }

let map, markers = [], infoWindows = [];
let defaultPosition = { lat: {{ $rescuerCoordinates['lat'] ?? 2.6485 }}, lng: {{ $rescuerCoordinates['lng'] ?? 103.8350 }} };
let userPosition = null;

// Initialize the map
function initMap() {
    // Create the map centered on the rescuer's location
    map = new google.maps.Map(document.getElementById('main-map'), {
        zoom: 12,
        center: defaultPosition,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
        styles: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            }
        ]
    });

    // Add markers for each SOS case
    @if(isset($cases) && count($cases) > 0)
        @foreach($cases as $case)
            addCaseMarker(
                {{ $case->id }},
                {{ $case->victim->lat }},
                {{ $case->victim->lng }},
                '{{ $case->victim->name }}',
                '{{ $case->victim->category }}',
                '{{ $case->victim->phone_number ?? 'Tiada' }}',
                '{{ $case->status }}',
                '{{ $case->formatted_created_at ?? date('d/m/Y H:i', strtotime($case->created_at)) }}'
            );
        @endforeach
        // Fit map to show all markers
        fitMapToMarkers();
    @else
        // If no cases, just center on default position
        map.setCenter(defaultPosition);
        map.setZoom(12);
    @endif

    // Try to get the rescuer's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                addRescuerMarker(userPosition.lat, userPosition.lng);
            },
            (error) => {
                console.error('Error getting location:', error);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }
}

// Add a marker for a case
function addCaseMarker(id, lat, lng, name, category, phone, status, reportedAt) {
    const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
    const iconUrl = 'http://maps.google.com/mapfiles/ms/icons/red-dot.png';
    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: name,
        icon: {
            url: iconUrl,
            scaledSize: new google.maps.Size(32, 32)
        }
    });
    const contentString = `
        <div style="min-width: 200px;">
            <h6 class="mb-2" style="color: #0d6efd; font-weight: 600;">${name}</h6>
            <div class="mb-1"><small class="text-muted">Kategori:</small> ${category}</div>
            <div class="mb-1"><small class="text-muted">No. Telefon:</small> ${phone}</div>
            <div class="mb-2"><small class="text-muted">Masa Lapor:</small> ${reportedAt}</div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge ${getStatusBadgeClass(status)}">${getStatusText(status)}</span>
                <button onclick="centerMapOnCase(${lat}, ${lng})" class="btn btn-sm btn-outline-primary btn-sm">
                    <i class="fas fa-location-arrow"></i>
                </button>
            </div>
        </div>
    `;
    const infoWindow = new google.maps.InfoWindow({
        content: contentString
    });
    marker.addListener('click', () => {
        infoWindows.forEach(iw => iw.close());
        infoWindow.open(map, marker);
    });
    markers.push(marker);
    infoWindows.push(infoWindow);
    return marker;
}

function addRescuerMarker(lat, lng) {
    const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: 'Lokasi Anda',
        icon: {
            url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
            scaledSize: new google.maps.Size(32, 32)
        },
        zIndex: 1000
    });
    const infoWindow = new google.maps.InfoWindow({
        content: '<div><strong>Lokasi Anda</strong><br>Anda berada di sini</div>'
    });
    infoWindow.open(map, marker);
    markers.push(marker);
    infoWindows.push(infoWindow);
}

function fitMapToMarkers() {
    const bounds = new google.maps.LatLngBounds();
    let hasMarkers = false;
    markers.forEach(marker => {
        bounds.extend(marker.getPosition());
        hasMarkers = true;
    });
    if (hasMarkers) {
        map.fitBounds(bounds);
        const zoom = map.getZoom();
        if (zoom > 14) {
            map.setZoom(14);
        }
    } else {
        map.setCenter(defaultPosition);
        map.setZoom(12);
    }
}

function centerMapOnCase(lat, lng) {
    const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
    map.setCenter(position);
    map.setZoom(16);
}

function refreshMap() {
    window.location.reload();
}

function showCaseDetails(caseId) {
    alert('Butiran lanjut untuk kes #' + caseId);
}

function getStatusText(status) {
    switch(status) {
        case 'new': return 'Menunggu';
        case 'assigned': return 'Dalam Perjalanan';
        case 'completed': return 'Diselamatkan';
        default: return 'Tidak Ditemui';
    }
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'new': return 'bg-warning';
        case 'assigned': return 'bg-info';
        case 'completed': return 'bg-success';
        default: return 'bg-danger';
    }
}

window.gm_authFailure = function() {
    console.error('Google Maps authentication failed');
    const errorDiv = document.getElementById('map-error');
    errorDiv.style.display = 'block';
    errorDiv.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Ralat!</strong> Gagal memuatkan peta. Sila pastikan kunci API Google Maps anda sah dan diaktifkan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    const mapLoading = document.getElementById('map-loading');
    if (mapLoading) mapLoading.style.display = 'none';
};

document.addEventListener('DOMContentLoaded', function() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        const errorDiv = document.getElementById('map-error');
        errorDiv.style.display = 'block';
        errorDiv.innerHTML = `
            <div class="alert alert-danger">
                <strong>Ralat!</strong> Google Maps API gagal dimuatkan. Sila semak sambungan internet anda.
            </div>
        `;
        return;
    }
});

document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible' && typeof map !== 'undefined') {
        setTimeout(() => {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(map.getCenter());
        }, 300);
    }
});
</script>
<!-- Load Google Maps API with callback to initMap -->
<script async defer 
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&libraries=places">
</script>
@endpush

@endsection