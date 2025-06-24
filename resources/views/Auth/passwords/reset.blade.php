@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Semula Kata Laluan - Projek Protek</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            width: 100%;
            padding: 10px;
        }
        .btn-login:hover {
            background-color: #e55700;
        }
        .logo-box {
            text-align: center;
            margin-bottom: 20px;
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
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box bg-white">
            <h4 class="text-center mb-4" style="color: #0463fb;">Set Semula Kata Laluan</h4>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <div class="mb-3">
                    <label for="email" class="form-label">Emel</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" 
                           value="{{ $email ?? old('email') }}" 
                           required 
                           autofocus
                           placeholder="Masukkan emel anda">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Kata Laluan Baru</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" 
                           required 
                           autocomplete="new-password"
                           placeholder="Masukkan kata laluan baru">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password-confirm" class="form-label">Sahkan Kata Laluan</label>
                    <input type="password" class="form-control" 
                           id="password-confirm" name="password_confirmation" 
                           required 
                           autocomplete="new-password"
                           placeholder="Masukkan semula kata laluan">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login text-white">
                        Set Semula Kata Laluan
                    </button>
                </div>

                <div class="back-to-login">
                    <a href="{{ route('login') }}" class="text-decoration-none">
                        Kembali ke Log Masuk
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
@endsection
