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
        font-size: 0.7rem;
        color: #6b7280;
        text-align: right;
        margin-top: 4px;
        display: block;
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
</style>
@endsection

@section('content')
<div class="content-header" style="margin-bottom: 16px;">
    <div class="header-left">
        <h1 class="page-title"><i class="fa-brands fa-whatsapp"></i> Chat WABA Webhook</h1>
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
                        {{ $contact->nama ?? 'Tidak Dikenal' }}
                        <span class="contact-time">{{ \Carbon\Carbon::parse($contact->last_message_time)->format('H:i') }}</span>
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
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    function scrollToBottom() {
        const messagesDiv = document.getElementById('chat-messages');
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function appendMessage(msg) {
        const isIncoming = msg.tipe === 'incoming';
        const bubbleClass = isIncoming ? 'msg-incoming' : 'msg-outgoing';
        
        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${bubbleClass}`;
        
        let mediaHtml = '';
        if (msg.media_url) {
            const proxyUrl = `{{ route('admin.waba_chat.media') }}?url=${encodeURIComponent(msg.media_url)}`;
            mediaHtml = `<div style="margin-bottom: 6px;"><a href="${proxyUrl}" target="_blank"><img src="${proxyUrl}" style="max-width: 100%; max-height: 250px; border-radius: 8px; object-fit: cover;" alt="Media" onerror="this.outerHTML='<a href=\\'${proxyUrl}\\' target=\\'_blank\\' style=\\'color:#25D366; text-decoration:none;\\'><i class=\\'fa-solid fa-paperclip\\'></i> Buka Lampiran</a>'"></a></div>`;
        }
        
        bubble.innerHTML = `${mediaHtml}<div style="word-wrap: break-word;">${msg.pesan.replace(/\\n/g, '<br>')}</div> <span class="msg-time">${formatDate(msg.created_at)}</span>`;
        
        document.getElementById('chat-messages').appendChild(bubble);
    }

    function loadChat(no_telp) {
        // Highlight active contact
        document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('active'));
        document.getElementById('contact-' + no_telp).classList.add('active');
        
        document.getElementById('no-chat-placeholder').style.display = 'none';
        document.getElementById('chat-pane').style.display = 'flex';
        document.getElementById('current_no_telp').value = no_telp;
        document.getElementById('chat-messages').innerHTML = '<div style="text-align:center; padding: 20px; color:#6b7280;">Memuat riwayat...</div>';
        
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
    
    // Handle Enter key in textarea
    document.getElementById('chat-input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendReply();
        }
    });
</script>
@endsection
