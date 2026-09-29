<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIS Assistant</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body {
            font-family: Poppins, sans-serif;
            color: #1c2b45;
            background: #fff;
        }
        .gpt {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            height: 100vh;
        }
        .gpt-public {
            grid-template-columns: 1fr;
        }
        .gpt-side {
            display: flex;
            flex-direction: column;
            background: #f7f7f8;
            border-right: 1px solid #ececf1;
            min-height: 0;
        }
        .gpt-side-top {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
        }
        .gpt-menu, .gpt-new, .gpt-back, .gpt-send {
            border: 0;
            background: transparent;
            color: #1c2b45;
            font-family: inherit;
            cursor: pointer;
        }
        .gpt-menu {
            display: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
        }
        .gpt-menu:hover, .gpt-new:hover, .gpt-back:hover { background: #ececf1; }
        .gpt-new {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-align: left;
        }
        .gpt-list {
            flex: 1;
            overflow: auto;
            padding: 4px 8px 12px;
        }
        .gpt-item {
            width: 100%;
            display: block;
            border: 0;
            background: transparent;
            color: #1c2b45;
            font-family: inherit;
            font-size: 14px;
            text-align: left;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .gpt-item:hover, .gpt-item.active { background: #ececf1; }
        .gpt-side-foot { padding: 12px; border-top: 1px solid #ececf1; }
        .gpt-back {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 13px;
            text-decoration: none;
        }
        .gpt-public-bar {
            padding: 12px 16px 0;
        }
        .gpt-back-public {
            width: auto;
            display: inline-flex;
        }
        .gpt-main {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
            background: #fff;
        }
        .gpt-scroll {
            flex: 1;
            overflow: auto;
            padding: 24px 16px 12px;
            scrollbar-width: thin;
            scrollbar-color: #b0b6c3 transparent;
        }
        .gpt-scroll::-webkit-scrollbar { width: 8px; }
        .gpt-scroll::-webkit-scrollbar-button { display: none; width: 0; height: 0; }
        .gpt-scroll::-webkit-scrollbar-track { background: transparent; margin: 6px 0; }
        .gpt-scroll::-webkit-scrollbar-thumb { background: #b0b6c3; border-radius: 999px; }
        .gpt-scroll::-webkit-scrollbar-thumb:hover { background: #8b93a7; }
        .gpt-thread, .gpt-empty {
            width: min(760px, 100%);
            margin: 0 auto;
        }
        .gpt-empty[hidden],
        .gpt-thread[hidden] {
            display: none !important;
        }
        .gpt-empty {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 18px;
            padding-bottom: 40px;
        }
        .gpt-mark {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #16325c;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .gpt-empty h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .gpt-chips {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            max-width: 640px;
        }
        .gpt-chip {
            border: 1px solid #e5e5e5;
            background: #fff;
            color: #1c2b45;
            border-radius: 999px;
            padding: 8px 14px;
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
        }
        .gpt-chip:hover { background: #f7f7f8; }
        .gpt-row { display: flex; gap: 14px; padding: 16px 0; }
        .gpt-row.user { justify-content: flex-end; }
        .gpt-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #16325c;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            font-size: 14px;
        }
        .gpt-bubble {
            max-width: min(640px, 100%);
            font-size: 15px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .gpt-row.user .gpt-bubble {
            background: #f4f4f4;
            border-radius: 18px;
            padding: 10px 14px;
        }
        .gpt-bubble ol, .gpt-bubble ul {
            margin: 8px 0 8px 1.25em;
            padding: 0;
            white-space: normal;
        }
        .gpt-bubble ol { list-style: decimal; }
        .gpt-bubble ul { list-style: disc; }
        .gpt-bubble li { display: list-item; }
        .gpt-bubble p { margin: 0 0 8px; }
        .gpt-bubble p:last-child { margin-bottom: 0; }
        .gpt-typing {
            display: flex;
            gap: 5px;
            padding-top: 8px;
        }
        .gpt-typing span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #9aa0b4;
            animation: blink 1.2s infinite;
        }
        .gpt-typing span:nth-child(2) { animation-delay: .15s; }
        .gpt-typing span:nth-child(3) { animation-delay: .3s; }
        @keyframes blink {
            0%, 80%, 100% { opacity: .25; }
            40% { opacity: 1; }
        }
        .gpt-composer-wrap {
            padding: 8px 16px 18px;
        }
        .gpt-composer {
            width: min(760px, 100%);
            margin: 0 auto;
            display: flex;
            align-items: flex-end;
            gap: 8px;
            border: 1px solid #e5e5e5;
            border-radius: 26px;
            padding: 8px 8px 8px 16px;
            box-shadow: 0 0 12px rgba(0, 0, 0, .04);
            background: #fff;
        }
        .gpt-composer textarea,
        .gpt-composer textarea::placeholder {
            flex: 1;
            border: 0;
            resize: none;
            max-height: 160px;
            overflow: hidden;
            font-family: inherit;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0;
            line-height: 1.5;
            text-transform: none;
            outline: none;
            padding: 8px 0;
            scrollbar-width: none;
        }
        .gpt-composer textarea::-webkit-scrollbar,
        .gpt-composer textarea::-webkit-scrollbar-button {
            display: none;
            width: 0;
            height: 0;
        }
        .gpt-send {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #16325c;
            color: #fff;
            flex: 0 0 auto;
        }
        .gpt-send:disabled { opacity: .4; cursor: default; }
        .gpt-note {
            width: min(760px, 100%);
            margin: 8px auto 0;
            text-align: center;
            color: #8b93a7;
            font-size: 12px;
        }
        @media (max-width: 800px) {
            .gpt { grid-template-columns: 1fr; }
            .gpt-menu { display: inline-flex; align-items: center; justify-content: center; }
            .gpt-side {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(280px, 86vw);
                z-index: 20;
                transform: translateX(-105%);
                transition: transform .2s ease;
            }
            .gpt-side.open { transform: none; }
            .gpt-empty h1 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <?php
    $dashboardUrl = (isset($dashboardUrl) && is_string($dashboardUrl)) ? $dashboardUrl : '/';
    $chatEndpoint = (isset($chatEndpoint) && is_string($chatEndpoint)) ? $chatEndpoint : '/api/chatbot/chat';
    $chatSource   = (isset($chatSource) && is_string($chatSource)) ? $chatSource : '';
    $isPublicChat = $chatSource === 'landing';
    $backLabel    = (isset($backLabel) && is_string($backLabel)) ? $backLabel : ($isPublicChat ? 'Back to home' : 'Back to dashboard');
    ?>
    <div class="gpt<?= $isPublicChat ? ' gpt-public' : '' ?>">
        <?php if (! $isPublicChat): ?>
        <aside class="gpt-side" id="gptSide">
            <div class="gpt-side-top">
                <button class="gpt-new" id="gptNew" type="button">
                    <i class="fas fa-plus"></i> New chat
                </button>
            </div>
            <div class="gpt-list" id="gptList"></div>
            <div class="gpt-side-foot">
                <a class="gpt-back" href="<?= esc($dashboardUrl) ?>">
                    <i class="fas fa-arrow-left"></i> <?= esc($backLabel ?? 'Back to dashboard') ?>
                </a>
            </div>
        </aside>
        <?php endif; ?>
        <main class="gpt-main">
            <?php if ($isPublicChat): ?>
            <div class="gpt-public-bar">
                <a class="gpt-back gpt-back-public" href="<?= esc($dashboardUrl) ?>">
                    <i class="fas fa-arrow-left"></i> <?= esc($backLabel ?? 'Back to home') ?>
                </a>
            </div>
            <?php endif; ?>
            <div class="gpt-scroll" id="gptScroll">
                <div class="gpt-empty" id="gptEmpty">
                    <div class="gpt-mark"><i class="fas fa-comment-dots"></i></div>
                    <h1>How can I help you?</h1>
                    <div class="gpt-chips">
                        <button class="gpt-chip" type="button">What documents can I request?</button>
                        <button class="gpt-chip" type="button">What events are on the calendar?</button>
                        <button class="gpt-chip" type="button">How do I send a concern?</button>
                        <button class="gpt-chip" type="button">What are the office hours?</button>
                    </div>
                </div>
                <div class="gpt-thread" id="gptThread" hidden></div>
            </div>
            <div class="gpt-composer-wrap">
                <form class="gpt-composer" id="gptForm">
                    <?php if (! $isPublicChat): ?>
                    <button class="gpt-menu" id="gptMenu" type="button" aria-label="Open chats">
                        <i class="fas fa-bars"></i>
                    </button>
                    <?php endif; ?>
                    <textarea id="gptInput" rows="1" placeholder="Message BIS Assistant"></textarea>
                    <button class="gpt-send" id="gptSend" type="submit" aria-label="Send" disabled>
                        <i class="fas fa-arrow-up"></i>
                    </button>
                </form>
                <p class="gpt-note">BIS Assistant answers questions about Barangay Bacolod services.</p>
            </div>
        </main>
    </div>
    <script>
        const chatEndpoint = <?= json_encode($chatEndpoint) ?>;
        const chatSource = <?= json_encode($chatSource ?? '') ?>;
        const thread = document.getElementById('gptThread');
        const empty = document.getElementById('gptEmpty');
        const list = document.getElementById('gptList');
        const input = document.getElementById('gptInput');
        const sendBtn = document.getElementById('gptSend');
        const form = document.getElementById('gptForm');
        const side = document.getElementById('gptSide');
        let conversationId = null;
        let sending = false;

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function resizeInput() {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 160) + 'px';
            sendBtn.disabled = sending || input.value.trim() === '';
        }

        function showEmpty(on) {
            empty.hidden = !on;
            thread.hidden = on;
        }

        function scrollDown() {
            const box = document.getElementById('gptScroll');
            box.scrollTop = box.scrollHeight;
        }

        function addMessage(text, isUser) {
            showEmpty(false);
            const row = document.createElement('div');
            row.className = 'gpt-row' + (isUser ? ' user' : '');
            if (!isUser) {
                const avatar = document.createElement('div');
                avatar.className = 'gpt-avatar';
                avatar.innerHTML = '<i class="fas fa-robot"></i>';
                row.appendChild(avatar);
            }
            const bubble = document.createElement('div');
            bubble.className = 'gpt-bubble';
            if (isUser) {
                bubble.textContent = text;
            } else {
                bubble.innerHTML = text;
            }
            row.appendChild(bubble);
            thread.appendChild(row);
            scrollDown();
        }

        function setTyping(on) {
            document.getElementById('gptTyping')?.remove();
            if (!on) return;
            showEmpty(false);
            const row = document.createElement('div');
            row.className = 'gpt-row';
            row.id = 'gptTyping';
            row.innerHTML = '<div class="gpt-avatar"><i class="fas fa-robot"></i></div><div class="gpt-typing"><span></span><span></span><span></span></div>';
            thread.appendChild(row);
            scrollDown();
        }

        function renderList(conversations) {
            if (!list) return;
            list.innerHTML = '';
            conversations.forEach(function (item) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'gpt-item' + (Number(item.id) === Number(conversationId) ? ' active' : '');
                button.textContent = item.title || 'New chat';
                button.addEventListener('click', function () {
                    side?.classList.remove('open');
                    loadConversation(item.id);
                });
                list.appendChild(button);
            });
        }

        async function loadHistory(openActive) {
            const response = await fetch('/api/chatbot/history?_=' + Date.now(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const data = await response.json();
            const conversations = Array.isArray(data.conversations) ? data.conversations : [];
            if (openActive && data.active_conversation_id) {
                conversationId = Number(data.active_conversation_id);
                thread.innerHTML = '';
                (data.messages || []).forEach(function (item) {
                    const sender = String(item.sender || '').toLowerCase();
                    addMessage(item.message || '', sender === 'user' || sender === 'resident');
                });
                if ((data.messages || []).length === 0) showEmpty(true);
            }
            renderList(conversations);
        }

        async function loadConversation(id) {
            const response = await fetch('/api/chatbot/conversation/' + encodeURIComponent(id) + '?_=' + Date.now(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const data = await response.json();
            if (!data || data.success !== true) return;
            conversationId = Number(id);
            thread.innerHTML = '';
            (data.messages || []).forEach(function (item) {
                const sender = String(item.sender || '').toLowerCase();
                addMessage(item.message || '', sender === 'user' || sender === 'resident');
            });
            if ((data.messages || []).length === 0) showEmpty(true);
            loadHistory(false);
        }

        async function sendMessage(text) {
            const message = text.trim();
            if (message === '' || sending) return;
            sending = true;
            resizeInput();
            input.value = '';
            resizeInput();
            addMessage(message, true);
            setTyping(true);
            try {
                const body = new URLSearchParams();
                body.append('message', message);
                body.append('conversation_id', conversationId ? String(conversationId) : '');
                if (chatSource) body.append('source', chatSource);
                const response = await fetch(chatEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: body.toString()
                });
                const data = await response.json();
                setTyping(false);
                if (data && Number(data.conversation_id) > 0) {
                    conversationId = Number(data.conversation_id);
                }
                addMessage(
                    (data && data.response) ? data.response : 'Sorry, I could not answer that. Please try again.',
                    false
                );
                loadHistory(false);
            } catch (error) {
                setTyping(false);
                addMessage('Sorry, I could not connect to the BIS service. Please try again.', false);
            } finally {
                sending = false;
                resizeInput();
                input.focus();
            }
        }

        const newChat = document.getElementById('gptNew');
        if (newChat) {
            newChat.addEventListener('click', function () {
                conversationId = null;
                thread.innerHTML = '';
                showEmpty(true);
                side?.classList.remove('open');
                document.querySelectorAll('.gpt-item').forEach(function (item) {
                    item.classList.remove('active');
                });
                input.focus();
            });
        }
        const menuBtn = document.getElementById('gptMenu');
        if (menuBtn) {
            menuBtn.addEventListener('click', function () {
                side?.classList.toggle('open');
            });
        }
        document.querySelectorAll('.gpt-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                sendMessage(chip.textContent);
            });
        });
        input.addEventListener('input', resizeInput);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage(input.value);
            }
        });
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            sendMessage(input.value);
        });
        loadHistory(true).then(function () {
            const starter = new URLSearchParams(window.location.search).get('q');
            if (starter) {
                history.replaceState({}, '', window.location.pathname);
                sendMessage(starter);
                return;
            }
            input.focus();
        });
    </script>
</body>
</html>
