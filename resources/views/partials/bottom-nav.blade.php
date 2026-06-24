<!-- FontAwesome CDN for bottom-nav icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Bottom Tab Navigation Bar for Mobile */
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(15, 23, 42, 0.95);
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(20px);
        display: flex;
        justify-content: space-around;
        padding: 10px 0;
        z-index: 9999;
    }

    .bottom-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        color: #94a3b8;
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
        color: #64748b;
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
        body {
            padding-bottom: 70px; /* Prevent bottom nav from overlapping page content */
        }
    }
</style>

<!-- Footer version info in Indonesian -->
<div class="footer">
    &copy; {{ date('Y') }} LOTUS COMPUTAMA TEKNIK. Versi 2.1 | Oleh Lotus
</div>

<!-- Mobile Bottom Navigation Bar in Indonesian -->
<nav class="bottom-nav">
    <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('network.status') }}" class="bottom-nav-item {{ request()->routeIs('network.status') ? 'active' : '' }}">
        <i class="fa-solid fa-wifi"></i>
        <span>Status</span>
    </a>
    <a href="{{ route('keluhan.index') }}" class="bottom-nav-item {{ request()->routeIs('keluhan.*') ? 'active' : '' }}">
        <i class="fa-solid fa-ticket"></i>
        <span>Tiket</span>
    </a>
    <a href="{{ route('profile') }}" class="bottom-nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
        <i class="fa-solid fa-user"></i>
        <span>Profil</span>
    </a>
</nav>

<!-- Hidden Logout Form -->
<form method="POST" action="{{ route('logout') }}" id="logout-form" style="display:none;">
    @csrf
</form>
