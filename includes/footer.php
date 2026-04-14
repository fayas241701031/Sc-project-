</main>
<footer class="bg-white border-top py-5" style="background: var(--surface-solid) !important; border-color: var(--border-color) !important;">
    <div class="container text-center text-muted small">
        <div class="mb-3">
            <a href="<?= BASE_URL ?>/index.php" class="navbar-brand text-muted fw-bold me-0">Bazaar</a>
        </div>
        <p class="mb-1">Bazaar &copy; <?= date('Y') ?>. Modern Multi-Vendor Ecosystem.</p>
        <p class="mb-0 overflow-hidden text-nowrap">Built with Apple-style Minimalism, PHP, and Motion.dev.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- PREMIUM CHATBOT UI -->
<style>
    .chat-bubble-btn {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 30px rgba(0, 122, 255, 0.3);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        z-index: 1050;
    }
    .chat-bubble-btn:hover { transform: scale(1.1); box-shadow: 0 12px 40px rgba(0, 122, 255, 0.4); }
    
    .chat-window {
        position: fixed;
        bottom: 100px; right: 30px;
        width: 380px; height: 600px;
        max-height: calc(100vh - 150px);
        background: var(--surface);
        backdrop-filter: blur(20px);
        border: var(--glass-border);
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 1050;
        transform-origin: bottom right;
    }
    
    .chat-header {
        padding: 20px;
        background: rgba(0, 122, 255, 0.1);
        border-bottom: 1px solid var(--border-color);
        display: flex; align-items: center; justify-content: space-between;
    }
    
    .chat-messages {
        flex-grow: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex; flex-direction: column; gap: 12px;
        scrollbar-width: thin;
    }
    
    .message {
        max-width: 80%; padding: 12px 16px;
        border-radius: 18px;
        font-size: 0.95rem;
        line-height: 1.4;
    }
    .message.user {
        align-self: flex-end;
        background: var(--primary-color); color: white;
        border-bottom-right-radius: 4px;
    }
    .message.bot {
        align-self: flex-start;
        background: var(--surface-solid); color: var(--text-main);
        border-bottom-left-radius: 4px;
        border: 1px solid var(--border-color);
    }
    
    .chat-footer {
        padding: 20px;
        border-top: 1px solid var(--border-color);
    }
    
    .chat-input-wrapper {
        background: var(--surface-solid);
        border-radius: 14px;
        padding: 5px 15px;
        display: flex; align-items: center;
        border: 1px solid var(--border-color);
    }
    .chat-input-wrapper input {
        border: none; background: transparent; padding: 10px 0;
        width: 100%; color: var(--text-main); font-size: 0.95rem;
    }
    .chat-input-wrapper input:focus { outline: none; }
</style>

<div id="chatbot-wrapper" class="position-fixed bottom-0 end-0 m-4" style="z-index: 1100;">
    <!-- Chat Window -->
    <div class="chat-window" id="chatWindow">
        <div class="chat-header">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; color: white;">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h6 class="fw-bold m-0" style="color: var(--text-main);">Bazaar AI Hub</h6>
                    <span class="small text-success d-flex align-items-center gap-1"><span class="rounded-circle bg-success" style="width: 6px; height: 6px;"></span> Always Online</span>
                </div>
            </div>
            <button class="btn btn-link text-muted p-0 border-0" id="closeChatBtn"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message bot">
                Hi! 👋 I'm your Bazaar guide. How can I assist your premium shopping experience today?
            </div>
        </div>
        
        <div class="chat-footer">
            <form id="chatForm">
                <div class="chat-input-wrapper">
                    <input type="text" id="chatInput" placeholder="Ask about products or shipping..." required autocomplete="off">
                    <button type="submit" class="btn btn-link px-2 py-0 border-0" style="color: var(--primary-color);">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bubble Button -->
    <div class="chat-bubble-btn" id="chatBubbleBtn">
        <i class="fas fa-comment-alt fs-4"></i>
    </div>
</div>

<script type="module">
document.addEventListener('DOMContentLoaded', function() {
    const chatBubble = document.getElementById('chatBubbleBtn');
    const chatWindow = document.getElementById('chatWindow');
    const closeBtn = document.getElementById('closeChatBtn');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');

    let chatOpen = false;

    chatBubble.addEventListener('click', () => {
        if(!chatOpen) {
            chatWindow.style.display = 'flex';
            window.animate(chatWindow, { opacity: [0, 1], scale: [0.8, 1], y: [40, 0] }, { duration: 0.5, easing: window.spring() });
            chatOpen = true;
        } else {
            closeChat();
        }
    });

    closeBtn.addEventListener('click', closeChat);

    function closeChat() {
        window.animate(chatWindow, { opacity: [1, 0], scale: [1, 0.8], y: [0, 40] }, { duration: 0.3 }).then(() => {
            chatWindow.style.display = 'none';
        });
        chatOpen = false;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;

        addMessage('user', message);
        chatInput.value = '';

        // Bot typing indicator placeholder
        const typingId = 'typing_' + Date.now();
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot small text-muted';
        typingDiv.id = typingId;
        typingDiv.innerText = 'Bazaar Bot is thinking...';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById(typingId).remove();
            addMessage('bot', data.response);
        })
        .catch(error => {
            document.getElementById(typingId).remove();
            addMessage('bot', 'Apologies, my system had a minor glitch. Please try again.');
        });
    });

    function addMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${sender}`;
        msgDiv.innerText = text;
        chatMessages.appendChild(msgDiv);
        window.animate(msgDiv, { opacity: [0, 1], x: [sender === 'user' ? 20 : -20, 0] }, { duration: 0.4 });
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});
</script>

</body>
</html>
