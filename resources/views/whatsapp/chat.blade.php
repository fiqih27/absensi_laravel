@extends('layouts.app')

@section('title', 'WhatsApp Chat - Sistem Absensi')

@push('styles')
<style>
    /* WhatsApp Web Style - Professional */
    .whatsapp-container {
        display: flex;
        height: calc(100vh - 180px);
        background: #f0f2f5;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        margin-top: 0.5rem;
    }

    /* Sidebar kontak */
    .contacts-sidebar {
        width: 380px;
        background: white;
        border-right: 1px solid #e9ecef;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 20px;
        background: #f0f2f5;
        border-bottom: 1px solid #e9ecef;
    }

    .sidebar-header h5 {
        color: #2d1a1a;
        font-weight: 600;
    }

    .search-box {
        padding: 10px 15px;
        background: white;
    }

    .search-box input {
        width: 100%;
        padding: 10px 15px;
        border: none;
        background: #f0f2f5;
        border-radius: 25px;
        outline: none;
        font-size: 14px;
    }

    .search-box input:focus {
        box-shadow: 0 0 0 2px rgba(196, 30, 58, 0.2);
    }

    .contacts-list {
        flex: 1;
        overflow-y: auto;
    }

    .contact-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        cursor: pointer;
        transition: background 0.2s;
        border-bottom: 1px solid #f0f2f5;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .contact-item:hover {
        background: #f5f6f8;
    }

    .contact-item.active {
        background: #e9ecef;
    }

    .contact-avatar {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #c41e3a 0%, #9b1d2c 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
        font-weight: bold;
        font-size: 18px;
        flex-shrink: 0;
    }

    .contact-info {
        flex: 1;
        min-width: 0;
    }

    .contact-name {
        font-weight: 600;
        margin-bottom: 4px;
        color: #2d1a1a;
        font-size: 15px;
    }

    .contact-last-message {
        font-size: 13px;
        color: #667781;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .contact-meta {
        text-align: right;
        flex-shrink: 0;
        margin-left: 10px;
    }

    .contact-time {
        font-size: 11px;
        color: #667781;
        white-space: nowrap;
    }

    .unread-badge {
        background: #c41e3a;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: bold;
        margin-top: 4px;
        display: inline-block;
    }

    /* Chat area */
    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #efeae2;
        position: relative;
    }

    .chat-header {
        padding: 12px 20px;
        background: #f0f2f5;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chat-header .contact-avatar {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .chat-header .contact-name {
        font-size: 16px;
        font-weight: 600;
        color: #2d1a1a;
        margin-bottom: 0;
    }

    .chat-header .status-badge {
        font-size: 12px;
        color: #25d366;
    }

    .chat-actions button {
        background: none;
        border: none;
        color: #667781;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.2s;
        margin-left: 8px;
    }

    .chat-actions button:hover {
        background: #e9ecef;
        color: #c41e3a;
    }

    .messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 20px 30px;
        display: flex;
        flex-direction: column;
        background-image: url('https://web.whatsapp.com/img/bg-chat-tile-dark_a4be512e7195b6b733d9110b408f075d.png');
        background-repeat: repeat;
    }

    /* BALON CHAT */
    .message {
        display: flex;
        margin-bottom: 8px;
        max-width: 70%;
    }

    .message.incoming {
        align-self: flex-start;
        justify-content: flex-start;
    }

    .message.outgoing {
        align-self: flex-end;
        justify-content: flex-end;
    }

    .message-bubble {
        position: relative;
        padding: 8px 12px;
        border-radius: 18px;
        word-wrap: break-word;
        max-width: 100%;
    }

    .message.incoming .message-bubble {
        background: white;
        color: #111b21;
        border-bottom-left-radius: 4px;
    }

    .message.outgoing .message-bubble {
        background: #d9fdd3;
        color: #111b21;
        border-bottom-right-radius: 4px;
    }

    .message-text {
        font-size: 14px;
        line-height: 1.4;
        margin: 0;
        white-space: pre-wrap;
    }

    .message-time {
        font-size: 10px;
        color: #667781;
        margin-top: 4px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
    }

    .chat-input-area {
        padding: 12px 20px;
        background: #f0f2f5;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .chat-input-area input {
        flex: 1;
        padding: 12px 18px;
        border: none;
        border-radius: 25px;
        outline: none;
        font-size: 14px;
        background: white;
    }

    .chat-input-area input:focus {
        box-shadow: 0 0 0 2px rgba(196, 30, 58, 0.2);
    }

    .chat-input-area button {
        background: #c41e3a;
        color: white;
        border: none;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chat-input-area button:hover {
        background: #9b1d2c;
        transform: scale(1.02);
    }

    .chat-input-area button:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }

    .empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #667781;
        text-align: center;
        background: #efeae2;
    }

    .online-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #25d366;
        border-radius: 50%;
        margin-right: 6px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { opacity: 0.5; transform: scale(0.8); }
        50% { opacity: 1; transform: scale(1.2); }
        100% { opacity: 0.5; transform: scale(0.8); }
    }

    /* Animasi fade out untuk hapus */
    .fade-out {
        animation: fadeOut 0.3s ease-out forwards;
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-20px); }
    }

    /* Animasi reload */
    .reload-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        animation: fadeInOverlay 0.2s ease-out;
    }

    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .spinner-reload {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #c41e3a;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Scrollbar */
    .messages-container::-webkit-scrollbar, .contacts-list::-webkit-scrollbar {
        width: 6px;
    }

    .messages-container::-webkit-scrollbar-track, .contacts-list::-webkit-scrollbar-track {
        background: #e9ecef;
        border-radius: 3px;
    }

    .messages-container::-webkit-scrollbar-thumb, .contacts-list::-webkit-scrollbar-thumb {
        background: #c41e3a;
        border-radius: 3px;
    }

    #unreadTotalBadge {
        background: #c41e3a;
        font-size: 11px;
        padding: 3px 8px;
    }
