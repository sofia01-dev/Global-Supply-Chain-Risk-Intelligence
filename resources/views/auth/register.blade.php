<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Global Supply Chain</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Left Side */
        .login-left {
            background-image: url('{{ asset("images/auth/login-bg-v2.png") }}');
            background-size: cover;
            background-position: center bottom;
            position: relative;
            color: #fff;
            padding: 100px 80px 50px 80px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .login-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(10, 37, 88, 0.9) 0%, rgba(10, 37, 88, 0.4) 100%);
            z-index: 1;
        }

        .left-content {
            position: relative;
            z-index: 2;
            max-width: 460px;
            margin: 0;
        }

        .logo-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .logo-icon {
            font-size: 2.5rem;
            color: #4da3ff;
        }

        .logo-text h2 {
            font-weight: 700;
            margin: 0;
            font-size: 2rem;
            letter-spacing: -0.5px;
        }

        .logo-text span {
            font-size: 1.15rem;
            color: #8bb9ff;
            font-weight: 500;
        }

        .description {
            font-size: 1rem;
            line-height: 1.6;
            color: #e0e8f5;
            margin-bottom: 50px;
        }

        .feature-item {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: flex-start;
        }

        .feature-icon {
            background: rgba(255,255,255,0.1);
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.15);
        }

        .feature-text h6 {
            margin: 0 0 6px 0;
            font-weight: 600;
            font-size: 1rem;
        }

        .feature-text p {
            margin: 0;
            font-size: 0.85rem;
            color: #b5cbf0;
            line-height: 1.5;
        }

        /* Right Side */
        .login-right {
            padding: 40px;
            position: relative;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }
        
        .right-content {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            padding: 40px 0;
        }


        .login-title h3 {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            font-size: 1.85rem;
            letter-spacing: -0.5px;
        }

        .login-title p {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group-custom .icon-left {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            transition: 0.3s;
            pointer-events: none;
            z-index: 10;
        }

        .input-group-custom .form-control,
        .input-group-custom .form-select {
            padding: 14px 18px 14px 50px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
            color: #1e293b;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            font-weight: 500;
            appearance: none;
        }

        .input-group-custom .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px 12px;
        }

        .input-group-custom .form-control:focus,
        .input-group-custom .form-select:focus {
            border-color: #3b82f6;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: 0;
        }

        .input-group-custom .form-control:focus + .icon-left,
        .input-group-custom .form-control:focus ~ .icon-left,
        .input-group-custom .form-select:focus + .icon-left,
        .input-group-custom .form-select:focus ~ .icon-left {
            color: #3b82f6;
        }

        .input-group-custom .icon-right {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            transition: 0.2s;
            z-index: 10;
        }

        .input-group-custom .icon-right:hover {
            color: #3b82f6;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            color: #ffffff;
            box-shadow: 0 4px 14px 0 rgba(37,99,235,0.39);
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(37,99,235,0.5);
            color: #fff;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .register-link a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
        }

        .register-link a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            .login-left { display: none !important; }
            .login-right { width: 100% !important; padding: 20px; }
            .right-content { padding: 20px 0; }
        }
    </style>
</head>
<body>

<div class="d-flex w-100 min-vh-100 m-0 p-0">
    <!-- Left Column -->
    <div class="login-left col-lg-7 d-none d-lg-flex">
        <div class="left-content">
            <div class="logo-header">
                <i class="bi bi-globe-americas logo-icon"></i>
                <div class="logo-text">
                    <h2>Global Supply Chain</h2>
                    <span>Risk Intelligence Platform</span>
                </div>
            </div>
            
            <p class="description">
                Pantau risiko, analisis data, dan ambil keputusan lebih cerdas untuk rantai pasok global yang lebih aman dan efisien.
            </p>

            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <div class="feature-text">
                    <h6>Monitor Risiko Global</h6>
                    <p>Pantau berbagai risiko dari seluruh dunia secara real-time.</p>
                </div>
            </div>

            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="feature-text">
                    <h6>Analisis & Prediksi</h6>
                    <p>Dapatkan insight berbasis data untuk keputusan yang lebih tepat.</p>
                </div>
            </div>

            <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-globe2"></i></div>
                <div class="feature-text">
                    <h6>Informasi Terintegrasi</h6>
                    <p>Semua informasi penting dalam satu platform terintegrasi.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="login-right col-12 col-lg-5">
        <div class="right-content">

            <div class="login-title">
                <h3>Buat Akun Baru</h3>
                <p>Silakan lengkapi formulir pendaftaran di bawah ini</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <!-- Full Name -->
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group-custom">
                    <i class="bi bi-person-badge icon-left"></i>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required autofocus>
                    @error('name')
                        <div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Country Select -->
                <label class="form-label">Negara / Wilayah Operasi</label>
                <div class="input-group-custom">
                    <i class="bi bi-geo-alt icon-left"></i>
                    <select name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Negara --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')
                        <div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <label class="form-label">Email Address</label>
                <div class="input-group-custom">
                    <i class="bi bi-envelope icon-left"></i>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Masukkan email aktif" required>
                    @error('email')
                        <div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <label class="form-label">Password (Min. 8 Karakter)</label>
                <div class="input-group-custom">
                    <i class="bi bi-lock icon-left"></i>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Buat kata sandi" required>
                    <i class="bi bi-eye icon-right" id="togglePassword"></i>
                    @error('password')
                        <div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group-custom">
                    <i class="bi bi-shield-lock icon-left"></i>
                    <input type="password" name="password_confirmation" id="password_confirm" class="form-control" placeholder="Ketik ulang kata sandi" required>
                    <i class="bi bi-eye icon-right" id="togglePasswordConfirm"></i>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-person-plus"></i> Daftar Sekarang
                </button>
                
                <!-- Register -->
                <div class="register-link">
                    Sudah memiliki akun? <a href="{{ route('login') }}">Masuk di sini</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Toggle Password
    document.querySelector('#togglePassword').addEventListener('click', function (e) {
        const password = document.querySelector('#password');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });

    // Toggle Confirm Password
    document.querySelector('#togglePasswordConfirm').addEventListener('click', function (e) {
        const passwordConfirm = document.querySelector('#password_confirm');
        const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirm.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>