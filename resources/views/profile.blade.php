<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pelanggan</title>
    <!-- Import Google Fonts Inter & FontAwesome Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            color-scheme: dark;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, rgba(79, 70, 229, .22), transparent 25%),
                        radial-gradient(circle at right, rgba(59, 130, 246, .14), transparent 15%),
                        linear-gradient(180deg, #0b1124 0%, #090d1d 100%);
            color: #e5e7eb;
        }
        .page {
            width: min(650px, 100%);
            margin: 0 auto;
            padding: 32px 24px;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 32px;
        }
        .topbar h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 30%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .topbar a {
            color: #94a3b8;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .topbar a:hover {
            color: #a5b4fc;
        }
        
        .card {
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 32px;
            backdrop-filter: blur(16px);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        /* Avatar styling */
        .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
            display: grid;
            place-items: center;
            color: #ffffff;
            font-size: 2.5rem;
            border: 4px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.25);
        }

        .profile-title-section {
            text-align: center;
            margin-top: -8px;
        }
        .profile-name-main {
            font-size: 1.45rem;
            font-weight: 700;
            color: #f8fafc;
            margin: 0 0 6px 0;
        }
        .profile-username-sub {
            font-size: 0.9rem;
            color: #a5b4fc;
            margin: 0;
            font-weight: 500;
            background: rgba(99, 102, 241, 0.1);
            padding: 4px 12px;
            border-radius: 12px;
            display: inline-block;
        }

        /* Info rows styling */
        .info-list {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .info-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            gap: 20px;
        }
        .info-label-section {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .info-label-section i {
            font-size: 1.1rem;
            color: #818cf8;
            width: 20px;
            text-align: center;
        }
        .info-value-section {
            font-size: 1rem;
            font-weight: 600;
            color: #f1f5f9;
            text-align: right;
        }

        /* Status colors */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .status-aktif {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        .status-terisolir {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Logout button */
        .logout-btn-container {
            width: 100%;
            margin-top: 10px;
        }
        .logout-btn {
            width: 100%;
            border: 1px solid rgba(239, 68, 68, 0.35);
            background: rgba(239, 68, 68, 0.08);
            color: #f87171;
            padding: 16px 20px;
            border-radius: 18px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.1s ease;
        }
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.16);
            border-color: rgba(239, 68, 68, 0.5);
        }
        .logout-btn:active {
            transform: scale(0.98);
        }

        /* Mobile adjustments */
        @media (max-width: 600px) {
            .page {
                padding: 20px 16px;
            }
            .topbar {
                margin-bottom: 24px;
            }
            .card {
                padding: 24px 20px;
                border-radius: 24px;
                gap: 20px;
            }
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 2.2rem;
            }
            .profile-name-main {
                font-size: 1.25rem;
            }
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                padding: 14px 16px;
                border-radius: 16px;
            }
            .info-value-section {
                text-align: left;
                width: 100%;
                font-size: 0.95rem;
                padding-left: 32px;
            }
            .logout-btn {
                padding: 14px;
                border-radius: 14px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <h1>Profil Pelanggan</h1>
            <a href="{{ route('dashboard') }}"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </div>

        <div class="card">
            <!-- Profile Avatar -->
            <div class="profile-avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <!-- Profile Title -->
            <div class="profile-title-section">
                <h2 class="profile-name-main">{{ $pelanggan->nama_pelanggan }}</h2>
                <span class="profile-username-sub">Kode: {{ $pelanggan->kode_pelanggan }}</span>
            </div>

            <!-- Info List -->
            <div class="info-list">
                <!-- 1. Nama Pelanggan -->
                <div class="info-item">
                    <div class="info-label-section">
                        <i class="fa-solid fa-address-card"></i>
                        <span>Nama Lengkap</span>
                    </div>
                    <div class="info-value-section">{{ $pelanggan->nama_pelanggan }}</div>
                </div>

                <!-- 2. Alamat Pelanggan -->
                <div class="info-item">
                    <div class="info-label-section">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>Alamat Lengkap</span>
                    </div>
                    <div class="info-value-section">{{ $pelanggan->alamat ?: '-' }}</div>
                </div>

                <!-- 3. Nomor Telepon -->
                <div class="info-item">
                    <div class="info-label-section">
                        <i class="fa-solid fa-phone"></i>
                        <span>Nomor Telepon</span>
                    </div>
                    <div class="info-value-section">{{ $pelanggan->no_telp ?: '-' }}</div>
                </div>

                <!-- 4. Paket Yang Diambil -->
                <div class="info-item">
                    <div class="info-label-section">
                        <i class="fa-solid fa-box-archive"></i>
                        <span>Paket Terlanggan</span>
                    </div>
                    <div class="info-value-section">
                        {{ optional($pelanggan->paketDetail)->nama_paket ?? 'Paket ' . $pelanggan->paket }}
                    </div>
                </div>

                <!-- 5. Status Paket -->
                <div class="info-item">
                    <div class="info-label-section">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Status Layanan</span>
                    </div>
                    <div class="info-value-section">
                        @if($statusPaket === 'Aktif')
                            <span class="status-badge status-aktif">
                                <i class="fa-solid fa-circle-check"></i> Aktif
                            </span>
                        @else
                            <span class="status-badge status-terisolir">
                                <i class="fa-solid fa-circle-exclamation"></i> Terisolir (Isolir)
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Logout Button -->
            <div class="logout-btn-container">
                <button type="button" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar / Log Out
                </button>
            </div>
        </div>

        @include('partials.bottom-nav')
    </div>
</body>
</html>
