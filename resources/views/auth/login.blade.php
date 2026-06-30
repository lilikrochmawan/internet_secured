<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pelanggan - {{ $profile->nama_sekolah ?? 'INDOTEL' }} Client Area</title>
    <!-- Favicon -->
    @if(!empty($profile->foto) && file_exists(public_path('images/' . $profile->foto)))
        <link rel="shortcut icon" href="{{ asset('images/' . $profile->foto) }}" type="image/x-icon">
        <link rel="icon" type="image/png" href="{{ asset('images/' . $profile->foto) }}">
    @else
        <link rel="shortcut icon" href="{{ asset('favicon.ico?v=3') }}" type="image/x-icon">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png?v=3') }}">
    @endif
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --button-glow: 0 8px 20px rgba(99, 102, 241, 0.35);
            --bg-dark: #080a1c;
            --card-bg: rgba(13, 16, 45, 0.45);
            --border-glow: rgba(255, 255, 255, 0.08);
            --text-gray: #94a3b8;
            --text-light: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glowing Background Blobs */
        .bg-glow {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            filter: blur(140px);
            opacity: 0.35;
            z-index: 1;
            pointer-events: none;
            animation: floatGlow 12s ease-in-out infinite alternate;
        }

        .bg-glow-1 {
            background: #4f46e5;
            top: -100px;
            left: -100px;
        }

        .bg-glow-2 {
            background: #7c3aed;
            bottom: -150px;
            right: -100px;
            animation-delay: -6s;
        }

        @keyframes floatGlow {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(40px) scale(1.15); }
        }

        /* Login Card Container */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            animation: cardEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border-glow);
            border-radius: 28px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            width: 100%;
        }

        /* Header / Brand Area */
        .brand-header {
            padding: 40px 32px 28px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02) 0%, rgba(255, 255, 255, 0) 100%);
        }

        .brand-logo {
            width: 86px;
            height: 86px;
            border-radius: 24px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            animation: pulseGlow 4s infinite ease-in-out;
        }

        .brand-logo img {
            width: 58px;
            height: 58px;
            object-fit: contain;
        }

        @keyframes pulseGlow {
            0%, 100% { border-color: rgba(255, 255, 255, 0.12); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); }
            50% { border-color: rgba(99, 102, 241, 0.4); box-shadow: 0 10px 30px rgba(99, 102, 241, 0.15); }
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: var(--text-light);
            margin-bottom: 6px;
        }

        .brand-subtitle {
            font-size: 0.9rem;
            color: var(--text-gray);
            font-weight: 500;
        }

        /* Content / Form Area */
        .form-content {
            padding: 32px;
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-light);
        }

        .section-desc {
            font-size: 0.88rem;
            color: var(--text-gray);
            margin-bottom: 24px;
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 0.76rem;
            font-weight: 700;
            color: #a5b4fc;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: var(--text-gray);
            font-size: 1rem;
            transition: color 0.2s;
        }

        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 14px 16px 14px 44px;
            font-size: 1rem;
            color: var(--text-light);
            outline: none;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .form-control:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: rgba(255, 255, 255, 0.07);
        }

        .form-control:focus + .input-icon {
            color: #818cf8;
        }

        /* Options row */
        .form-options {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .forgot-link {
            font-size: 0.88rem;
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
            cursor: pointer;
        }

        .forgot-link:hover {
            color: #a5b4fc;
            text-decoration: underline;
        }

        /* Buttons */
        .btn-submit {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 15px;
            font-size: 0.95rem;
            font-weight: 700;
            color: #ffffff;
            background: var(--primary-gradient);
            cursor: pointer;
            box-shadow: var(--button-glow);
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.45);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            background: #312e81;
            box-shadow: none;
            color: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }

        /* Alerts & Errors */
        .alert-box {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 0.88rem;
            color: #fca5a5;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: shakeAlert 0.4s ease;
        }

        @keyframes shakeAlert {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        /* Footer */
        .app-footer {
            margin-top: 28px;
            text-align: center;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        /* Spinner */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Modal dialog - Lupa Akun */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(8, 10, 28, 0.75);
            z-index: 100;
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.2s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-card {
            background: #111430;
            border: 1px solid var(--border-glow);
            border-radius: 24px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-gray);
            font-size: 1.25rem;
            cursor: pointer;
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: var(--text-light);
        }

        .modal-body {
            padding: 24px;
            font-size: 0.92rem;
            line-height: 1.6;
            color: var(--text-gray);
        }

        .modal-body strong {
            color: var(--text-light);
        }

        .btn-wa {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #22c55e;
            color: white;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
            transition: all 0.2s;
        }

        .btn-wa:hover {
            background-color: #16a34a;
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .brand-header {
                padding: 30px 24px 22px;
            }

            .form-content {
                padding: 24px;
            }

            .brand-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glows -->
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>

    <div class="login-wrapper">

        <div class="login-card">

            <!-- Card Header -->
            <div class="brand-header">
                <div class="brand-logo">
                    @if(!empty($profile->foto) && file_exists(public_path('images/' . $profile->foto)))
                        <img src="{{ asset('images/' . $profile->foto) }}" alt="Logo">
                    @else
                        <img src="{{ asset('images/ion.png') }}" alt="Logo">
                    @endif
                </div>
                <div class="brand-title">{{ $profile->nama_sekolah ?? 'INDOTEL' }}</div>
                <div class="brand-subtitle">Sistem Informasi Pelanggan</div>
            </div>

            <!-- Card Body / Form -->
            <div class="form-content">
                <h2 class="section-title">Selamat Datang</h2>
                <p class="section-desc">Silakan login menggunakan nomor telepon Anda</p>

                <!-- ERROR DARI VALIDATION / SESSION -->
                @if($errors->any())
                    @foreach($errors->all() as $error)
                        <div class="alert-box">
                            <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                @elseif(session('error'))
                    <div class="alert-box">
                        <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                    @csrf

                    <div class="form-group">
                        <label for="phone">Nomor Telepon Pelanggan</label>
                        <div class="input-wrapper">
                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                value="{{ old('phone') }}"
                                placeholder="Contoh: 08123456789"
                                class="form-control"
                                autocomplete="tel"
                                required
                            >
                            <i class="fa-solid fa-phone input-icon"></i>
                        </div>
                    </div>

                    <div class="form-options">
                        <a href="javascript:void(0)" class="forgot-link" onclick="openForgotModal()">
                            Lupa Akun / Nomor Telepon?
                        </a>
                    </div>

                    <button type="submit" class="btn-submit" id="btnSubmit">
                        <span class="spinner" id="btnSpinner"></span>
                        <span id="btnText">MASUK SEKARANG</span>
                        <i class="fa-solid fa-arrow-right-to-bracket" id="btnIcon"></i>
                    </button>
                </form>

                <div class="app-footer">
                    &copy; {{ date('Y') }} {{ $profile->nama_sekolah ?? 'BILLING INTERNET' }}. Version 2.0 | By Lotus Computama Teknik. (Dilindungi hak cipta)
                </div>
            </div>

        </div>

    </div>

    <!-- Modal Lupa Akun -->
    <div class="modal-overlay" id="forgotModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Bantuan Login Pelanggan</h3>
                <button class="modal-close" onclick="closeForgotModal()">&times;</button>
            </div>
            <div class="modal-body">
                Jika Anda lupa nomor telepon yang terdaftar pada sistem kami, atau ingin melakukan pembaruan data nomor login, silakan hubungi Customer Service **{{ $profile->nama_sekolah ?? 'Indotel' }}**:
                <br><br>
                @if(!empty($profile->telpon))
                    • WhatsApp / Telepon: **{{ $profile->telpon }}**
                @endif
                @if(!empty($profile->email))
                    <br>• Email Support: **{{ $profile->email }}**
                @endif
                <br>• Kantor: **{{ $profile->alamat }}**
                <br><br>
                Hubungi kami sekarang melalui tautan WhatsApp berikut untuk respon lebih cepat:
                @if(!empty($profile->telpon))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $profile->telpon) }}?text=Halo%20Admin%20{{ urlencode($profile->nama_sekolah ?? 'Indotel') }},%20saya%20lupa%20nomor%20telepon%20pendaftaran%20akun%20saya.%20Mohon%20bantuannya." 
                       target="_blank" class="btn-wa">
                        <i class="fa-brands fa-whatsapp"></i> Hubungi Customer Service
                    </a>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Form submit loading effect
        const loginForm = document.getElementById('loginForm');
        const phoneInput = document.getElementById('phone');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnSpinner = document.getElementById('btnSpinner');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');

        // Allow only digits to be typed
        phoneInput.addEventListener('input', function(e) {
            // Strip any non-digit character
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        loginForm.addEventListener('submit', function(e) {
            btnSubmit.disabled = true;
            btnSpinner.style.display = 'inline-block';
            btnText.innerText = 'Memproses Masuk...';
            btnIcon.style.display = 'none';
        });

        // Modal Controls
        function openForgotModal() {
            document.getElementById('forgotModal').classList.add('active');
        }

        function closeForgotModal() {
            document.getElementById('forgotModal').classList.remove('active');
        }

        // Close modal when clicking outside of modal-card
        document.getElementById('forgotModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeForgotModal();
            }
        });
    </script>

</body>
</html>