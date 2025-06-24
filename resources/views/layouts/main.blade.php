<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>@yield('title') – Projek Protek</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  <style>
        body { 
            font-family: 'Segoe UI', sans-serif;  
            background: #f2f2f2; 
            padding-top: 60px;
        }
        #map {
            height: 400px;
            width: 100%;
        }

        @media (max-width: 768px) {
            #map {
                height: 300px;
            }
        }

        #map-mangsa,
        #map-penyelamat {
            height: 400px;
            width: 100%;
        }

        @media (max-width: 768px) {
            #map-mangsa,
            #map-penyelamat {
                height: 300px;
            }
        }

        .sidebar { 
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background-color: #0463fb;
            transition: left 0.3s ease;
            z-index: 999;
            padding-top: 60px;
        }
        
        .sidebar.active {
            left: 0;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            padding: 0;
        }

        .sidebar a { 
            color: white; 
            padding: 12px 20px; 
            display: block; 
            text-decoration: none; 
            transition: background-color 0.3s;
        }
        
        .sidebar a:hover, .sidebar a.active { 
            background: #f96304; 
            text-decoration: none;
        }
        
        .logo { 
            display: block; 
            margin: 20px auto; 
            width: 140px; 
            height: auto;
            padding: 0 20px;
        }
        
        .center-box { 
            max-width: 400px; 
            margin: 80px auto; 
            padding: 20px; 
            background: #fff; 
            border-radius: 8px; 
        }

        .btn-sos {
            background-color: #dc3545;
            color: white;
            font-size: 24px;
            font-weight: bold;
            padding: 20px 50px;
            border: none;
            border-radius: 12px;
            display: block;
            margin: 20px auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: 0.3s ease;
        }

        .btn-sos:hover {
            background-color: #c82333;
            transform: scale(1.05);
            color: white;
        }
        
        #map, #map-mangsa, #map-penyelamat { 
            width: 100%; 
            border-radius: 8px; 
            margin-bottom: 20px;
        }
        
        #map { 
            height: 350px; 
        }
        
        #map-mangsa, #map-penyelamat { 
            height: 300px; 
        }

        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            background-color: #0463fb;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 20px;
            cursor: pointer;
            z-index: 1000;
            border-radius: 4px;
        }

        #main-content {
            transition: margin-left 0.3s ease;
            margin-left: 0;
            padding: 20px;
            width: 100%;
        }

        .sidebar.active ~ #main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        @media (max-width: 768px) {
            .sidebar.active ~ #main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .sidebar {
                left: -250px;
            }
            
            .sidebar.active {
                left: 0;
                width: 80%;
            }
        }
  </style>
</head>
<body>
  <nav id="sidebar" class="sidebar" role="navigation" aria-label="Main navigation">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">

    @auth
        @if(auth()->user()->role == 'admin')
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.victims') }}" class="{{ request()->is('admin/victims*') ? 'active' : '' }}">Senarai Mangsa</a>
        @elseif(auth()->user()->role == 'victim')
            <a href="{{ route('victim.dashboard') }}" class="{{ request()->is('victim*') ? 'active' : '' }}">Dashboard</a>
        @elseif(auth()->user()->role == 'rescuer')
            <a href="{{ route('rescuer.dashboard') }}" class="{{ request()->is('rescuer*') ? 'active' : '' }}">Dashboard</a>
        @else
            <a href="{{ route('home') }}" class="{{ request()->is('home') ? 'active' : '' }}">Home</a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-danger w-100 mt-3" style="border-radius: 0; text-align: left; padding: 12px 20px;">Log Keluar</button>
        </form>
    @endauth
  </nav>

  <main id="main-content" class="p-4">
    <!-- Sidebar Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    {{-- Flash messages --}}
    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    }

    // Close sidebar on link click (mobile)
    document.querySelectorAll('.sidebar a').forEach(function(link) {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
          document.getElementById('sidebar').classList.remove('active');
        }
      });
    });

    // Close sidebar when clicking outside (mobile)
    document.addEventListener('click', function(event) {
      const sidebar = document.getElementById('sidebar');
      const toggleBtn = document.querySelector('.toggle-btn');
      if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
        if (window.innerWidth <= 768) {
          sidebar.classList.remove('active');
        }
      }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
      const sidebar = document.getElementById('sidebar');
      if (window.innerWidth > 768) {
        sidebar.classList.add('active');
      } else {
        sidebar.classList.remove('active');
      }
    });

    // Initialize sidebar state based on screen size
    document.addEventListener('DOMContentLoaded', function() {
      if (window.innerWidth > 768) {
        document.getElementById('sidebar').classList.add('active');
      }
    });

    // General form validation
    document.addEventListener('DOMContentLoaded', function () {
      const forms = document.querySelectorAll('.needs-validation');
      Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    });
  </script>
    
    @stack('scripts')
</body>
</html>