</style>
@endpush

@section('content')
<div class="whatsapp-container">
    <!-- Sidebar Contacts -->
    <div class="contacts-sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">
                <i class="fab fa-whatsapp text-success me-2"></i>
                WhatsApp Chats
                <span id="unreadTotalBadge" class="badge rounded-pill ms-2" style="display: none; background: #c41e3a;">0</span>
            </h5>
        </div>
        <div class="search-box">
            <input type="text" id="searchContact" placeholder="Cari kontak...">
        </div>
        <div class="contacts-list" id="contactsList">
            @forelse($conversations as $conv)
            <div class="contact-item" data-id="{{ $conv->id }}" data-phone="{{ $conv->phone_number }}" data-name="{{ $conv->contact_display }}">
                <div class="contact-avatar">
                    {{ substr($conv->contact_display, 0, 1) }}
                </div>
                <div class="contact-info">
                    <div class="contact-name">{{ $conv->contact_display }}</div>
                    <div class="contact-last-message">{{ Str::limit($conv->last_message, 40) }}</div>
                </div>
                <div class="contact-meta">
                    <div class="contact-time">{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : '-' }}</div>
                    @if($conv->unread_count > 0)
                        <div class="unread-badge">{{ $conv->unread_count }}</div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center p-4 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                Belum ada percakapan
            </div>
            @endforelse
        </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
        <div class="chat-header" id="chatHeader" style="display: none;">
            <div class="d-flex align-items-center">
                <div class="contact-avatar me-3" id="headerAvatar"></div>
                <div>
                    <div class="contact-name" id="headerName"></div>
                    <div class="status-badge">
                        <span class="online-indicator"></span> WhatsApp
                    </div>
                </div>
            </div>
            <div class="chat-actions">
                <button onclick="deleteConversation()" title="Hapus percakapan">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>

        <div class="messages-container" id="messagesContainer">
            <div class="empty-state">
                <div>
                    <i class="fab fa-whatsapp fa-4x mb-3" style="color: #25d366;"></i>
                    <h5>Pilih chat untuk memulai</h5>
                    <p class="text-muted">Klik salah satu kontak di sebelah kiri</p>
                </div>
            </div>
        </div>

        <div class="chat-input-area" id="chatInputArea" style="display: none;">
            <input type="text" id="messageInput" placeholder="Ketik pesan..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()" id="sendBtn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ==================== VARIABLES ====================
let currentConversationId = null;
let currentPhoneNumber = null;
let currentContactName = null;
let refreshInterval = null;

// ==================== LOAD CONVERSATION ====================
function loadConversation(conversationId, phoneNumber, contactName) {
    currentConversationId = conversationId;
    currentPhoneNumber = phoneNumber;
    currentContactName = contactName;

    // Update header
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('chatInputArea').style.display = 'flex';
    document.getElementById('headerName').innerText = contactName;
    document.getElementById('headerAvatar').innerText = contactName.charAt(0);

    // Highlight active contact
    document.querySelectorAll('.contact-item').forEach(el => {
        el.classList.remove('active');
    });
    const activeEl = document.querySelector(`.contact-item[data-id="${conversationId}"]`);
    if (activeEl) activeEl.classList.add('active');

    // Mark as read
    markAsRead(conversationId);

    // Fetch messages
    fetchMessages();

    // Auto refresh every 3 seconds
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(fetchMessages, 3000);
}

// ==================== MARK AS READ ====================
function markAsRead(conversationId) {
    fetch(`/whatsapp-chat/mark-read/${conversationId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const contactEl = document.querySelector(`.contact-item[data-id="${conversationId}"] .unread-badge`);
            if (contactEl) contactEl.remove();
            updateTotalUnreadBadge();
        }
    })
    .catch(error => console.error('Error:', error));
}

// ==================== FETCH MESSAGES ====================
function fetchMessages() {
    if (!currentConversationId) return;

    fetch(`/whatsapp-chat/messages/${currentConversationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMessages(data.messages);
                updateTotalUnreadBadge();
            }
        })
        .catch(error => console.error('Error:', error));
}

