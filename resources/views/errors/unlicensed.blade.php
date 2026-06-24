<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Terkunci - Masukkan Lisensi</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --dark: #0f172a;
            --gray-light: #f8fafc;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #eef2ff 0%, #f1f5f9 100%);
            color: #334155;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
        }

        .card {
            background-color: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08), 0 10px 15px -3px rgba(0, 0, 0, 0.04);
            padding: 40px;
            text-align: center;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: var(--danger);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 24px;
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.1);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        h2 {
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
        }

        p.subtitle {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .alert {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #b91c1c;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 0.88rem;
            font-weight: 500;
            margin-bottom: 24px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 46px;
            font-size: 1rem;
            font-family: inherit;
            font-weight: 500;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            background-color: #f8fafc;
            outline: none;
            transition: all 0.2s;
            color: var(--dark);
        }

        .form-control:focus {
            border-color: #4f46e5;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn {
            width: 100%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 14px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="icon-wrapper">
            <i class="fa-solid fa-lock"></i>
        </div>
        
        <h2>Aplikasi Dinonaktifkan</h2>
        <p class="subtitle">Sistem billing internet terkunci karena masalah lisensi. Silakan masukkan License Key yang valid untuk melanjutkan.</p>

        <!-- Pesan Error -->
        @if(session('license_error') || $errors->has('license_key'))
            <div class="alert">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 1.2rem;"></i>
                <div>
                    {{ session('license_error') ?: $errors->first('license_key') }}
                </div>
            </div>
        @endif

        <form action="{{ route('admin.unlicensed.activate') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="license_key">LICENSE KEY</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-key"></i>
                    <input type="text" name="license_key" id="license_key" class="form-control" placeholder="BILL-XXXX-XXXX-XXXX-XXXX" value="{{ old('license_key', $profile->license_key ?? '') }}" required autofocus autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn">
                <i class="fa-solid fa-unlock-keyhole"></i> Verifikasi & Aktifkan Aplikasi
            </button>
        </form>
    </div>

    <div class="footer">
        Butuh lisensi baru? <a href="mailto:support@yourcenter.com"><i class="fa-solid fa-envelope me-1"></i>Hubungi Administrator</a>
    </div>
</div>

</body>
</html>
