<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - {{ $profile->nama_sekolah ?? 'Indotel Billing' }}</title>
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
            color-scheme: dark;
            --bg-color: #0b1124;
            --card-bg: rgba(15, 23, 42, 0.75);
            --border-color: rgba(148, 163, 184, 0.12);
            --primary-gradient: linear-gradient(90deg, #6366f1, #7c3aed);
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #eab308;
            --text-light: #f8fafc;
            --text-gray: #cbd5e1;
            --text-dim: #94a3b8;
            --font-stack: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-stack);
            background: radial-gradient(circle at top, rgba(79, 70, 229, .22), transparent 25%),
                        radial-gradient(circle at right, rgba(59, 130, 246, .14), transparent 15%),
                        linear-gradient(180deg, #0b1124 0%, #090d1d 100%);
            color: var(--text-light);
            min-height: 100vh;
            padding-bottom: 80px; /* Space for bottom nav on mobile */
        }

        .page {
            width: min(1120px, 100%);
            margin: 0 auto;
            padding: 24px;
        }

        /* Topbar Header */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 14px 24px;
            backdrop-filter: blur(16px);
            margin-bottom: 24px;
        }

        /* Brand logo on Topbar */
        .brand-logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-light);
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
        }

        .brand-logo-wrapper {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.12);
            overflow: hidden;
        }

        .brand-logo-wrapper img {
            width: 28px;
            height: 28px;
            object-fit: contain;
        }

        .brand-logo-container .brand-name {
            font-family: 'Outfit', sans-serif;
            text-transform: uppercase;
            background: linear-gradient(90deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Desktop Nav inside Topbar */
        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-item {
            text-decoration: none;
            color: var(--text-dim);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-item.active {
            color: #f8fafc;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.25);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            font-size: 0.95rem;
            cursor: pointer;
            position: relative;
            transition: background 0.2s ease;
            text-decoration: none;
        }

        .topbar-icon:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .topcard-icon--notify {
            animation: bell-swing 1.8s ease-in-out infinite;
            transform-origin: 50% 0%;
        }

        @keyframes bell-swing {
            0%, 100% { transform: rotate(0deg); }
            12% { transform: rotate(14deg); }
            24% { transform: rotate(-12deg); }
            36% { transform: rotate(10deg); }
            48% { transform: rotate(-8deg); }
            60% { transform: rotate(5deg); }
            72% { transform: rotate(-3deg); }
        }

        .notif-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--danger-color);
            border: 1px solid #0f172a;
        }

        /* Welcome Text */
        .welcome-section {
            margin-bottom: 24px;
        }

        .welcome-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-dim);
            font-weight: 700;
        }

        .welcome-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-light);
            margin-top: 4px;
        }

        /* Content Layout Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 24px;
        }

        .left-column {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .right-column {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 24px;
            backdrop-filter: blur(16px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Card 1: Client Card Details */
        .client-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .client-info-wrapper {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .client-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: grid;
            place-items: center;
            color: white;
            font-weight: 800;
            font-size: 1.4rem;
        }

        .client-meta h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .client-meta p {
            font-size: 0.85rem;
            color: var(--text-dim);
            margin-top: 2px;
        }

        .card-action-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.06);
            display: grid;
            place-items: center;
            color: #818cf8;
            text-decoration: none;
            transition: all 0.2s;
        }

        .card-action-icon:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        /* Inset Inner Panel matching Mockup */
        .client-inner-panel {
            background: rgba(0, 0, 0, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 20px;
        }

        .connection-status-text {
            font-size: 0.88rem;
            color: var(--text-gray);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot--success { background-color: var(--success-color); box-shadow: 0 0 8px var(--success-color); }
        .status-dot--danger { background-color: var(--danger-color); box-shadow: 0 0 8px var(--danger-color); }
        .status-dot--warning { background-color: var(--warning-color); box-shadow: 0 0 8px var(--warning-color); }
        .status-dot--loading { background-color: #cbd5e1; box-shadow: 0 0 8px #cbd5e1; }

        .package-info-line {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .package-name-label {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-light);
        }

        .badge {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        .badge-warning {
            background: rgba(234, 179, 8, 0.15);
            color: var(--warning-color);
            border: 1px solid rgba(234, 179, 8, 0.25);
        }

        /* Chips row */
        .chips-row {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .chip-badge {
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 8px;
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        /* Due Dates info */
        .due-dates-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 0.88rem;
            color: var(--text-dim);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 14px;
        }

        .date-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .date-line strong {
            color: var(--text-light);
            font-weight: 700;
        }

        /* Big Status Button */
        .status-button-link {
            text-decoration: none;
            width: 100%;
            display: block;
        }

        .status-button {
            width: 100%;
            border-radius: 16px;
            border: none;
            padding: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .status-button--paid {
            background-color: var(--success-color);
            color: white;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
            cursor: default;
        }

        .status-button--unpaid {
            background-color: #6366f1;
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        .status-button--unpaid:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        /* Card 2: Usage Card details */
        .card-title-with-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .usage-item {
            margin-bottom: 16px;
        }

        .usage-item-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.88rem;
            color: var(--text-gray);
            margin-bottom: 6px;
        }

        .usage-item-header span {
            font-weight: 700;
            color: var(--text-light);
        }

        .progress-bar-container {
            height: 8px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            transition: width 1s ease-in-out;
            border-radius: 4px;
        }

        .progress-bar-fill--download {
            background: linear-gradient(90deg, #3b82f6, #6366f1);
        }

        .progress-bar-fill--upload {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .duration-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 9999px; /* Capsule pill shape matching mockup */
            padding: 12px 20px;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--text-gray);
        }

        .duration-box span {
            font-weight: 700;
            color: var(--text-light);
        }

        /* Card 3: Invoice History Card */
        .invoice-history-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 380px;
            overflow-y: auto;
        }

        .invoice-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 14px 16px;
        }

        .invoice-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .invoice-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.1);
            color: #818cf8;
            display: grid;
            place-items: center;
            font-size: 1rem;
        }

        .invoice-id {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .invoice-date {
            font-size: 0.78rem;
            color: var(--text-dim);
            margin-top: 2px;
        }

        .invoice-item-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .invoice-amount {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-light);
        }

        .status-badge {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .status-badge-paid {
            background: rgba(16, 185, 129, 0.12);
            color: var(--success-color);
        }

        .status-badge-unpaid {
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger-color);
        }

        .no-invoices {
            text-align: left;
            color: var(--text-dim);
            font-size: 0.9rem;
            padding: 14px 20px;
            border: 1px solid var(--border-color);
            border-radius: 9999px; /* Pill-shaped container matching mockup */
            background: rgba(0, 0, 0, 0.15);
        }

        /* Quick Action Cards Row */
        .actions-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .action-item-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 16px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.2s;
            text-align: center;
        }

        .action-item-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }

        .action-item-card span {
            font-size: 1.25rem;
        }

        .action-item-card p {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-gray);
        }

        /* Announcement Info Modal */
        .info-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, .72);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .info-modal-overlay.is-open {
            display: flex;
        }

        .info-modal {
            width: min(500px, 100%);
            background: linear-gradient(180deg, #1e293b, #0f172a);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: modalSlide 0.2s ease-out;
        }

        @keyframes modalSlide {
            from { transform: translateY(-16px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .info-modal h3 {
            margin-bottom: 12px;
            font-size: 1.2rem;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-modal p {
            color: var(--text-gray);
            line-height: 1.6;
            white-space: pre-wrap;
            font-size: 0.95rem;
        }

        .info-modal-actions {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
        }

        .info-modal-close {
            padding: 10px 20px;
            border-radius: 12px;
            border: none;
            background: var(--primary-gradient);
            color: white;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.25);
        }

        /* Bottom Tab Navigation Bar for Mobile */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.9);
            border-top: 1px solid var(--border-color);
            backdrop-filter: blur(20px);
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 999;
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 600;
            flex: 1;
            transition: color 0.2s;
        }

        .bottom-nav-item i {
            font-size: 1.25rem;
        }

        .bottom-nav-item.active {
            color: #6366f1;
        }

        /* Footer styling */
        .footer {
            margin-top: 40px;
            text-align: center;
            color: var(--text-dim);
            font-size: 0.8rem;
            padding-bottom: 20px;
        }

        /* Desktop vs Mobile Media Queries */
        @media (min-width: 769px) {
            .bottom-nav {
                display: none; /* Hide bottom nav on desktop */
            }
        }

        @media (max-width: 768px) {
            .topbar-nav {
                display: none; /* Hide topbar nav links on mobile */
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .page {
                padding: 16px 12px;
            }
            .topbar {
                padding: 12px 16px;
            }
            .actions-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Topbar Header -->
        <div class="topbar">
            <!-- Brand Logo matching Login Page wrapper style -->
            <a href="{{ route('dashboard') }}" class="brand-logo-container">
                <div class="brand-logo-wrapper">
                    @if(!empty($profile->foto) && file_exists(public_path('images/' . $profile->foto)))
                        <img src="{{ asset('images/' . $profile->foto) }}" alt="Logo">
                    @else
                        <img src="{{ asset('images/ion.png') }}" alt="Logo">
                    @endif
                </div>
                <div style="display: flex; flex-direction: column; min-width: 0;">
                    <span class="brand-name" style="font-size: 1.1rem; line-height: 1.2;">{{ $profile->nama_sekolah ?? 'LOTUS COMPUTAMA TEKNIK' }}</span>
                </div>
            </a>

            <!-- Desktop Navigation Links (Indonesian) -->
            <div class="topbar-nav">
                <a href="{{ route('dashboard') }}" class="nav-item active">Dashboard</a>
                <a href="{{ route('network.status') }}" class="nav-item">Status</a>
                <a href="{{ route('keluhan.index') }}" class="nav-item">Tiket</a>
            </div>

            <!-- Topbar Icons/Actions -->
            <div class="topbar-actions">
                <!-- Announcement Icon -->
                <button type="button" class="topbar-icon {{ ($hasInformasi ?? false) ? 'topcard-icon--notify' : '' }}" id="btnInformasi" title="Informasi" aria-label="Buka informasi">
                    <i class="fa-solid fa-bell"></i>
                    @if($hasInformasi ?? false)
                        <span class="notif-badge" aria-hidden="true"></span>
                    @endif
                </button>
                <!-- Logout Button -->

                <button type="button" class="topbar-icon" style="color: var(--danger-color); border-color: rgba(239, 68, 68, 0.25);" onclick="document.getElementById('logout-form').submit()" title="Keluar">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </div>
        </div>

        <!-- Announcement Information Modal -->
        <div class="info-modal-overlay" id="infoModal" role="dialog" aria-modal="true" aria-labelledby="infoModalTitle">
            <div class="info-modal">
                <h3 id="infoModalTitle">
                    <i class="fa-solid fa-circle-info" style="color:#6366f1;"></i> 
                    {{ $informasi->judul_informasi ?? 'Informasi' }}
                </h3>
                <p>{{ $informasi->isi_informasi ?? 'Belum ada informasi terbaru.' }}</p>
                <div class="info-modal-actions">
                    <button type="button" class="info-modal-close" id="btnCloseInformasi">Tutup</button>
                </div>
            </div>
        </div>

        <!-- Dynamic Welcome Greetings in Indonesian -->
        <div class="welcome-section">
            <p class="welcome-label">SELAMAT DATANG,</p>
            <h1 class="welcome-title">
                @php
                    $hour = date('H');
                    if ($hour >= 5 && $hour < 12) {
                        $greeting = 'Selamat Pagi 🌅';
                    } elseif ($hour >= 12 && $hour < 17) {
                        $greeting = 'Selamat Siang ☀️';
                    } elseif ($hour >= 17 && $hour < 20) {
                        $greeting = 'Selamat Sore 🌇';
                    } else {
                        $greeting = 'Selamat Malam 🌃';
                    }
                @endphp
                {{ $greeting }}
            </h1>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="dashboard-grid">
            <!-- Left Column: Profile Card and Live Bandwidth Usage -->
            <div class="left-column">
                <!-- Card 1: Client Card -->
                <div class="card">
                    <div class="client-header">
                        <div class="client-info-wrapper">
                            <div class="client-avatar">
                                {{ substr($pelanggan->nama_pelanggan ?? $user->nama_user ?? 'C', 0, 1) }}
                            </div>
                            <div class="client-meta">
                                <h3>{{ $pelanggan->nama_pelanggan ?? $user->nama_user }}</h3>
                                <p>{{ $pelanggan->alamat ?? '-' }}</p>
                            </div>
                        </div>
                        <!-- Secondary Action Icon: Document icon matching mockup top-right -->
                        <a href="{{ route('network.status') }}" class="card-action-icon" title="Status Jaringan & WiFi">
                            <i class="fa-regular fa-file-lines"></i>
                        </a>
                    </div>

                    <!-- Client Inset Inner Panel matching Mockup layout -->
                    <div class="client-inner-panel">
                        <!-- Dynamic Connection status based on Router states / logic -->
                        <p class="connection-status-text" id="connection-status">
                            <span class="status-dot status-dot--loading"></span> Memeriksa status...
                        </p>
                        
                        <!-- Package Details -->
                        <div class="package-info-line">
                            <span class="package-name-label">
                                Paket {{ optional($paket)->nama_paket ?? 'Internet' }}
                            </span>
                            @if($tagihanTotal > 0)
                                <span class="badge badge-danger" id="package-status-badge">Isolir</span>
                            @else
                                <span class="badge badge-success" id="package-status-badge">Aktif</span>
                            @endif
                        </div>

                        <!-- Prabayar & Perpanjangan Chips badges -->
                        <div class="chips-row">
                            <span class="chip-badge">Prabayar</span>
                            <span class="chip-badge">Perpanjangan</span>
                        </div>

                        <!-- Due Dates Details in Indonesian -->
                        @php
                            $firstUnpaid = collect($invoices)->where('status_bayar', 0)->first();
                            $dueDateStr = $firstUnpaid ? \Carbon\Carbon::parse($firstUnpaid->jatuh_tempo)->translatedFormat('d F Y') : '-';
                            $suspendDateStr = $firstUnpaid ? \Carbon\Carbon::parse($firstUnpaid->jatuh_tempo)->translatedFormat('d F Y') : '-';
                        @endphp
                        <div class="due-dates-info">
                            <div class="date-line">
                                <span>Tanggal Jatuh Tempo Tagihan</span>
                                <strong>{{ $dueDateStr }}</strong>
                            </div>
                            <div class="date-line">
                                <span>Tanggal Isolir Layanan</span>
                                <strong>{{ $suspendDateStr }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Paid/Unpaid Big Button at bottom of card -->
                    @if($tagihanTotal == 0)
                        <div class="status-button status-button--paid">
                            <i class="fa-solid fa-circle-check"></i> ✓ Lunas
                        </div>
                    @else
                        <a href="{{ route('payment.detail') }}" class="status-button-link">
                            <button class="status-button status-button--unpaid">
                                <i class="fa-solid fa-wallet"></i> Bayar Sekarang ({{ 'Rp ' . number_format($tagihanTotal, 0, ',', '.') }})
                            </button>
                        </a>
                    @endif
                </div>

                <!-- Card 2: Usage Card (Translated to Indonesian) -->
                <div class="card">
                    <div class="card-title-with-icon">
                        <span>🚀</span>
                        <span>Kuota Unlimited</span>
                    </div>

                    <!-- Download Usage -->
                    <div class="usage-item">
                        <div class="usage-item-header">
                            <p><i class="fa-solid fa-download" style="color:#3b82f6;"></i> Unduh</p>
                            <span id="download-usage">0 B</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill progress-bar-fill--download" id="dl-progress-bar"></div>
                        </div>
                    </div>

                    <!-- Upload Usage -->
                    <div class="usage-item" style="margin-bottom:0;">
                        <div class="usage-item-header">
                            <p><i class="fa-solid fa-upload" style="color:#10b981;"></i> Unggah</p>
                            <span id="upload-usage">0 B</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill progress-bar-fill--upload" id="ul-progress-bar"></div>
                        </div>
                    </div>

                    <!-- Active Session Duration (Capsule box matching mockup) -->
                    <div class="duration-box">
                        <p><i class="fa-regular fa-clock" style="margin-right:6px;"></i> Durasi</p>
                        <span id="session-duration">0 jam 0 menit</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Invoice History and Quick Actions -->
            <div class="right-column">
                <!-- Card 3: Invoice History (Translated to Indonesian) -->
                <div class="card">
                    <div class="card-title-with-icon">
                        <span>📦</span>
                        <span>Riwayat Tagihan</span>
                    </div>

                    <div class="invoice-history-list">
                        @forelse($invoices as $inv)
                            <div class="invoice-item">
                                <div class="invoice-item-left">
                                    <div class="invoice-icon">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </div>
                                    <div>
                                        <p class="invoice-id">INV-{{ $inv->id_tagihan }}</p>
                                        <p class="invoice-date">Periode: {{ $inv->bulan_tahun }}</p>
                                    </div>
                                </div>
                                <div class="invoice-item-right">
                                    <p class="invoice-amount">{{ 'Rp ' . number_format($inv->jml_bayar, 0, ',', '.') }}</p>
                                    @if($inv->status_bayar == 1)
                                        <span class="status-badge status-badge-paid">Lunas</span>
                                    @else
                                        <span class="status-badge status-badge-unpaid">Belum Lunas</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="no-invoices">
                                Tidak ada tagihan aktif.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Action Buttons in Indonesian -->
                <div class="actions-row">
                    <a href="{{ route('keluhan.create') }}" class="action-item-card">
                        <span><i class="fa-solid fa-headset" style="color:#f43f5e;"></i></span>
                        <p>Lapor Gangguan</p>
                    </a>
                    <a href="{{ route('network.status') }}" class="action-item-card">
                        <span><i class="fa-solid fa-wifi" style="color:#3b82f6;"></i></span>
                        <p>Status Jaringan</p>
                    </a>
                    <div class="action-item-card" style="opacity: 0.6; cursor: not-allowed;" title="Layanan Penuh">
                        <span><i class="fa-solid fa-arrow-up-right-dots" style="color:#eab308;"></i></span>
                        <p>Upgrade</p>
                    </div>
                    <a href="{{ route('keluhan.index') }}" class="action-item-card">
                        <span><i class="fa-solid fa-ticket-simple" style="color:#10b981;"></i></span>
                        <p>Lihat Tiket</p>
                    </a>
                </div>
            </div>
        </div>

        @include('partials.bottom-nav')
    </div>

    <!-- AJAX script to load live session stats and Uptime from router -->
    <script>
        (function () {
            // Modal script
            var btnOpen = document.getElementById('btnInformasi');
            var btnClose = document.getElementById('btnCloseInformasi');
            var modal = document.getElementById('infoModal');
            if (btnOpen && modal) {
                function openModal() { modal.classList.add('is-open'); }
                function closeModal() { modal.classList.remove('is-open'); }
                btnOpen.addEventListener('click', openModal);
                if (btnClose) btnClose.addEventListener('click', closeModal);
                modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
            }

            // Live Router stats loading
            const connStatusEl = document.getElementById("connection-status");
            const dlUsageEl = document.getElementById("download-usage");
            const ulUsageEl = document.getElementById("upload-usage");
            const durationEl = document.getElementById("session-duration");
            
            const dlProgress = document.getElementById("dl-progress-bar");
            const ulProgress = document.getElementById("ul-progress-bar");
            const packageBadgeEl = document.getElementById("package-status-badge");

            // Format Bytes helper
            function formatBytes(bytes) {
                if (!bytes || bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Format Uptime helper for Mikrotik values to Indonesian
            function formatUptime(uptime) {
                if (!uptime || uptime === '0s') return '0 jam 0 menit';
                
                if (uptime.includes(':')) {
                    const parts = uptime.split(':');
                    if (parts.length === 3) {
                        const h = parseInt(parts[0], 10);
                        const m = parseInt(parts[1], 10);
                        return `${h} jam ${m} menit`;
                    }
                }
                
                let days = 0, hours = 0, minutes = 0;
                const dMatch = uptime.match(/(\d+)d/);
                const hMatch = uptime.match(/(\d+)h/);
                const mMatch = uptime.match(/(\d+)m/);
                
                if (dMatch) days = parseInt(dMatch[1], 10);
                if (hMatch) hours = parseInt(hMatch[1], 10);
                if (mMatch) minutes = parseInt(mMatch[1], 10);
                
                let res = [];
                if (days > 0) res.push(`${days} hari`);
                if (hours > 0 || days > 0) res.push(`${hours} jam`);
                res.push(`${minutes} menit`);
                
                return res.join(' ');
            }
            
            fetch("{{ route('dashboard.router_stats') }}")
                .then(r => r.json())
                .then(res => {
                    // Update connection status label and dot class based on server status response
                    if (res.status_label) {
                        if (connStatusEl) {
                            connStatusEl.innerHTML = `<span class="status-dot status-dot--${res.status_color}"></span> ${res.status_label}`;
                        }
                    } else {
                        // Fallback logic
                        if (connStatusEl) {
                            if (res.online) {
                                connStatusEl.innerHTML = '<span class="status-dot status-dot--success"></span> Internet Aktif';
                            } else {
                                const hasUnpaid = {{ $tagihanTotal > 0 ? 'true' : 'false' }};
                                if (hasUnpaid) {
                                    connStatusEl.innerHTML = '<span class="status-dot status-dot--danger"></span> Internet Terisolir';
                                } else {
                                    connStatusEl.innerHTML = '<span class="status-dot status-dot--success"></span> Internet Aktif';
                                }
                            }
                        }
                    }

                    // Update package status badge dynamically
                    if (packageBadgeEl && res.status) {
                        packageBadgeEl.className = "badge";
                        if (res.status === 'terisolir') {
                            packageBadgeEl.textContent = "Isolir";
                            packageBadgeEl.classList.add("badge-danger");
                        } else if (res.status === 'aktif') {
                            packageBadgeEl.textContent = "Aktif";
                            packageBadgeEl.classList.add("badge-success");
                        } else if (res.status === 'aktif_tidak_terhubung') {
                            packageBadgeEl.textContent = "Tidak Terhubung";
                            packageBadgeEl.classList.add("badge-warning");
                        }
                    }

                    // Populate dynamic bandwidth stats
                    if (dlUsageEl) dlUsageEl.textContent = formatBytes(res.bytes_out || 0);
                    if (ulUsageEl) ulUsageEl.textContent = formatBytes(res.bytes_in || 0);
                    if (durationEl) durationEl.textContent = formatUptime(res.uptime || '0s');
                    
                    // Set progress bar widths dynamically
                    const bytesOut = res.bytes_out || 0;
                    const bytesIn = res.bytes_in || 0;
                    const totalBytes = bytesOut + bytesIn;
                    if (totalBytes > 0) {
                        const dlPct = Math.round((bytesOut / totalBytes) * 100);
                        const ulPct = Math.round((bytesIn / totalBytes) * 100);
                        if (dlProgress) dlProgress.style.width = dlPct + "%";
                        if (ulProgress) ulProgress.style.width = ulPct + "%";
                    } else {
                        if (dlProgress) dlProgress.style.width = "0%";
                        if (ulProgress) ulProgress.style.width = "0%";
                    }
                })
                .catch(err => {
                    console.error("Gagal memuat stats router:", err);
                    if (connStatusEl) {
                        const hasUnpaid = {{ $tagihanTotal > 0 ? 'true' : 'false' }};
                        if (hasUnpaid) {
                            connStatusEl.innerHTML = '<span class="status-dot status-dot--danger"></span> Internet Terisolir';
                        } else {
                            connStatusEl.innerHTML = '<span class="status-dot status-dot--success"></span> Internet Aktif';
                        }
                    }
                    if (packageBadgeEl) {
                        packageBadgeEl.className = "badge";
                        const hasUnpaid = {{ $tagihanTotal > 0 ? 'true' : 'false' }};
                        if (hasUnpaid) {
                            packageBadgeEl.textContent = "Isolir";
                            packageBadgeEl.classList.add("badge-danger");
                        } else {
                            packageBadgeEl.textContent = "Tidak Terhubung";
                            packageBadgeEl.classList.add("badge-warning");
                        }
                    }
                    if (dlUsageEl) dlUsageEl.textContent = "0 B";
                    if (ulUsageEl) ulUsageEl.textContent = "0 B";
                    if (durationEl) durationEl.textContent = "0 jam 0 menit";
                    if (dlProgress) dlProgress.style.width = "0%";
                    if (ulProgress) ulProgress.style.width = "0%";
                });
        })();
    </script>
</body>
</html>
