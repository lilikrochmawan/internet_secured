@extends('layouts.admin')

@section('title', 'Chat WABA Webhook')

@section('styles')
<style>
    .chat-app {
        display: flex;
        height: 75vh;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    
    /* Left Panel: Contact List */
    .chat-sidebar {
        width: 320px;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        background: #f9fafb;
    }
    .sidebar-header {
        padding: 16px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .contact-list {
        flex: 1;
        overflow-y: auto;
    }
    .contact-item {
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        transition: background 0.2s;
    }
    .contact-item:hover, .contact-item.active {
        background: #f3f4f6;
    }
    .contact-item.active {
        border-left: 4px solid var(--primary-color);
        padding-left: 12px;
    }
    .contact-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
        display: flex;
        justify-content: space-between;
    }
    .contact-time {
        font-size: 0.75rem;
        color: #9ca3af;
        font-weight: normal;
    }
    .contact-msg-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 4px;
    }
    .contact-msg {
        font-size: 0.85rem;
        color: #6b7280;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
    }
    .unread-badge {
        background-color: #25D366;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 0.7rem;
        font-weight: bold;
        min-width: 20px;
        text-align: center;
        margin-left: 8px;
    }
    
    /* Right Panel: Chat Room */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #efeae2; /* WA web background color */
        background-image: url('https://web.whatsapp.com/img/bg-chat-tile-light_04fcacde539c58cca6745483d4858c52.png');
    }
    .chat-header {
        padding: 16px 24px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 12px;
        height: 65px;
    }
    .chat-header-name {
        font-weight: 600;
        font-size: 1.1rem;
        color: #1f2937;
    }
    .chat-header-phone {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .chat-messages {
        flex: 1;
        padding: 24px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .message-bubble {
        max-width: 75%;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.95rem;
        line-height: 1.4;
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    .msg-incoming {
        align-self: flex-start;
        background: #fff;
        border-top-left-radius: 0;
    }
    .msg-outgoing {
        align-self: flex-end;
        background: #d9fdd3;
        border-top-right-radius: 0;
    }
    .msg-time {
        font-size: 0.72rem;
        color: #64748b;
        text-align: right;
        margin-top: 5px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    .chat-date-separator {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 14px 0 8px 0;
        width: 100%;
        clear: both;
    }
    .chat-date-badge {
        background: #ffffff;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.2px;
    }
    
    .chat-input-area {
        padding: 16px 24px;
        background: #f0f2f5;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .chat-input-controls {
        display: flex;
        gap: 12px;
        align-items: flex-end;
        width: 100%;
    }
    .file-preview {
        display: none;
        background: #fff;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
    }
    .file-preview-close {
        cursor: pointer;
        color: #ef4444;
    }
    .chat-textarea {
        flex: 1;
        border: none;
        border-radius: 8px;
        padding: 12px 16px;
        resize: none;
        outline: none;
        font-family: inherit;
        font-size: 0.95rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        max-height: 120px;
        overflow-y: auto;
    }
    .btn-send {
        background: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s;
        flex-shrink: 0;
    }
    .btn-send:hover {
        transform: scale(1.05);
    }
    .btn-send:disabled {
        background: #9ca3af;
        cursor: not-allowed;
        transform: none;
    }
    
    .no-chat-selected {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        color: #6b7280;
    }
    .btn-back-chat {
        display: none;
    }
    
    @media (max-width: 768px) {
        .chat-app {
            height: calc(100vh - 120px);
            border-radius: 8px;
            flex-direction: column;
        }
        .chat-sidebar {
            width: 100%;
            height: 100%;
        }
        .chat-main {
            display: none !important;
            width: 100%;
            height: 100%;
        }
        
        .chat-app.chat-active .chat-sidebar {
            display: none !important;
        }
        .chat-app.chat-active .chat-main {
            display: flex !important;
        }
        
        .btn-back-chat {
            display: block;
            background: none;
            border: none;
            color: #374151;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 4px 8px;
        }
    }
</style>
@endsection

@section('content')
<div class="content-header" style="margin-bottom: 16px;">
    <div class="header-left" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <h1 class="page-title"><i class="fa-brands fa-whatsapp"></i> Chat WABA Webhook</h1>
        <button type="button" id="toggle-sound-btn" onclick="toggleSoundNotification()" class="btn btn-sm btn-outline-success" style="border-radius: 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #fff; border: 1px solid #10b981; color: #059669; cursor: pointer; transition: all 0.2s;" title="Klik untuk mengaktifkan / menonaktifkan suara notifikasi">
            <i class="fa-solid fa-volume-high" id="sound-icon"></i> <span id="sound-text">Suara: Aktif</span>
        </button>
    </div>
</div>

<div class="chat-app">
    <!-- Left Sidebar: Contacts -->
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-address-book"></i> Daftar Pesan
        </div>
        <div class="contact-list">
            @forelse($contacts as $contact)
                <div class="contact-item" onclick="loadChat('{{ $contact->no_telp }}')" id="contact-{{ $contact->no_telp }}">
                    <div class="contact-name">
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180px;" title="{{ $contact->nama ?? 'Tidak Dikenal' }}">{{ $contact->nama ?? 'Tidak Dikenal' }}</span>
                        @php
                            $lastTime = \Carbon\Carbon::parse($contact->last_message_time);
                            if ($lastTime->isToday()) {
                                $displayTime = $lastTime->format('H:i');
                            } elseif ($lastTime->isYesterday()) {
                                $displayTime = 'Kemarin';
                            } elseif ($lastTime->isCurrentYear()) {
                                $displayTime = $lastTime->translatedFormat('d M');
                            } else {
                                $displayTime = $lastTime->format('d/m/Y');
                            }
                        @endphp
                        <span class="contact-time" title="{{ $lastTime->translatedFormat('d F Y H:i') }}">{{ $displayTime }}</span>
                    </div>
                    <div class="contact-msg-row">
                        <div class="contact-msg">{{ $contact->latest_pesan }}</div>
                        @if($contact->unread_count > 0)
                            <div class="unread-badge" id="badge-{{ $contact->no_telp }}">{{ $contact->unread_count }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="padding: 20px; text-align: center; color: #9ca3af; font-size: 0.9rem;">
                    Belum ada riwayat pesan WABA masuk.
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Right Main: Chat Area -->
    <div class="chat-main" id="chat-pane" style="display: none;">
        <div class="chat-header">
            <button class="btn-back-chat" onclick="closeChatMobile()"><i class="fa-solid fa-arrow-left"></i></button>
            <i class="fa-solid fa-circle-user" style="font-size: 2rem; color: #9ca3af;"></i>
            <div>
                <div class="chat-header-name" id="active-chat-name">Nama Pelanggan</div>
                <div class="chat-header-phone" id="active-chat-phone">Nomor Telepon</div>
            </div>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <!-- Messages loaded via AJAX -->
        </div>
        
        <div class="chat-input-area">
            <div id="file-preview-container" class="file-preview">
                <i class="fa-solid fa-file"></i> <span id="file-preview-name"></span>
                <i class="fa-solid fa-times file-preview-close" onclick="clearFile()"></i>
            </div>
            <div class="chat-input-controls">
                <button type="button" class="btn btn-light" style="border-radius: 50%; width: 45px; height: 45px; flex-shrink: 0;" onclick="document.getElementById('chat-file').click()">
                    <i class="fa-solid fa-paperclip"></i>
                </button>
                <input type="file" id="chat-file" style="display: none;" onchange="handleFileSelect(this)" accept="image/*,application/pdf,video/mp4">
                <textarea id="chat-input" class="chat-textarea" rows="1" placeholder="Ketik pesan balasan..."></textarea>
                <button id="btn-send-reply" class="btn-send" onclick="sendReply()">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Placeholder when no chat selected -->
    <div class="no-chat-selected" id="no-chat-placeholder">
        <i class="fa-brands fa-whatsapp" style="font-size: 5rem; color: #d1d5db; margin-bottom: 20px;"></i>
        <h3 style="margin-bottom: 10px; color: #374151;">Pilih pesan untuk mulai membalas</h3>
        <p>Sistem ini terhubung langsung dengan Webhook Bablast.</p>
    </div>
</div>

<input type="hidden" id="current_no_telp" value="">
@csrf
@endsection

@section('scripts')
<script>
    // Audio notification & Web Audio API
    let soundEnabled = localStorage.getItem('waba_chat_sound') !== 'disabled';
    let audioCtx = null;

    function initAudioContext() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    function toggleSoundNotification() {
        soundEnabled = !soundEnabled;
        localStorage.setItem('waba_chat_sound', soundEnabled ? 'enabled' : 'disabled');
        updateSoundButtonUI();
        if (soundEnabled) {
            initAudioContext();
            playWhatsAppSound();
        }
    }

    function updateSoundButtonUI() {
        const btn = document.getElementById('toggle-sound-btn');
        const icon = document.getElementById('sound-icon');
        const text = document.getElementById('sound-text');
        if (!btn || !icon || !text) return;

        if (soundEnabled) {
            btn.style.borderColor = '#10b981';
            btn.style.color = '#059669';
            icon.className = 'fa-solid fa-volume-high';
            text.innerText = 'Suara: Aktif';
        } else {
            btn.style.borderColor = '#9ca3af';
            btn.style.color = '#6b7280';
            icon.className = 'fa-solid fa-volume-xmark';
            text.innerText = 'Suara: Senyap';
        }
    }

    function playWhatsAppSound() {
        if (!soundEnabled) return;
        try {
            initAudioContext();
            if (!audioCtx) return;

            const now = audioCtx.currentTime;

            // Note 1 (800Hz - soft bell ding)
            const osc1 = audioCtx.createOscillator();
            const gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(800, now);
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);
            osc1.start(now);
            osc1.stop(now + 0.12);

            // Note 2 (1200Hz - bright bell chime)
            const osc2 = audioCtx.createOscillator();
            const gain2 = audioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1200, now + 0.08);
            gain2.gain.setValueAtTime(0.35, now + 0.08);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
            osc2.start(now + 0.08);
            osc2.stop(now + 0.5);
        } catch (e) {
            console.error('Audio playback error:', e);
        }
    }

    // Unlock Audio Context on first user click anywhere
    document.addEventListener('click', function unlockAudio() {
        initAudioContext();
        document.removeEventListener('click', unlockAudio);
    }, { once: true });

    function parseChatDate(dateString) {
        if (!dateString) return new Date();
        const safe = typeof dateString === 'string' ? dateString.replace(' ', 'T') : dateString;
        const d = new Date(safe);
        return isNaN(d.getTime()) ? new Date() : d;
    }

    function getDateKey(dateString) {
        const d = parseChatDate(dateString);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function formatDateDivider(dateString) {
        const d = parseChatDate(dateString);
        const now = new Date();
        
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const msgDate = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        const diffMs = today - msgDate;
        const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) {
            return 'Hari Ini';
        } else if (diffDays === 1) {
            return 'Kemarin';
        } else {
            return d.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }
    }

    function formatTimeWithDate(dateString) {
        const d = parseChatDate(dateString);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        
        return `${day}/${month}/${year} ${hours}.${minutes}`;
    }

    function formatFullDateTime(dateString) {
        const d = parseChatDate(dateString);
        return d.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        }) + ' pukul ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    let lastChatDate = null;
    let currentMaxChatId = 0;
    let lastIncomingId = {{ \App\Models\WabaChat::where('tipe', 'incoming')->max('id') ?? 0 }};
    let isPolling = false;

    function scrollToBottom() {
        const messagesDiv = document.getElementById('chat-messages');
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function renderMessageStatus(msg) {
        if (msg.tipe !== 'outgoing') {
            return '';
        }

        const status = (msg.status || 'sent').toLowerCase();

        if (status === 'read') {
            // Centang 2 Biru (Sudah dibaca)
            return '<i class="fa-solid fa-check-double" style="color: #53bdeb; margin-left: 5px; font-size: 0.85rem;" title="Sudah Dibaca"></i>';
        } else if (status === 'delivered') {
            // Centang 2 Abu-abu (Terkirim & Diterima di perangkat)
            return '<i class="fa-solid fa-check-double" style="color: #8696a0; margin-left: 5px; font-size: 0.85rem;" title="Terkirim & Diterima"></i>';
        } else if (status === 'failed') {
            // Gagal Terkirim
            return '<i class="fa-solid fa-circle-exclamation" style="color: #ef4444; margin-left: 5px; font-size: 0.8rem;" title="Gagal Terkirim"></i>';
        } else {
            // Centang 1 Abu-abu (Pesan terkirim, belum diterima)
            return '<i class="fa-solid fa-check" style="color: #8696a0; margin-left: 5px; font-size: 0.85rem;" title="Pesan Terkirim (Belum Diterima)"></i>';
        }
    }

    function appendMessage(msg) {
        const isIncoming = msg.tipe === 'incoming';
        const bubbleClass = isIncoming ? 'msg-incoming' : 'msg-outgoing';
        
        const dateKey = getDateKey(msg.created_at);
        if (dateKey !== lastChatDate) {
            lastChatDate = dateKey;
            const divider = document.createElement('div');
            divider.className = 'chat-date-separator';
            divider.innerHTML = `<span class="chat-date-badge"><i class="fa-regular fa-calendar-days" style="margin-right: 5px; color: #64748b;"></i>${formatDateDivider(msg.created_at)}</span>`;
            document.getElementById('chat-messages').appendChild(divider);
        }

        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${bubbleClass}`;
        
        let mediaHtml = '';
        if (msg.media_url) {
            const proxyUrl = `{{ route('admin.waba_chat.media') }}?url=${encodeURIComponent(msg.media_url)}`;
            mediaHtml = `<div style="margin-bottom: 6px;"><a href="${proxyUrl}" target="_blank"><img src="${proxyUrl}" style="max-width: 100%; max-height: 250px; border-radius: 8px; object-fit: cover;" alt="Media" onerror="this.outerHTML='<a href=\\'${proxyUrl}\\' target=\\'_blank\\' style=\\'color:#25D366; text-decoration:none;\\'><i class=\\'fa-solid fa-paperclip\\'></i> Buka Lampiran</a>'"></a></div>`;
        }
        
        const safePesan = (msg.pesan || '').replace(/\\n/g, '<br>');
        const statusHtml = renderMessageStatus(msg);
        bubble.innerHTML = `${mediaHtml}<div style="word-wrap: break-word;">${safePesan}</div><span class="msg-time" title="${formatFullDateTime(msg.created_at)}"><i class="fa-regular fa-clock" style="font-size: 0.65rem; opacity: 0.6; margin-right: 3px;"></i>${formatTimeWithDate(msg.created_at)}<span class="msg-status-icon" id="msg-status-${msg.id}">${statusHtml}</span></span>`;
        
        document.getElementById('chat-messages').appendChild(bubble);

        if (msg.id && msg.id > currentMaxChatId) {
            currentMaxChatId = msg.id;
        }
    }

    function closeChatMobile() {
        document.querySelector('.chat-app').classList.remove('chat-active');
    }

    function loadChat(no_telp) {
        // Mobile UI switch
        document.querySelector('.chat-app').classList.add('chat-active');
        
        // Highlight active contact
        document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('active'));
        const activeItem = document.getElementById('contact-' + no_telp);
        if (activeItem) activeItem.classList.add('active');
        
        document.getElementById('no-chat-placeholder').style.display = 'none';
        document.getElementById('chat-pane').style.display = 'flex';
        document.getElementById('current_no_telp').value = no_telp;
        document.getElementById('chat-messages').innerHTML = '<div style="text-align:center; padding: 20px; color:#6b7280;"><i class="fa-solid fa-spinner fa-spin"></i> Memuat riwayat...</div>';
        
        // Remove unread badge visually since we are reading it
        const badge = document.getElementById('badge-' + no_telp);
        if (badge) {
            badge.remove();
        }
        
        fetch(`{{ url('/administrator/waba-chat/load') }}/${no_telp}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('active-chat-name').innerText = data.contact.nama;
                    document.getElementById('active-chat-phone').innerText = '+' + data.contact.no_telp;
                    
                    document.getElementById('chat-messages').innerHTML = '';
                    lastChatDate = null;
                    currentMaxChatId = 0;
                    data.messages.forEach(msg => {
                        appendMessage(msg);
                    });
                    
                    scrollToBottom();
                }
            })
            .catch(err => {
                document.getElementById('chat-messages').innerHTML = '<div style="text-align:center; padding: 20px; color:red;">Gagal memuat riwayat chat.</div>';
            });
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('file-preview-name').innerText = file.name;
            document.getElementById('file-preview-container').style.display = 'flex';
        }
    }

    function clearFile() {
        document.getElementById('chat-file').value = '';
        document.getElementById('file-preview-container').style.display = 'none';
        document.getElementById('file-preview-name').innerText = '';
    }

    function sendReply() {
        const no_telp = document.getElementById('current_no_telp').value;
        const pesan = document.getElementById('chat-input').value.trim();
        const fileInput = document.getElementById('chat-file');
        
        if (!no_telp) return;
        if (!pesan && (!fileInput.files || fileInput.files.length === 0)) return;

        const btnSend = document.getElementById('btn-send-reply');
        btnSend.disabled = true;
        btnSend.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        const formData = new FormData();
        formData.append('no_telp', no_telp);
        formData.append('pesan', pesan);
        if (fileInput.files && fileInput.files[0]) {
            formData.append('media', fileInput.files[0]);
        }
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`{{ route('admin.waba_chat.reply') }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btnSend.disabled = false;
            btnSend.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
            if (data.success || data.status === 'success') {
                document.getElementById('chat-input').value = '';
                clearFile();
                if (data.data) {
                    appendMessage(data.data);
                    scrollToBottom();
                } else if (data.message && data.message.tipe) {
                    appendMessage(data.message);
                    scrollToBottom();
                }
            } else {
                alert('Gagal mengirim pesan');
            }
        })
        .catch(err => {
            btnSend.disabled = false;
            btnSend.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
            alert('Terjadi kesalahan jaringan');
        });
    }

    // Auto-polling for real-time refresh & notifications
    function pollWabaUpdates() {
        if (isPolling) return;
        isPolling = true;

        const activePhone = document.getElementById('current_no_telp').value;
        const url = `{{ route('admin.waba_chat.poll') }}?no_telp=${encodeURIComponent(activePhone)}&last_id=${currentMaxChatId}&last_incoming_id=${lastIncomingId}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                isPolling = false;
                if (data.status !== 'success') return;

                // 1. Play sound notification if new incoming message arrives
                if (data.has_new_incoming) {
                    playWhatsAppSound();
                }
                if (data.latest_incoming_id) {
                    lastIncomingId = data.latest_incoming_id;
                }

                // 2. Update sidebar badge
                updateSidebarBadge(data.total_unread);

                // 3. Update Left Contact List
                if (data.contacts && data.contacts.length > 0) {
                    renderContactList(data.contacts, activePhone);
                }

                // 4. Update Chat Room in real-time
                if (activePhone && data.new_messages && data.new_messages.length > 0) {
                    data.new_messages.forEach(msg => {
                        appendMessage(msg);
                    });
                    scrollToBottom();
                }

                // 5. Update outgoing message checkmark statuses (centang 1 -> 2 -> biru)
                if (activePhone && data.updated_statuses && data.updated_statuses.length > 0) {
                    data.updated_statuses.forEach(item => {
                        const el = document.getElementById(`msg-status-${item.id}`);
                        if (el) {
                            el.innerHTML = renderMessageStatus({ tipe: 'outgoing', status: item.status });
                        }
                    });
                }
            })
            .catch(err => {
                isPolling = false;
            });
    }

    function renderContactList(contacts, activePhone) {
        const listDiv = document.querySelector('.contact-list');
        if (!listDiv) return;

        let html = '';
        contacts.forEach(c => {
            const isActive = (c.no_telp === activePhone) ? 'active' : '';
            const unreadBadge = (c.unread_count > 0 && c.no_telp !== activePhone) 
                ? `<div class="unread-badge" id="badge-${c.no_telp}">${c.unread_count}</div>` 
                : '';
            
            html += `
                <div class="contact-item ${isActive}" onclick="loadChat('${c.no_telp}')" id="contact-${c.no_telp}">
                    <div class="contact-name">
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180px;" title="${c.nama || 'Tidak Dikenal'}">${c.nama || 'Tidak Dikenal'}</span>
                        <span class="contact-time">${c.time_display || ''}</span>
                    </div>
                    <div class="contact-msg-row">
                        <div class="contact-msg">${c.latest_pesan || ''}</div>
                        ${unreadBadge}
                    </div>
                </div>
            `;
        });

        listDiv.innerHTML = html;
    }

    function updateSidebarBadge(totalUnread) {
        const sidebarLinks = document.querySelectorAll('.submenu-item a[href*="waba-chat"]');
        sidebarLinks.forEach(link => {
            let badge = link.querySelector('.badge-danger');
            if (totalUnread > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge badge-danger';
                    badge.style.cssText = 'margin-left: 5px; background-color: #dc3545; color: white; border-radius: 50%; padding: 3px 6px; font-size: 0.75rem;';
                    link.appendChild(badge);
                }
                badge.innerText = totalUnread;
            } else if (badge) {
                badge.remove();
            }
        });
    }

    // Initialize UI and start auto-refresh interval
    document.addEventListener('DOMContentLoaded', function() {
        updateSoundButtonUI();
        // Auto-refresh poll every 3 seconds
        setInterval(pollWabaUpdates, 3000);
    });

    // Handle Enter key in textarea
    document.getElementById('chat-input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendReply();
        }
    });
</script>
@endsection
