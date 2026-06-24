<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico?v=3') }}" type="image/x-icon">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png?v=3') }}">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #6366f1 0%, #4f46e5 35%, #0f1123 100%);
            color: #f8fafc;
            padding: 20px;
        }

        .login-container {
            width: min(420px, calc(100vw - 32px));
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            backdrop-filter: blur(18px);
            animation: fadeIn 0.5s ease;
        }

        .login-hero {
            padding: 40px 30px 30px;
            text-align: center;
            background: linear-gradient(180deg, rgba(79, 70, 229, 0.95), rgba(124, 58, 237, 0.9));
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-logo {
            width: 76px;
            height: 76px;
            border-radius: 22px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 2.2rem;
            color: white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .login-hero h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .login-hero p {
            font-size: 0.9rem;
            opacity: 0.85;
            font-weight: 500;
        }

        .login-content {
            padding: 35px 30px 40px;
            background-color: white;
            color: #1e293b;
        }

        .login-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 14px;
            padding: 14px 16px 14px 44px;
            font-size: 0.95rem;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
            background-color: white;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 0.88rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #475569;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
            accent-color: #4f46e5;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 15px;
            font-size: 0.95rem;
            font-weight: 700;
            color: white;
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .btn-client-login {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.88rem;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .btn-client-login:hover {
            color: #7c3aed;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 0.88rem;
            margin-bottom: 20px;
            list-style: none;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-hero">
            <div class="login-logo" style="overflow: hidden; background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25);">
                @if(!empty($profile->foto) && file_exists(public_path('images/' . $profile->foto)))
                    <img src="{{ asset('images/' . $profile->foto) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; padding: 10px;">
                @else
                    <i class="fa-solid fa-user-shield"></i>
                @endif
            </div>
            <h1>{{ $profile->nama_sekolah ?? 'INDOTEL BILLING' }}</h1>
            <p>Portal Administrator & Staff</p>
        </div>

        <div class="login-content">
            <h2 class="login-title">Masuk</h2>
            <p class="login-subtitle">Silakan masukkan username dan password staff Anda.</p>

            @if($errors->any())
                <ul class="alert-danger">
                    @foreach($errors->all() as $error)
                        <li><i class="fa-solid fa-triangle-exclamation" style="margin-right:8px;"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            @if(session('success'))
                <div style="background-color:#f0fdf4; color:#166534; border:1px solid #bbf7d0; border-radius:14px; padding:12px 16px; font-size:0.88rem; margin-bottom:20px;">
                    <i class="fa-solid fa-circle-check" style="margin-right:8px;"></i>{{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required style="padding-right: 44px;">
                        <i class="fa-solid fa-eye-slash toggle-password" id="togglePassword" style="position: absolute; right: 16px; left: auto; cursor: pointer; color: #94a3b8; font-size: 1rem;"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    LOGIN <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <a href="{{ route('login') }}" class="btn-client-login">
                <i class="fa-solid fa-user-large" style="margin-right:6px;"></i> Login sebagai Pelanggan
            </a>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.querySelector('#togglePassword');
            const passwordInput = document.querySelector('#password');

            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
                this.classList.toggle('fa-eye');
            });
        });
    </script>
</body>
</html>
