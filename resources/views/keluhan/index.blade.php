<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Keluhan</title>
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
            width: min(900px, 100%);
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
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .topbar a {
            color: #94a3b8;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }
        .topbar a:hover {
            color: #a5b4fc;
        }
        .topbar a.button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .topbar a.button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
            color: #fff;
        }
        
        /* Modern Card List Layout */
        .keluhan-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .keluhan-card {
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 24px;
            backdrop-filter: blur(16px);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .keluhan-card:hover {
            transform: translateY(-3px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .ticket-badge {
            background: rgba(99, 102, 241, 0.12);
            color: #a5b4fc;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid rgba(99, 102, 241, 0.25);
            letter-spacing: 0.02em;
        }
        .date-text {
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 500;
        }
        
        .card-body {
            margin-bottom: 20px;
        }
        .keluhan-title {
            margin: 0 0 10px 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #f8fafc;
            line-height: 1.4;
        }
        .keluhan-desc {
            margin: 0;
            font-size: 0.95rem;
            color: #cbd5e1;
            line-height: 1.6;
        }
        
        /* Image Attachment Box */
        .attachment-box {
            margin-top: 14px;
            display: inline-flex;
        }
        .attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .attachment-link:hover {
            background: rgba(59, 130, 246, 0.08);
            border-color: rgba(59, 130, 246, 0.4);
            color: #60a5fa;
        }
        
        /* Solusi / Respon Admin Box */
        .solution-box {
            background: rgba(34, 197, 94, 0.06);
            border-left: 3px solid #22c55e;
            border-radius: 4px 16px 16px 4px;
            padding: 16px 20px;
            margin-top: 18px;
        }
        .solution-box.admin-note {
            background: rgba(59, 130, 246, 0.06);
            border-left: 3px solid #3b82f6;
        }
        .solution-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #4ade80;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .solution-box.admin-note .solution-title {
            color: #60a5fa;
        }
        .solution-text {
            font-size: 0.92rem;
            color: #e2e8f0;
            line-height: 1.5;
        }
        
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .status-container {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        .status-text {
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        
        /* Status Colors */
        .status-menunggu-dot {
            background: #fbbf24;
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.6);
            animation: pulse 2s infinite;
        }
        .status-menunggu-text { color: #fcd34d; }
        
        .status-proses-dot {
            background: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.6);
            animation: pulse 2s infinite;
        }
        .status-proses-text { color: #93c5fd; }
        
        .status-selesai-dot {
            background: #22c55e;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.6);
        }
        .status-selesai-text { color: #4ade80; }
        
        .status-tidak-merespon-dot {
            background: #64748b;
        }
        .status-tidak-merespon-text { color: #94a3b8; }
        
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            background: rgba(15, 23, 42, 0.4);
            border: 1px dashed rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            max-width: 500px;
            margin: 40px auto;
        }
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 16px;
        }
        .empty-state p {
            color: #94a3b8;
            font-size: 1rem;
            margin: 0;
        }
        
        /* Mobile adjustments */
        @media (max-width: 600px) {
            .page {
                padding: 20px 16px;
            }
            .topbar {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
                margin-bottom: 24px;
            }
            .topbar-actions {
                flex-direction: column-reverse;
                align-items: stretch;
                gap: 12px;
            }
            .topbar-actions a {
                text-align: center;
            }
            .topbar-actions a.button {
                justify-content: center;
                padding: 14px;
            }
            .keluhan-card {
                padding: 20px;
                border-radius: 20px;
            }
            .keluhan-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <h1>Daftar Keluhan</h1>
            <div class="topbar-actions">
                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
                <a href="{{ route('keluhan.create') }}" class="button"><i class="fa-solid fa-plus"></i> Buat Laporan</a>
            </div>
        </div>

        <div class="keluhan-list">
            @forelse($keluhanList as $keluhan)
                @php
                    $statusKey = $keluhan->status_keluhan;
                    $statusClassDot = match ($statusKey) {
                        'menunggu' => 'status-menunggu-dot',
                        'proses' => 'status-proses-dot',
                        'selesai' => 'status-selesai-dot',
                        'tidak merespon' => 'status-tidak-merespon-dot',
                        default => 'status-tidak-merespon-dot',
                    };
                    $statusClassText = match ($statusKey) {
                        'menunggu' => 'status-menunggu-text',
                        'proses' => 'status-proses-text',
                        'selesai' => 'status-selesai-text',
                        'tidak merespon' => 'status-tidak-merespon-text',
                        default => 'status-tidak-merespon-text',
                    };
                    $statusText = match ($statusKey) {
                        'menunggu' => 'Menunggu Tanggapan',
                        'proses' => 'Sedang Diproses',
                        'selesai' => 'Selesai / Teratasi',
                        'tidak merespon' => 'Tidak Merespon',
                        default => ucfirst($statusKey),
                    };
                    $tanggalSubmit = \Carbon\Carbon::parse($keluhan->tanggal)->format('d M Y H:i');
                    $tanggalSelesai = ($statusKey === 'selesai' && !empty($keluhan->tanggal)) 
                        ? \Carbon\Carbon::parse($keluhan->tanggal)->format('d M Y H:i') 
                        : null;
                @endphp
                
                <div class="keluhan-card">
                    <div class="card-header">
                        <span class="ticket-badge">#{{ strtoupper($keluhan->nomor_tiket) }}</span>
                        <span class="date-text"><i class="fa-regular fa-calendar-days"></i> {{ $tanggalSubmit }}</span>
                    </div>
                    
                    <div class="card-body">
                        <h3 class="keluhan-title">{{ $keluhan->judul_keluhan }}</h3>
                        <p class="keluhan-desc">{{ $keluhan->isi_keluhan }}</p>
                        
                        @if(!empty($keluhan->gambar))
                            <div class="attachment-box">
                                <a href="{{ asset('administrator/page/keluhan/images/' . $keluhan->gambar) }}" target="_blank" class="attachment-link">
                                    <i class="fa-solid fa-image"></i> Lihat Lampiran Gambar
                                </a>
                            </div>
                        @endif
                        
                        @if($statusKey === 'selesai' && !empty($keluhan->masalah))
                            <div class="solution-box">
                                <div class="solution-title"><i class="fa-solid fa-circle-check"></i> Solusi dari Tim Teknis:</div>
                                <div class="solution-text">{{ $keluhan->masalah }}</div>
                            </div>
                        @elseif(!empty($keluhan->masalah))
                            <div class="solution-box admin-note">
                                <div class="solution-title"><i class="fa-solid fa-comment-dots"></i> Tanggapan / Catatan Admin:</div>
                                <div class="solution-text">{{ $keluhan->masalah }}</div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-footer">
                        <div class="status-container">
                            <span class="status-dot {{ $statusClassDot }}"></span>
                            <span class="status-text {{ $statusClassText }}">{{ $statusText }}</span>
                        </div>
                        @if($tanggalSelesai)
                            <span class="date-text" style="color: #4ade80;"><i class="fa-solid fa-check-double"></i> Selesai pada: {{ $tanggalSelesai }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">📂</div>
                    <p>Belum ada laporan keluhan yang dibuat.</p>
                </div>
            @endforelse
        </div>
        @include('partials.bottom-nav')
    </div>
</body>
</html>
