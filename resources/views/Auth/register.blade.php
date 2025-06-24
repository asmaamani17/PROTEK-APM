<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Projek Protek</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
        }
        .register-box {
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
        .btn-register {
            background-color: #0463fb;
            border: none;
            width: 100%;
            padding: 10px;
        }
        .btn-register:hover {
            background-color: #034cd1;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="register-box bg-white">
            <div class="logo-box">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Projek Protek" class="img-fluid" style="max-width: 100px;">
            </div>
            <h4 class="text-center mb-4" style="color: #0463fb;">Daftar Akaun</h4>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" 
                           value="{{ old('name') }}" 
                           required 
                           autofocus
                           placeholder="Masukkan nama">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Emel</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
       id="email" name="email"
       value="{{ old('email') }}"
       required
       pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
       title="Sila masukkan emel yang sah"
       placeholder="Masukkan emel">
@error('email')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
                </div>
                
                <div class="mb-3">
                    <label for="no_telefon" class="form-label">No. Telefon</label>
                    <input type="tel" class="form-control @error('no_telefon') is-invalid @enderror"
       id="no_telefon" name="no_telefon"
       value="{{ old('no_telefon') }}"
       required
       pattern="^\d{10,15}$"
       title="No telefon mesti 10-15 digit"
       placeholder="Masukkan no. Telefon">
@error('no_telefon')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Pilih Kategori Pengguna</label>

                            <div class="col-md-12">
                                <select id="role" class="form-control @error('role') is-invalid @enderror" name="role" required>
                                    <option value="">Kategori Pengguna</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Pentadbir Sistem Daerah APM</option>
                                    <option value="victim" {{ old('role') == 'victim' ? 'selected' : '' }}>Golongan Rentan</option>
                                    <option value="rescuer" {{ old('role') == 'rescuer' ? 'selected' : '' }}>Pasukan Penyelamat</option>
                                </select>

                                @error('role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                
                <div class="mb-3">
                    <label for="daerah" class="form-label">Daerah</label>
                    <select id="daerah" class="form-control @error('daerah') is-invalid @enderror" name="daerah" required>
                        <option value="">Pilih Daerah</option>
                        <option value="BATU PAHAT" {{ old('daerah') == 'BATU PAHAT' ? 'selected' : '' }}>Batu Pahat</option>
                        <option value="SEGAMAT" {{ old('daerah') == 'SEGAMAT' ? 'selected' : '' }}>Segamat</option>
                        <option value="KOTA TINGGI" {{ old('daerah') == 'KOTA TINGGI' ? 'selected' : '' }}>Kota Tinggi</option>
                        <option value="KLUANG" {{ old('daerah') == 'KLUANG' ? 'selected' : '' }}>Kluang</option>
                    </select>
                    @error('daerah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Kata Laluan</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
       id="password" name="password"
       required
       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+=\-]).{8,}$"
       title="Min 8 aksara, huruf besar, kecil, nombor & simbol"
       placeholder="Masukkan kata laluan">
@error('password')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
                </div>
                
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Sahkan Kata Laluan</label>
                    <input type="password" class="form-control"
       id="password_confirmation" name="password_confirmation"
       required
       placeholder="Sahkan kata laluan"
       oninput="this.setCustomValidity(this.value !== document.getElementById('password').value ? 'Kata laluan tidak sepadan' : '')">

                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-register text-white">Daftar</button>
                </div>
            </form>

            <p class="text-center mt-3">
                Sudah ada akaun? <a href="{{ route('login') }}" style="color:#0463fb;">Log Masuk</a>
            </p>
        </div>
    </div>

    <script>
        // Client-side password match validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('password_confirmation');
            
            if (form && password && confirmPassword) {
                // Real-time password match validation
                function validatePasswordMatch() {
                    if (confirmPassword.value && password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Kata laluan tidak sepadan');
                        confirmPassword.classList.add('is-invalid');
                    } else {
                        confirmPassword.setCustomValidity('');
                        confirmPassword.classList.remove('is-invalid');
                    }
                }
                
                password.addEventListener('input', validatePasswordMatch);
                confirmPassword.addEventListener('input', validatePasswordMatch);
                
                // Form submission validation
                form.addEventListener('submit', function(e) {
                    if (password.value !== confirmPassword.value) {
                        e.preventDefault();
                        alert('Kata laluan tidak sepadan. Sila pastikan kedua-dua kata laluan adalah sama.');
                        confirmPassword.focus();
                    }
                });
            }
        });
    </script>
</body>
</html>
