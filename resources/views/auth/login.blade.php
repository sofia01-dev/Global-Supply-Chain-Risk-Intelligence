<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Global Supply Chain</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
        }
        
        .right-content {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
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
            margin-bottom: 40px;
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
            margin-bottom: 24px;
        }

        .input-group-custom .icon-left {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        .input-group-custom .form-control {
            padding: 14px 18px 14px 50px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
            color: #1e293b;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .input-group-custom .form-control:focus {
            border-color: #3b82f6;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        .input-group-custom .form-control:focus + .icon-left,
        .input-group-custom .form-control:focus ~ .icon-left {
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
        }

        .input-group-custom .icon-right:hover {
            color: #3b82f6;
        }

        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 0.85rem;
        }

        .forgot-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }

        .forgot-link:hover {
            color: #2563eb;
            text-decoration: underline;
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
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(37,99,235,0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider::before { margin-right: 15px; }
        .divider::after { margin-left: 15px; }

        .btn-google {
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #334155;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            transition: 0.2s;
        }

        .btn-google:hover {
            background: #f8fafc;
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .register-link a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 992px) {
            .login-left { display: none; }
            .login-right { width: 100%; padding: 40px; }
            .login-wrapper { max-width: 500px; min-height: auto; }
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
                <h3>Selamat Datang Kembali!</h3>
                <p>Masuk untuk melanjutkan ke akun Anda</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <!-- Email -->
                <label class="form-label">Email Address</label>
                <div class="input-group-custom">
                    <i class="bi bi-person icon-left"></i>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Masukkan email Anda" required autofocus>
                    @error('email')
                        <div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <i class="bi bi-lock icon-left"></i>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password Anda" required>
                    <i class="bi bi-eye icon-right" id="togglePassword"></i>
                    @error('password')
                        <div class="text-danger mt-1" style="font-size:0.8rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Options -->
                <div class="options-row justify-content-end">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                    @endif
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>

                <!-- Register -->
                <div class="register-link mt-4">
                    Belum punya akun? <a href="{{ route('register') }}">Registrasi sekarang</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>