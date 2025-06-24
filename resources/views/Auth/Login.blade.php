<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Projek Protek</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-box {
            max-width: 420px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-top: 5px solid #f96304;
        }
        .form-control:focus {
            border-color: #0463fb;
            box-shadow: 0 0 0 0.2rem rgba(4, 99, 251, 0.25);
        }
        .btn-login {
            background-color: #f96304;
            border: none;
        }
        .btn-login:hover {
            background-color: #e55700;
        }
        .logo-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background-color: #eee;
            border-radius: 50%;
            display: inline-block;
            line-height: 80px;
            font-weight: bold;
            color: #888;
        }
        .alert {
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="login-box bg-white">
            <div class="logo-box">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Projek Protek" class="img-fluid" style="max-width: 100px;">
            </div>
            <h4 class="text-center mb-4" style="color: #0463fb;">Log Masuk</h4>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @if($errors->has('email') || $errors->has('password'))
                            <li>Emel atau kata laluan tidak sah.</li>
                        @else
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Emel</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus
                           placeholder="Masukkan emel">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Kata Laluan</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" 
                           required 
                           placeholder="Masukkan kata laluan">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-login text-white">Log Masuk</button>
                </div>

                <div class="text-end mt-2">
                    <a href="{{ route('password.request') }}" class="text-decoration-none" style="color:#0463fb;">
                        Lupa Kata Laluan?
                    </a>
                </div>
            </form>

            <p class="text-center mt-3">
                Tiada akaun? <a href="{{ route('register') }}" style="color:#0463fb;">Daftar di sini</a>
            </p>
        </div>
    </div>

</body>
</html>

