<!DOCTYPE html>
<html>
<head>
    <title>PROTEK SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="app-url" content="{{ config('app.url') }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    
    <style>
        body { 
            padding-top: 60px; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        #btn-sos {
            font-size: 1.5rem;
            padding: 0.75rem 2rem;
            border-radius: 2rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        #btn-sos:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">🛡️ PROTEK SYSTEM</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <span class="nav-link">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                        </li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

    {{-- Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    

    
    <!-- Laravel Echo and Pusher -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.min.js"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    
    <script>
        // Initialize Pusher and Echo with configuration from .env
        window.Pusher = Pusher;
        
        // Only initialize Echo if we have the required configuration
        @if(env('PUSHER_APP_KEY') && env('PUSHER_APP_CLUSTER'))
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            wsHost: window.location.hostname,
            wsPort: 6001,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Socket-ID': window.Echo ? window.Echo.socketId() : null
                }
            }
        });
        
        console.log('Echo initialized with Pusher');
        @else
        console.warn('Pusher configuration is missing. Real-time features will be disabled.');
        @endif
        
        // Global error handler
        window.addEventListener('error', function(event) {
            console.error('Global error:', event.error);
            
            // Show user-friendly error message
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat',
                    text: 'Terdapat masalah teknikal. Sila muat semula halaman.',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Ralat: ' + (event.message || 'Terdapat masalah teknikal. Sila muat semula halaman.'));
            }
        });
        
        // Unhandled promise rejections
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled rejection:', event.reason);
        });
    </script>
    
    @yield('scripts')
</body>
</html>
