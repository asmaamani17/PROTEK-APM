@extends('layouts.main')

@section('title', 'Senarai Mangsa')

@push('styles')
<style>
    /* Minimal CSS to keep pagination SVG arrows small and clean */
    .pagination svg {
        width: 1em !important;
        height: 1em !important;
        vertical-align: middle;
    }

    .card {
        border-radius: 1rem;
        box-shadow: 0 4px 16px rgba(0,0,0,0.07), 0 1.5px 3px rgba(0,0,0,0.03);
        border: none;
        margin-top: 1.5rem;
    }
    .table th, .table td {
        vertical-align: middle !important;
        padding: 0.75rem;
    }
    .table thead {
        background-color: #f5f5f5;
    }
    .form-inline .form-control {
        width: auto;
        display: inline-block;
        margin-right: 0.5rem;
    }
    .search-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 1rem;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="container">
    <h2 class="mb-4">Senarai Golongan Rentan</h2>

    <div class="card p-4">
        <form method="GET" action="{{ route('admin.victims') }}" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Cari Nama / IC / No Tel" value="{{ request('search') }}">
            <select name="category" class="form-control">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>NAMA</th>
                        <th>NO. KAD PENGENALAN</th>
                        <th>JANTINA</th>
                        <th>ALAMAT</th>
                        <th>DAERAH</th>
                        <th>PARLIMEN</th>
                        <th>DUN</th>
                        <th>NO. TELEFON</th>
                        <th>KATEGORI KETIDAKUPAYAAN</th>
                        <th>KLIEN</th>
                        <th>STATUS OKU</th>
                        <th>KATEGORI UMUR</th>
                        <th>KOD PARLIMEN & DUN</th>
                        <th>NO. SIRI PRB</th>
                        <th>STATUS PEMASANGAN</th>
                        <th>LATITUD</th>
                        <th>LONGITUD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($victims as $v)
                    <tr>
                        <td>{{ $v->serial_number ?? '-' }}</td>
                        <td>{{ $v->name ?? '-' }}</td>
                        <td>{{ $v->identification_number ?? '-' }}</td>
                        <td>{{ $v->gender ?? '-' }}</td>
                        <td>{{ $v->address ?? '-' }}</td>
                        <td>{{ $v->district ?? '-' }}</td>
                        <td>{{ $v->parliament ?? '-' }}</td>
                        <td>{{ $v->dun ?? '-' }}</td>
                        <td>{{ $v->phone_number ?? '-' }}</td>
                        <td>{{ $v->disability_category ?? '-' }}</td>
                        <td>{{ $v->client_type ?? '-' }}</td>
                        <td>{{ $v->oku_status ?? '-' }}</td>
                        <td>{{ $v->age_group ?? '-' }}</td>
                        <td>{{ $v->parliament_dun_code ?? '-' }}</td>
                        <td>{{ $v->prb_serial_number ?? '-' }}</td>
                        <td>{{ $v->installation_status ?? '-' }}</td>
                        <td>{{ $v->latitude ?? '-' }}</td>
                        <td>{{ $v->longitude ?? '-' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="18" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p class="mb-0">Tiada rekod mangsa dijumpai</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($victims->hasPages())
            <div class="card-footer bg-white">
                @if ($victims->hasPages())
                    <nav>
                        <ul class="pagination">
                            @foreach ($victims->links()->elements[0] as $page => $url)
                                @if ($page == $victims->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        </ul>
                    </nav>
                @endif
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Initialize tooltips if any
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        // Fix Laravel pagination  arrows size
        document.querySelectorAll('.pagination ').forEach(function() {
            svg.removeAttribute('style');
            svg.setAttribute('width', '20');
            svg.setAttribute('height', '20');
            svg.style.width = '20px';
            svg.style.height = '20px';
        });
    });
</script>
@endpush

@endsection