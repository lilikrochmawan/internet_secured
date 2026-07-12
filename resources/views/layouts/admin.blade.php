<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ $profile->nama_sekolah ?? 'Indotel Billing' }}</title>
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
            --sidebar-width: 260px;
            --bg-light: #f8fafc;
            --text-dark: #0f172a;
            --text-gray: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 20px -2px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 30px -3px rgba(79, 70, 229, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-gradient);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 25px rgba(79, 70, 229, 0.15);
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .sidebar-brand i {
            font-size: 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 10px;
        }

        .sidebar-brand span {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }

        .sidebar-user {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(0, 0, 0, 0.08);
        }

        .sidebar-user img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .sidebar-user-info {
            display: flex;
            flex-direction: column;
        }

        .sidebar-user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .sidebar-user-role {
            font-size: 0.78rem;
            opacity: 0.75;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .sidebar-menu-item a {
            display: flex;
            align-items: center;
            gap: 14px;
            color: rgba(255, 255, 255, 0.82);
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .sidebar-menu-item a:hover {
            background: rgba(255, 255, 255, 0.12);
            color: white;
            transform: translateX(4px);
        }

        .sidebar-menu-item.active a {
            background: white;
            color: #4f46e5;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .sidebar-menu-item a i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Sidebar Submenu Styling */
        .sidebar-menu-item.has-submenu {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 4px;
        }

        .sidebar-menu-item.has-submenu > a {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-menu-item.has-submenu > a .submenu-label {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .submenu-arrow {
            font-size: 0.8rem;
            transition: transform 0.2s ease;
        }

        .sidebar-menu-item.open > a .submenu-arrow {
            transform: rotate(180deg);
        }

        .submenu {
            list-style: none;
            padding-left: 14px;
            margin: 4px 0 0 0;
            display: none;
            flex-direction: column;
            gap: 4px;
            border-left: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar-menu-item.open .submenu {
            display: flex;
        }

        /* Prevent full white active background on the has-submenu container itself when open */
        .sidebar-menu-item.has-submenu.active > a {
            background: rgba(255, 255, 255, 0.12);
            color: white;
        }

        /* If sub-item is active, it gets the clean active style */
        .submenu-item.active a {
            background: white;
            color: #4f46e5;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .submenu-item a {
            display: flex;
            align-items: center;
            gap: 14px;
            color: rgba(255, 255, 255, 0.82);
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .submenu-item a:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            transform: translateX(4px);
        }

        /* Main Panel Styling */
        .main-panel {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-width: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        /* Topbar Styling */
        .topbar {
            height: 70px;
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: var(--shadow-sm);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--text-dark);
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .menu-toggle:hover {
            background-color: #f1f5f9;
        }

        .topbar-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 12px;
            transition: background 0.2s;
            position: relative;
        }

        .topbar-user:hover {
            background-color: #f8fafc;
        }

        .topbar-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .topbar-user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .topbar-dropdown {
            position: absolute;
            top: 55px;
            right: 0;
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-shadow: var(--shadow-md);
            width: 180px;
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 110;
            animation: slideDown 0.2s ease;
        }

        .topbar-dropdown.active {
            display: flex;
        }

        .topbar-dropdown-item {
            padding: 12px 16px;
            font-size: 0.9rem;
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background 0.2s;
        }

        .topbar-dropdown-item:hover {
            background-color: #f1f5f9;
            color: #4f46e5;
        }

        /* Content Area */
        .content-container {
            padding: 30px;
            flex-grow: 1;
            min-width: 0;
        }

        /* Footer Styling */
        .footer {
            padding: 20px 30px;
            border-top: 1px solid var(--border-color);
            background-color: white;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-gray);
        }

        /* Utility Cards & Styles */
        .card {
            background-color: white;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            padding: 24px;
            margin-bottom: 24px;
            min-width: 0;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: #4f46e5;
        }

        /* Alerts Styling */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.3s ease;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Animations */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive Helper Grid & Spacing */
        .form-row-2 {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 16px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);
            gap: 16px;
        }

        .form-row-2-1 {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 16px;
        }

        .form-row-1-2 {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
            gap: 16px;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.4);
            z-index: 95;
            backdrop-filter: blur(4px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Customize Table Container Scrollbar globally */
        .table-container::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .table-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .table-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-panel {
                margin-left: 0;
                width: 100%;
                min-width: 0;
            }
            .menu-toggle {
                display: block;
            }

            /* Responsive layout padding & elements */
            .content-container {
                padding: 15px;
                min-width: 0;
            }
            .topbar {
                padding: 0 15px;
            }
            .card {
                padding: 16px;
                margin-bottom: 16px;
                border-radius: 16px;
            }
            #master-map {
                height: 400px !important;
            }
            #titikodc, #titikodp {
                height: 350px !important;
            }
            .form-row-2 {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }
            .form-row-3 {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }
            .form-row-2-1 {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }
            .form-row-1-2 {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }
        }

        @media (max-width: 576px) {
            .topbar-user-name {
                display: none;
            }
            .topbar-title {
                font-size: 1.1rem;
            }
            .card {
                padding: 14px 12px;
                border-radius: 12px;
            }
            .card-header {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .card-header .btn {
                width: 100%;
                justify-content: center;
            }
            .table {
                min-width: 650px !important;
            }
        }

        /* Pagination Styles */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
        }

        .page-btn {
            background-color: white;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
        }

        .page-btn:hover:not(:disabled) {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }

        .page-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.15);
            font-weight: 600;
        }

        .page-btn:disabled {
            color: #94a3b8;
            background-color: #f8fafc;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        .page-ellipsis {
            color: var(--text-gray);
            font-size: 0.9rem;
            padding: 0 4px;
            user-select: none;
        }

        /* Bell Notification Styles */
        .topbar-bell-wrapper {
            position: relative;
            display: inline-block;
        }

        .topbar-bell-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.35rem;
            color: var(--text-gray);
            padding: 8px;
            border-radius: 10px;
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .topbar-bell-btn:hover {
            background-color: #f1f5f9;
            color: #4f46e5;
        }

        /* Ringing Animation for Bell Icon */
        .topbar-bell-btn.ringing i {
            animation: ring-bell 1s ease infinite;
            transform-origin: 50% 0;
            display: inline-block;
            color: #ef4444; /* Alert color when ringing */
        }

        @keyframes ring-bell {
            0% { transform: rotate(0); }
            10% { transform: rotate(15deg); }
            20% { transform: rotate(-10deg); }
            30% { transform: rotate(10deg); }
            40% { transform: rotate(-8deg); }
            50% { transform: rotate(6deg); }
            60% { transform: rotate(-4deg); }
            70% { transform: rotate(2deg); }
            80% { transform: rotate(-1deg); }
            90% { transform: rotate(1deg); }
            100% { transform: rotate(0); }
        }

        .topbar-bell-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: #ef4444;
            color: white;
            font-size: 0.68rem;
            font-weight: 700;
            min-width: 17px;
            height: 17px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }
            70% {
                box-shadow: 0 0 0 5px rgba(239, 68, 68, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .topbar-bell-dropdown {
            position: absolute;
            top: 55px;
            right: 0;
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            width: 320px;
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 120;
            animation: slideDown 0.2s ease;
        }

        .topbar-bell-dropdown.active {
            display: flex;
        }

        .topbar-bell-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            background-color: #f8fafc;
        }

        .topbar-bell-header span {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .topbar-bell-clear-btn {
            background: none;
            border: none;
            color: #4f46e5;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .topbar-bell-clear-btn:hover {
            color: #3730a3;
            background-color: rgba(79, 70, 229, 0.05);
        }

        .topbar-bell-content {
            max-height: 320px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .topbar-bell-item {
            display: flex;
            flex-direction: column;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
            transition: background 0.2s;
        }

        .topbar-bell-item:last-child {
            border-bottom: none;
        }

        .topbar-bell-item:hover {
            background-color: #f8fafc;
        }

        .topbar-bell-item.unread {
            background-color: rgba(79, 70, 229, 0.03);
            border-left: 3px solid #4f46e5;
            padding-left: 13px;
        }

        .topbar-bell-item-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .topbar-bell-item-title i {
            font-size: 0.95rem;
        }

        .topbar-bell-item-desc {
            font-size: 0.8rem;
            color: var(--text-gray);
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .topbar-bell-item-time {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .topbar-bell-empty {
            padding: 24px;
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-gray);
        }

        /* Adjust topbar-right for mobile to keep items aligned */
        @media (max-width: 768px) {
            .topbar-right {
                gap: 12px;
            }
            .topbar-bell-dropdown {
                width: 280px;
                right: -50px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar backdrop overlay for mobile viewports -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Sidebar Menu -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand" style="gap: 10px; display: flex; align-items: center;">
            @if(!empty($profile->foto) && file_exists(public_path('images/' . $profile->foto)))
                <img src="{{ asset('images/' . $profile->foto) }}" alt="Logo" style="width: 36px; height: 36px; object-fit: contain; border-radius: 6px; background: rgba(255,255,255,0.15); padding: 2px; flex-shrink: 0;">
            @else
                <i class="fa-solid fa-wifi" style="flex-shrink: 0;"></i>
            @endif
            <div style="display: flex; flex-direction: column; min-width: 0;">
                <span style="font-size: 1.1rem; line-height: 1.2; font-family: 'Outfit', sans-serif; font-weight: 800; letter-spacing: 0.5px; word-break: break-word;">{{ $profile->nama_sekolah ?? 'INDOTEL' }}</span>
                @if(!empty($profile->license_key))
                    <span class="badge-license" style="font-size: 0.65rem; background: rgba(255,255,255,0.2); color: #ffffff; padding: 2px 6px; border-radius: 4px; display: inline-block; width: max-content; margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1; border: 1px solid rgba(255,255,255,0.15); font-family: 'Outfit', sans-serif;">
                        (License: {{ $profile->license_plan_name ?? 'Lite' }})
                    </span>
                @else
                    <span class="badge-license" style="font-size: 0.65rem; background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 2px 6px; border-radius: 4px; display: inline-block; width: max-content; margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1; font-family: 'Outfit', sans-serif;">
                        No License
                    </span>
                @endif
            </div>
        </div>
        
        <div class="sidebar-user">
            @if(Auth::user()->foto && file_exists(public_path('administrator/images/' . Auth::user()->foto)))
                <img src="{{ asset('administrator/images/' . Auth::user()->foto) }}" alt="Foto Profile">
            @else
                <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.2); display:grid; place-items:center; font-weight:700; font-size:1.2rem; border: 2px solid rgba(255,255,255,0.4);">
                    {{ substr(Auth::user()->nama_user ?? 'A', 0, 1) }}
                </div>
            @endif
            <div class="sidebar-user-info">
                <span class="sidebar-user-name">{{ Auth::user()->nama_user }}</span>
                <span class="sidebar-user-role">{{ Auth::user()->level }}</span>
            </div>
        </div>

        <ul class="sidebar-menu">
            @php $currRoute = Route::currentRouteName(); @endphp
            
            @if(Auth::user()->hasMenuAccess('dashboard'))
                <li class="sidebar-menu-item {{ $currRoute == 'admin.dashboard' ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
                </li>
            @endif


            @if(Auth::user()->level == 'admin' || Auth::user()->hasMenuAccess('pengguna'))
                @php
                    $isSettingsActive = in_array($currRoute, ['admin.pengaturan.index', 'admin.pengaturan_client.index', 'admin.pengguna.index']);
                @endphp
                <li class="sidebar-menu-item has-submenu {{ $isSettingsActive ? 'active open' : '' }}">
                    <a href="javascript:void(0)" class="submenu-toggle">
                        <span class="submenu-label">
                            <i class="fa-solid fa-gear"></i>
                            <span>Pengaturan Sistem</span>
                        </span>
                        <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(Auth::user()->level == 'admin')
                            <li class="submenu-item {{ $currRoute == 'admin.pengaturan.index' ? 'active' : '' }}">
                                <a href="{{ route('admin.pengaturan.index') }}">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>Pengaturan Umum</span>
                                </a>
                            </li>
                            <li class="submenu-item {{ $currRoute == 'admin.pengaturan_client.index' ? 'active' : '' }}">
                                <a href="{{ route('admin.pengaturan_client.index') }}">
                                    <i class="fa-solid fa-sitemap"></i>
                                    <span>Pengaturan Akses</span>
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->hasMenuAccess('pengguna'))
                            <li class="submenu-item {{ $currRoute == 'admin.pengguna.index' ? 'active' : '' }}">
                                <a href="{{ route('admin.pengguna.index') }}">
                                    <i class="fa-solid fa-users-gear"></i>
                                    <span>Pengguna</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('monitoring') || Auth::user()->level == 'admin' || Auth::user()->level == 'teknisi')
                @php
                    $isMonitoringGroupActive = Str::startsWith($currRoute, 'admin.monitoring') || $currRoute == 'admin.teknisi.clients';
                @endphp
                <li class="sidebar-menu-item has-submenu {{ $isMonitoringGroupActive ? 'active open' : '' }}">
                    <a href="javascript:void(0)" class="submenu-toggle">
                        <span class="submenu-label">
                            <i class="fa-solid fa-network-wired"></i>
                            <span>Mikrotik Monitoring</span>
                        </span>
                        <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(Auth::user()->hasMenuAccess('monitoring'))
                            <li class="submenu-item {{ Str::startsWith($currRoute, 'admin.monitoring') ? 'active' : '' }}">
                                <a href="{{ route('admin.monitoring.index') }}">
                                    <i class="fa-solid fa-chart-line"></i>
                                    <span>Router Monitoring</span>
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->level == 'admin' || Auth::user()->level == 'teknisi' || Auth::user()->level == 'noc')
                            <li class="submenu-item {{ $currRoute == 'admin.teknisi.clients' ? 'active' : '' }}">
                                <a href="{{ route('admin.teknisi.clients') }}">
                                    <i class="fa-solid fa-user-slash"></i>
                                    <span>Client Isolir & Non-Aktif</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('order_pemasangan'))
                @php
                    $orderNotificationCount = 0;
                    if (Auth::check()) {
                        $user = Auth::user();
                        if ($user->level === 'admin' || $user->level === 'noc') {
                            $orderNotificationCount = \App\Models\OrderPemasangan::where('status', 'pending')->count();
                        } elseif ($user->level === 'teknisi') {
                            $orderNotificationCount = \App\Models\OrderPemasangan::whereIn('id_teknisi', [$user->id, 0])
                                ->where('status', 'approved')
                                ->count();
                        }
                    }
                @endphp
                <li class="sidebar-menu-item {{ Str::startsWith($currRoute, 'admin.order_pemasangan') ? 'active' : '' }}">
                    <a href="{{ route('admin.order_pemasangan.index') }}">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                        <span>Order Pemasangan</span>
                        @if($orderNotificationCount > 0)
                            <span class="sidebar-badge" style="margin-left: auto; background-color: #ef4444; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px;">
                                {{ $orderNotificationCount }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('tr069'))
                <li class="sidebar-menu-item {{ Str::startsWith($currRoute, 'admin.tr069') ? 'active' : '' }}">
                    <a href="{{ route('admin.tr069.index') }}"><i class="fa-solid fa-server"></i><span>TR-069 ACS</span></a>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('odc'))
                <li class="sidebar-menu-item {{ Str::startsWith($currRoute, 'admin.odc') ? 'active' : '' }}">
                    <a href="{{ route('admin.odc.index') }}"><i class="fa-solid fa-circle-nodes"></i><span>Kelola ODC</span></a>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('odp'))
                <li class="sidebar-menu-item {{ Str::startsWith($currRoute, 'admin.odp') ? 'active' : '' }}">
                    <a href="{{ route('admin.odp.index') }}"><i class="fa-solid fa-diagram-project"></i><span>Kelola ODP</span></a>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('mapping'))
                <li class="sidebar-menu-item {{ $currRoute == 'admin.mapping.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.mapping.index') }}"><i class="fa-solid fa-map-location-dot"></i><span>Map Client & Topologi</span></a>
                </li>
            @endif



            @if(Auth::user()->hasMenuAccess('custom_pesan'))
                <li class="sidebar-menu-item {{ $currRoute == 'admin.custom_pesan.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.custom_pesan.index') }}"><i class="fa-solid fa-comment-dots"></i><span>Custom Pesan WA</span></a>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('broadcast'))
                <li class="sidebar-menu-item {{ Str::startsWith($currRoute, 'admin.broadcast') ? 'active' : '' }}">
                    <a href="{{ route('admin.broadcast.index') }}"><i class="fa-solid fa-bullhorn"></i><span>Broadcast Notifikasi</span></a>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('pelanggan') || Auth::user()->hasMenuAccess('paket') || Auth::user()->hasMenuAccess('ont'))
                @php
                    $isMasterActive = in_array($currRoute, ['admin.pelanggan.index', 'admin.paket.index', 'admin.ont.index']);
                @endphp
                <li class="sidebar-menu-item has-submenu {{ $isMasterActive ? 'active open' : '' }}">
                    <a href="javascript:void(0)" class="submenu-toggle">
                        <span class="submenu-label">
                            <i class="fa-solid fa-database"></i>
                            <span>Data Master</span>
                        </span>
                        <i class="fa-solid fa-chevron-down submenu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        @if(Auth::user()->hasMenuAccess('pelanggan'))
                            <li class="submenu-item {{ $currRoute == 'admin.pelanggan.index' ? 'active' : '' }}">
                                <a href="{{ route('admin.pelanggan.index') }}">
                                    <i class="fa-solid fa-user-group"></i>
                                    <span>Data Pelanggan</span>
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->hasMenuAccess('paket'))
                            <li class="submenu-item {{ $currRoute == 'admin.paket.index' ? 'active' : '' }}">
                                <a href="{{ route('admin.paket.index') }}">
                                    <i class="fa-solid fa-box-archive"></i>
                                    <span>Data Paket</span>
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->hasMenuAccess('ont'))
                            <li class="submenu-item {{ $currRoute == 'admin.ont.index' ? 'active' : '' }}">
                                <a href="{{ route('admin.ont.index') }}">
                                    <i class="fa-solid fa-hard-drive"></i>
                                    <span>Data ONT</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('transaksi'))
                <li class="sidebar-menu-item {{ Str::startsWith($currRoute, 'admin.transaksi') ? 'active' : '' }}">
                    <a href="{{ route('admin.transaksi.index') }}"><i class="fa-solid fa-receipt"></i><span>Transaksi</span></a>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('kas'))
                <li class="sidebar-menu-item {{ $currRoute == 'admin.kas.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.kas.index') }}"><i class="fa-solid fa-money-bill-transfer"></i><span>Kas Masuk/Keluar</span></a>
                </li>
            @endif

            @if(Auth::user()->hasMenuAccess('keluhan'))
                <li class="sidebar-menu-item {{ $currRoute == 'admin.keluhan.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.keluhan.index') }}">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Keluhan/Ticket</span>
                        @if(isset($jumlahKeluhanAktif) && $jumlahKeluhanAktif > 0)
                            <span class="keluhan-badge" style="margin-left: auto; background-color: #ef4444; color: white; padding: 2px 8px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; min-width: 18px; text-align: center; line-height: 1.2; box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);">
                                {{ $jumlahKeluhanAktif }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif

            @if(Auth::user()->level === 'admin')
                <li class="sidebar-menu-item {{ Str::startsWith($currRoute, 'admin.logs') ? 'active' : '' }}">
                    <a href="{{ route('admin.logs.index') }}"><i class="fa-solid fa-file-shield"></i><span>Log Aktivitas</span></a>
                </li>
            @endif


        </ul>
    </div>

    <!-- Main Panel -->
    <div class="main-panel">
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-title">@yield('title', 'Dashboard')</div>
            </div>

            <div class="topbar-right">
                @if(Auth::user()->level === 'admin')
                    <!-- Bell Notifications Wrapper -->
                    <div class="topbar-bell-wrapper" id="bell-dropdown-trigger">
                        <button class="topbar-bell-btn" aria-label="Notifikasi">
                            <i class="fa-regular fa-bell"></i>
                            <span class="topbar-bell-badge" id="bell-badge" style="display: none;">0</span>
                        </button>
                        
                        <!-- Bell Dropdown List -->
                        <div class="topbar-bell-dropdown" id="bell-dropdown">
                            <div class="topbar-bell-header">
                                <span>Notifikasi Admin</span>
                                <button class="topbar-bell-clear-btn" id="bell-clear-btn">Tandai semua dibaca</button>
                            </div>
                            <div class="topbar-bell-content" id="bell-notifications-list">
                                <div class="topbar-bell-empty">Tidak ada notifikasi baru</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="topbar-user" id="user-dropdown-trigger">
                    <div class="topbar-user-avatar">
                        {{ substr(Auth::user()->nama_user, 0, 1) }}
                    </div>
                    <span class="topbar-user-name">{{ Auth::user()->nama_user }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size:0.75rem; color:var(--text-gray);"></i>
                    
                    <!-- Dropdown Content -->
                    <div class="topbar-dropdown" id="user-dropdown">
                        <a href="{{ route('admin.pengaturan.index') }}" class="topbar-dropdown-item">
                            <i class="fa-solid fa-gear"></i> Pengaturan Umum
                        </a>
                        <a href="javascript:void(0)" onclick="openChangePasswordModal()" class="topbar-dropdown-item">
                            <i class="fa-solid fa-key"></i> Ganti Password
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}" style="width:100%;">
                            @csrf
                            <button type="submit" class="topbar-dropdown-item" style="color:#ef4444;">
                                <i class="fa-solid fa-right-from-bracket"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-container">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <div>
                        <ul style="list-style:none;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>&copy; {{ date('Y') }} {{ $profile->nama_sekolah ?? 'BILLING INTERNET' }}</strong>. Version 2.0 | By Lotus Computama Teknik. (Dilindungi hak cipta)
        </div>
    </div>

    <!-- Layout JS scripts -->
    <script>
        // Toggle Sidebar & Overlay
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('active');
                if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
            });
            const closeSidebar = () => {
                sidebar.classList.remove('active');
                if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            };
            document.addEventListener('click', (e) => {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    closeSidebar();
                }
            });
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
        }

        // Toggle User Dropdown
        const dropdownTrigger = document.getElementById('user-dropdown-trigger');
        const dropdown = document.getElementById('user-dropdown');
        if (dropdownTrigger && dropdown) {
            dropdownTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('active');
            });
            document.addEventListener('click', () => {
                dropdown.classList.remove('active');
            });
        }

        // Submenu Toggle
        document.querySelectorAll('.submenu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('open');
            });
        });

        // Reusable Table Pagination and Row Limiter Utility
        window.setupTablePagination = function(tableSelector, paginationContainerSelector, limitSelector, searchSelector) {
            const table = document.querySelector(tableSelector);
            if (!table) return;
            const tableBody = table.querySelector("tbody");
            const controls = document.querySelector(paginationContainerSelector);
            const limitSelect = document.querySelector(limitSelector);
            const searchInput = document.querySelector(searchSelector);

            if (!tableBody || !controls || !limitSelect) return;

            let allRows = Array.from(tableBody.querySelectorAll("tr"));
            // Filter out empty state rows (where cells colspan is large or no actual data)
            allRows = allRows.filter(row => row.cells.length > 2);
            
            let filteredRows = [...allRows];
            let currentPage = 1;

            // Add sorting functionality to headers
            const headers = Array.from(table.querySelectorAll("thead th"));
            let sortDirection = {};

            headers.forEach((th, colIndex) => {
                // Skip if it's the first column (No) or actions column
                if (colIndex === 0 || th.style.textAlign === 'center' || th.classList.contains('no-sort') || th.textContent.trim() === 'Aksi' || th.textContent.toLowerCase().includes('aksi')) {
                    return;
                }

                th.style.cursor = 'pointer';
                th.style.position = 'relative';
                th.title = 'Klik untuk mengurutkan';
                
                const icon = document.createElement('i');
                icon.className = 'fa-solid fa-sort';
                icon.style.marginLeft = '6px';
                icon.style.opacity = '0.35';
                th.appendChild(icon);

                th.addEventListener('click', function() {
                    const direction = sortDirection[colIndex] === 'asc' ? 'desc' : 'asc';
                    sortDirection = {}; // Clear other sorts
                    sortDirection[colIndex] = direction;

                    headers.forEach(h => {
                        const iconEl = h.querySelector('i.fa-sort, i.fa-sort-up, i.fa-sort-down');
                        if (iconEl) {
                            iconEl.className = 'fa-solid fa-sort';
                            iconEl.style.opacity = '0.35';
                        }
                    });

                    icon.className = direction === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
                    icon.style.opacity = '1';

                    allRows.sort((a, b) => {
                        const cellA = a.cells[colIndex] ? a.cells[colIndex].textContent.trim() : '';
                        const cellB = b.cells[colIndex] ? b.cells[colIndex].textContent.trim() : '';

                        let valA = cellA.replace(/[^0-9.-]+/g, "");
                        let valB = cellB.replace(/[^0-9.-]+/g, "");
                        const isNum = valA !== '' && !isNaN(valA) && valB !== '' && !isNaN(valB);

                        if (isNum) {
                            return direction === 'asc' ? parseFloat(valA) - parseFloat(valB) : parseFloat(valB) - parseFloat(valA);
                        }

                        // Try to parse as date/datetime
                        const cleanA = cellA.replace(/(\d{2})-(\d{2})-(\d{4})/, '$3-$2-$1');
                        const cleanB = cellB.replace(/(\d{2})-(\d{2})-(\d{4})/, '$3-$2-$1');
                        const dateA = Date.parse(cleanA);
                        const dateB = Date.parse(cleanB);

                        if (!isNaN(dateA) && !isNaN(dateB)) {
                            return direction === 'asc' ? dateA - dateB : dateB - dateA;
                        }

                        return direction === 'asc' ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                    });

                    allRows.forEach(row => tableBody.appendChild(row));

                    if (searchInput && searchInput.value) {
                        const query = searchInput.value.toLowerCase();
                        filteredRows = allRows.filter(row => row.textContent.toLowerCase().includes(query));
                    } else {
                        filteredRows = [...allRows];
                    }

                    currentPage = 1;
                    renderTable();
                });
            });

            function renderTable() {
                const limit = parseInt(limitSelect.value) || 10;
                const totalItems = filteredRows.length;
                const totalPages = Math.ceil(totalItems / limit) || 1;

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * limit;
                const endIndex = startIndex + limit;

                // Hide all rows first
                allRows.forEach(row => row.style.display = "none");

                // Show only filtered rows that fall into the current page
                filteredRows.forEach((row, index) => {
                    if (index >= startIndex && index < endIndex) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                    // Update global number cell (if first cell is the number column)
                    if (row.cells[0]) {
                        row.cells[0].textContent = index + 1;
                    }
                });

                // Render page controls
                renderControls(totalPages);
            }

            function renderControls(totalPages) {
                controls.innerHTML = "";
                if (totalPages <= 1) {
                    controls.style.display = "none";
                    return;
                }
                controls.style.display = "flex";
                controls.className = "pagination-wrapper";

                // Previous page button
                const prevBtn = document.createElement("button");
                prevBtn.type = "button";
                prevBtn.textContent = "Sebelumnya";
                prevBtn.className = "page-btn" + (currentPage === 1 ? " disabled" : "");
                prevBtn.disabled = currentPage === 1;
                prevBtn.onclick = () => {
                    if (currentPage > 1) {
                        currentPage--;
                        renderTable();
                    }
                };
                controls.appendChild(prevBtn);

                // Page numbers logic
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                if (startPage > 1) {
                    const firstBtn = document.createElement("button");
                    firstBtn.type = "button";
                    firstBtn.textContent = "1";
                    firstBtn.className = "page-btn";
                    firstBtn.onclick = () => { currentPage = 1; renderTable(); };
                    controls.appendChild(firstBtn);

                    if (startPage > 2) {
                        const ellipsis = document.createElement("span");
                        ellipsis.textContent = "...";
                        ellipsis.className = "page-ellipsis";
                        controls.appendChild(ellipsis);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement("button");
                    pageBtn.type = "button";
                    pageBtn.textContent = i;
                    pageBtn.className = "page-btn" + (currentPage === i ? " active" : "");
                    pageBtn.onclick = () => { currentPage = i; renderTable(); };
                    controls.appendChild(pageBtn);
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const ellipsis = document.createElement("span");
                        ellipsis.textContent = "...";
                        ellipsis.className = "page-ellipsis";
                        controls.appendChild(ellipsis);
                    }

                    const lastBtn = document.createElement("button");
                    lastBtn.type = "button";
                    lastBtn.textContent = totalPages;
                    lastBtn.className = "page-btn";
                    lastBtn.onclick = () => { currentPage = totalPages; renderTable(); };
                    controls.appendChild(lastBtn);
                }

                // Next page button
                const nextBtn = document.createElement("button");
                nextBtn.type = "button";
                nextBtn.textContent = "Selanjutnya";
                nextBtn.className = "page-btn" + (currentPage === totalPages ? " disabled" : "");
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.onclick = () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderTable();
                    }
                };
                controls.appendChild(nextBtn);
            }

            // Search filtering integration
            if (searchInput) {
                const performSearch = function() {
                    const query = searchInput.value.toLowerCase();
                    filteredRows = allRows.filter(row => {
                        return row.textContent.toLowerCase().includes(query);
                    });
                    currentPage = 1;
                    renderTable();
                };
                searchInput.addEventListener("keyup", performSearch);
                if (searchInput.value) {
                    performSearch();
                }
            }

            // Limit selection integration
            limitSelect.addEventListener("change", function() {
                currentPage = 1;
                renderTable();
            });

            // Initial render
            renderTable();
        };
    </script>

    <!-- Modal Ganti Password Global -->
    <div class="modal" id="changePasswordModal" style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
        <div class="modal-content" style="background-color: white; border-radius: 24px; width: min(400px, 100%); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); animation: modalFadeIn 0.3s ease; overflow: hidden; display: flex; flex-direction: column;">
            <div class="modal-header" style="background: var(--primary-gradient); color: white; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700; margin: 0; color: white;">Ganti Password Akun</h3>
                <button type="button" class="modal-close" onclick="closeChangePasswordModal()" style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; opacity: 0.8; padding: 0; line-height: 1;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <form action="{{ route('admin.pengguna.ganti_password_self') }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px;">
                        <label for="current_password" style="font-size: 0.85rem; font-weight: 600; color: #334155; text-align: left;">Password Saat Ini *</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s;" placeholder="Masukkan password sekarang">
                    </div>

                    <div class="form-group" style="margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px;">
                        <label for="new_password" style="font-size: 0.85rem; font-weight: 600; color: #334155; text-align: left;">Password Baru *</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s;" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="form-group" style="margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px;">
                        <label for="new_password_confirmation" style="font-size: 0.85rem; font-weight: 600; color: #334155; text-align: left;">Ulangi Password Baru *</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 14px; font-size: 0.95rem; outline: none; width: 100%; transition: border 0.2s;" placeholder="Ketik ulang password baru">
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                        <button type="button" class="btn btn-secondary" onclick="closeChangePasswordModal()" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background-color: #e2e8f0; color: #334155;">Batal</button>
                        <button type="submit" class="btn btn-primary" style="padding: 10px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; background: var(--primary-gradient); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);">Simpan Sandi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openChangePasswordModal() {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown) dropdown.classList.remove('active');
            
            const modal = document.getElementById('changePasswordModal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function closeChangePasswordModal() {
            const modal = document.getElementById('changePasswordModal');
            if (modal) {
                modal.style.display = 'none';
            }
            document.getElementById('current_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('new_password_confirmation').value = '';
        }
    </script>

    <!-- Global Loading Overlay -->
    <div id="global-loading-overlay" style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; flex-direction: column; gap: 16px; backdrop-filter: blur(4px); color: white;">
        <div style="width: 50px; height: 50px; border: 5px solid rgba(255, 255, 255, 0.2); border-top-color: #ffffff; border-radius: 50%; animation: globalSpin 1s linear infinite;"></div>
        <div style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; letter-spacing: 0.5px;">Loading Data, Mohon Tunggu</div>
    </div>
    <style>
        @keyframes globalSpin {
            to { transform: rotate(360deg); }
        }
    </style>
    <script>
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (form && form.tagName === 'FORM' && form.getAttribute('target') !== '_blank' && !event.defaultPrevented) {
                const overlay = document.getElementById('global-loading-overlay');
                if (overlay) {
                    overlay.style.display = 'flex';
                }
            }
        });
    </script>
    @if(Auth::user()->level === 'admin')
        <!-- Bell Notifications JS Script -->
        <script>
            (function() {
                let audioCtx = null;
                let lastChimePlayedTime = 0;
                let knownUnreadIds = new Set();
                
                // Programmatic chime tone synthesizer (Web Audio API)
                function playNotificationChime() {
                    try {
                        if (!audioCtx) {
                            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        }
                        
                        // If suspended by browser autoplay policy, do not fail
                        if (audioCtx.state === 'suspended') {
                            console.warn('AudioContext is suspended. Autoplay blocked until user interaction.');
                            return;
                        }
                        
                        const now = audioCtx.currentTime;

                        // Tone 1 (D5 - 587.33Hz)
                        const osc1 = audioCtx.createOscillator();
                        const gain1 = audioCtx.createGain();
                        osc1.type = 'sine';
                        osc1.frequency.setValueAtTime(587.33, now);
                        gain1.gain.setValueAtTime(0.3, now);
                        gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.6);

                        osc1.connect(gain1);
                        gain1.connect(audioCtx.destination);
                        osc1.start(now);
                        osc1.stop(now + 0.6);

                        // Tone 2 (A5 - 880Hz)
                        const osc2 = audioCtx.createOscillator();
                        const gain2 = audioCtx.createGain();
                        osc2.type = 'sine';
                        osc2.frequency.setValueAtTime(880, now + 0.15);
                        gain2.gain.setValueAtTime(0.35, now + 0.15);
                        gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.95);

                        osc2.connect(gain2);
                        gain2.connect(audioCtx.destination);
                        osc2.start(now + 0.15);
                        osc2.stop(now + 0.95);
                        
                    } catch (err) {
                        console.error('Audio chime error:', err);
                    }
                }

                // Resume AudioContext on first page interaction
                function initAudioResume() {
                    const resumeAudio = () => {
                        if (audioCtx && audioCtx.state === 'suspended') {
                            audioCtx.resume().then(() => {
                                console.log('AudioContext resumed successfully via user interaction.');
                            });
                        }
                        // Cleanup event listeners
                        document.removeEventListener('click', resumeAudio);
                        document.removeEventListener('keydown', resumeAudio);
                    };
                    document.addEventListener('click', resumeAudio);
                    document.addEventListener('keydown', resumeAudio);
                }
                document.addEventListener('DOMContentLoaded', initAudioResume);

                let hasUnreadNotifications = false;

                // Render notifications list dynamically
                function renderNotifications(notifications) {
                    const listContainer = document.getElementById('bell-notifications-list');
                    const badge = document.getElementById('bell-badge');
                    
                    if (!listContainer || !badge) return;

                    const lastReadStr = localStorage.getItem('admin_bell_last_read') || '1970-01-01T00:00:00.000Z';
                    const lastReadDate = new Date(lastReadStr);

                    let unreadCount = 0;

                    listContainer.innerHTML = '';

                    if (notifications.length === 0) {
                        listContainer.innerHTML = '<div class="topbar-bell-empty">Tidak ada notifikasi baru</div>';
                        badge.style.display = 'none';
                        hasUnreadNotifications = false;
                        return;
                    }

                    notifications.forEach(item => {
                        const itemDate = new Date(item.timestamp);
                        const isUnread = itemDate > lastReadDate;

                        if (isUnread) {
                            unreadCount++;
                        }

                        const notificationItem = document.createElement('a');
                        notificationItem.href = item.url;
                        notificationItem.className = `topbar-bell-item ${isUnread ? 'unread' : ''}`;
                        notificationItem.setAttribute('data-id', item.id);
                        notificationItem.setAttribute('data-timestamp', item.timestamp);

                        let iconClass = 'fa-solid fa-circle-info';
                        if (item.type === 'order_pending') {
                            iconClass = 'fa-solid fa-truck-ramp-box text-primary';
                        } else if (item.type === 'order_installed') {
                            iconClass = 'fa-solid fa-square-check text-success';
                        } else if (item.type === 'complaint') {
                            iconClass = 'fa-solid fa-circle-exclamation text-danger';
                        }

                        notificationItem.innerHTML = `
                            <div class="topbar-bell-item-title">
                                <i class="${iconClass}"></i>
                                <span>${item.title}</span>
                            </div>
                            <div class="topbar-bell-item-desc">${item.description}</div>
                            <div class="topbar-bell-item-time">${item.time_human}</div>
                        `;

                        listContainer.appendChild(notificationItem);
                    });

                    // Update badge UI and unread state
                    const bellBtn = document.querySelector('.topbar-bell-btn');
                    if (unreadCount > 0) {
                        badge.innerText = unreadCount;
                        badge.style.display = 'flex';
                        hasUnreadNotifications = true;
                        if (bellBtn) bellBtn.classList.add('ringing');
                    } else {
                        badge.style.display = 'none';
                        hasUnreadNotifications = false;
                        if (bellBtn) bellBtn.classList.remove('ringing');
                    }
                }

                // AJAX notifications poller
                function pollNotifications() {
                    fetch('{{ route("admin.notifications.fetch") }}')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                renderNotifications(data.notifications);
                            }
                        })
                        .catch(err => console.error('Error polling notifications:', err));
                }

                // Client-side state clear to "read"
                function markAllNotificationsAsRead() {
                    localStorage.setItem('admin_bell_last_read', new Date().toISOString());
                    
                    const badge = document.getElementById('bell-badge');
                    if (badge) {
                        badge.style.display = 'none';
                    }

                    const bellBtn = document.querySelector('.topbar-bell-btn');
                    if (bellBtn) {
                        bellBtn.classList.remove('ringing');
                    }

                    const unreadItems = document.querySelectorAll('.topbar-bell-item.unread');
                    unreadItems.forEach(item => {
                        item.classList.remove('unread');
                    });

                    // Instantly kill sound
                    hasUnreadNotifications = false;
                }

                // Event Listeners setup
                document.addEventListener('DOMContentLoaded', function() {
                    const bellTrigger = document.getElementById('bell-dropdown-trigger');
                    const bellDropdown = document.getElementById('bell-dropdown');
                    const clearBtn = document.getElementById('bell-clear-btn');

                    if (bellTrigger && bellDropdown) {
                        bellTrigger.addEventListener('click', function(e) {
                            e.stopPropagation();
                            
                            // Close other user dropdown if active
                            const userDropdown = document.getElementById('user-dropdown');
                            if (userDropdown) userDropdown.classList.remove('active');
                            
                            const isActive = bellDropdown.classList.toggle('active');
                            
                            if (isActive) {
                                // Mark all as read when dropdown is opened
                                markAllNotificationsAsRead();
                            }
                        });
                    }

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            markAllNotificationsAsRead();
                        });
                    }

                    // Close dropdown on click outside
                    document.addEventListener('click', function(e) {
                        if (bellDropdown && bellDropdown.classList.contains('active')) {
                            if (!bellTrigger.contains(e.target)) {
                                bellDropdown.classList.remove('active');
                            }
                        }
                    });

                    // Initial Poll
                    pollNotifications();

                    // Poll every 2 seconds as requested
                    setInterval(pollNotifications, 2000);

                    // Continuous sound alert every 1 second if unread notifications exist
                    setInterval(function() {
                        if (hasUnreadNotifications) {
                            playNotificationChime();
                        }
                    }, 1000);
                });
            })();
        </script>
    @endif
    @yield('scripts')
</body>
</html>