// ==================== RENDER MESSAGES ====================
function renderMessages(messages) {
    const container = document.getElementById('messagesContainer');
    const wasAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;

    container.innerHTML = '';

    if (messages.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>Belum ada pesan. Kirim pesan pertama!</p></div>';
        return;
    }

    messages.forEach(msg => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${msg.direction}`;
        messageDiv.dataset.id = msg.id;

        const time = new Date(msg.created_at).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const statusIcon = msg.status === 'sent' ? '✓' : (msg.status === 'delivered' ? '✓✓' : '✓');

        messageDiv.innerHTML = `
            <div class="message-bubble">
                <div class="message-text">${escapeHtml(msg.message)}</div>
                <div class="message-time">
                    ${time}
                    ${msg.direction === 'outgoing' ? `<span class="message-status">${statusIcon}</span>` : ''}
                </div>
            </div>
        `;

        container.appendChild(messageDiv);
    });

    if (wasAtBottom) {
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 100);
    }
}

// ==================== SEND MESSAGE ====================
function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();

    if (!message || !currentConversationId) return;

    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    input.disabled = true;

    fetch('/whatsapp-chat/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            conversation_id: currentConversationId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            fetchMessages();
            updateContactLastMessage(currentConversationId, message);
        } else {
            alert('Gagal mengirim: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal mengirim pesan');
    })
    .finally(() => {
        sendBtn.disabled = false;
        input.disabled = false;
        input.focus();
    });
}

// ==================== UPDATE CONTACT LAST MESSAGE ====================
function updateContactLastMessage(conversationId, message) {
    const contactEl = document.querySelector(`.contact-item[data-id="${conversationId}"]`);
    if (contactEl) {
        const lastMsgEl = contactEl.querySelector('.contact-last-message');
        if (lastMsgEl) {
            lastMsgEl.textContent = message.substring(0, 40);
        }
        const timeEl = contactEl.querySelector('.contact-time');
        if (timeEl) {
            timeEl.textContent = 'Baru saja';
        }
    }
}

// ==================== SHOW RELOAD OVERLAY ====================
function showReloadOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'reload-overlay';
    overlay.id = 'reloadOverlay';
    overlay.innerHTML = `
        <div class="spinner-reload"></div>
        <p class="mt-3 text-muted">Memuat ulang...</p>
    `;
    document.body.appendChild(overlay);

    setTimeout(() => {
        window.location.reload();
    }, 500);
}

// ==================== DELETE CONVERSATION (RELOAD) ====================
function deleteConversation() {
    if (!currentConversationId) return;
    if (!confirm('Hapus percakapan ini? Semua pesan akan hilang.')) return;

    const contactEl = document.querySelector(`.contact-item[data-id="${currentConversationId}"]`);
    if (contactEl) contactEl.classList.add('fade-out');

    fetch(`/whatsapp-chat/delete/${currentConversationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(async (response) => {
        let isSuccess = false;

        try {
            const data = await response.json();
            isSuccess = data.success ?? true;
        } catch (e) {
            isSuccess = response.ok;
        }

        if (isSuccess) {
            showToast('Percakapan berhasil dihapus', 'success');
            showReloadOverlay();
        } else {
            throw new Error('Delete gagal');
        }
    })
    .catch(error => {
        console.error('DELETE ERROR:', error);
        if (contactEl) contactEl.classList.remove('fade-out');
        showToast('Gagal menghapus percakapan', 'error');
    });
}

// ==================== UPDATE TOTAL UNREAD BADGE ====================
function updateTotalUnreadBadge() {
    let total = 0;
    document.querySelectorAll('.contact-item .unread-badge').forEach(badge => {
        total += parseInt(badge.textContent) || 0;
    });

    const badgeElement = document.getElementById('unreadTotalBadge');
    if (badgeElement) {
        if (total > 0) {
            badgeElement.textContent = total;
            badgeElement.style.display = 'inline-block';
            document.title = `(${total}) WhatsApp Chat - Sistem Absensi`;
        } else {
            badgeElement.style.display = 'none';
            document.title = 'WhatsApp Chat - Sistem Absensi';
        }
    }
}

// ==================== SHOW TOAST ====================
function showToast(message, type = 'info') {
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    toast.style.cssText = 'background: white; border-left: 4px solid #c41e3a; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 12px; padding: 12px 20px; margin-top: 10px; min-width: 250px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2" style="color: #c41e3a;"></i>
        ${message}
        <button type="button" class="btn-close float-end" data-bs-dismiss="alert" style="font-size: 10px;"></button>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// ==================== UTILITIES ====================
function handleKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==================== SEARCH CONTACTS ====================
document.getElementById('searchContact')?.addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.contact-item').forEach(el => {
        const name = el.dataset.name?.toLowerCase() || '';
        const phone = el.dataset.phone || '';
        if (name.includes(search) || phone.includes(search)) {
            el.style.display = 'flex';
        } else {
            el.style.display = 'none';
        }
    });
});

// ==================== CLICK HANDLERS ====================
document.querySelectorAll('.contact-item').forEach(el => {
    el.addEventListener('click', () => {
        const id = el.dataset.id;
        const phone = el.dataset.phone;
        const name = el.dataset.name;
        loadConversation(id, phone, name);
    });
});

// ==================== INITIAL LOAD ====================
setTimeout(() => {
    updateTotalUnreadBadge();
}, 500);
</script>
@endpush
