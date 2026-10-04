<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIS Assistant - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        body.db-body:has(.resident-chat-page) {
            height: 100vh;
            overflow: hidden;
        }

        body.db-body:has(.resident-chat-page) .db-main {
            height: 100vh;
            min-height: 0;
            overflow: hidden;
        }

        .resident-chat-page {
            padding: 0;
            display: flex;
            flex-direction: column;
            flex: 1;
            height: auto;
            min-height: 0;
            overflow: hidden;
            background: #fff;
        }

        .resident-assistant {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: row;
            background: #fff;
            font-family: Poppins, sans-serif;
            color: #1c2b45;
        }

        .resident-assistant .gpt-side {
            width: 260px;
            flex: 0 0 260px;
            display: flex;
            flex-direction: column;
            min-height: 0;
            background: #fff;
            border-right: 1px solid #ececec;
        }

        .resident-assistant .gpt-side-top {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
        }

        .resident-assistant .gpt-collapse,
        .resident-assistant .gpt-expand {
            width: 32px;
            height: 32px;
            border: 1px solid #e5e5e5;
            background: #fff;
            color: #1c2b45;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex: 0 0 auto;
        }

        .resident-assistant .gpt-expand {
            display: none;
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 5;
        }

        .resident-assistant.is-history-collapsed .gpt-side {
            width: 0;
            flex-basis: 0;
            border-right: 0;
            overflow: hidden;
        }

        .resident-assistant.is-history-collapsed .gpt-expand {
            display: inline-flex;
        }

        .resident-assistant .gpt-recents {
            display: flex;
            align-items: center;
            gap: 6px;
            width: calc(100% - 16px);
            margin: 2px 8px 4px;
            padding: 8px 10px;
            border: 0;
            background: transparent;
            color: #5d5d5d;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            cursor: pointer;
            border-radius: 8px;
        }

        .resident-assistant .gpt-recents i {
            font-size: 11px;
        }

        .resident-assistant .gpt-recents:hover {
            background: #f3f3f3;
        }

        .resident-assistant .gpt-list.is-folded {
            display: none;
        }

        .resident-assistant .gpt-list {
            flex: 1;
            overflow: auto;
            padding: 0 8px 12px;
            scrollbar-width: thin;
            scrollbar-color: #b0b6c3 transparent;
        }

        .resident-assistant .gpt-list::-webkit-scrollbar {
            width: 8px;
        }

        .resident-assistant .gpt-list::-webkit-scrollbar-button {
            display: none;
            width: 0;
            height: 0;
        }

        .resident-assistant .gpt-list::-webkit-scrollbar-thumb {
            background: #b0b6c3;
            border-radius: 999px;
        }

        .resident-assistant .gpt-item {
            width: 100%;
            display: block;
            border: 0;
            background: transparent;
            color: #0d0d0d;
            font-family: inherit;
            font-size: 14px;
            font-weight: 400;
            text-align: left;
            padding: 9px 12px;
            border-radius: 12px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .resident-assistant .gpt-item:hover,
        .resident-assistant .gpt-item.active {
            background: #ececec;
        }

        .resident-assistant .gpt-history-empty {
            margin: 8px 12px;
            color: #8b93a7;
            font-size: 13px;
        }

        .resident-assistant .gpt-main {
            position: relative;
            flex: 1;
            min-width: 0;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .resident-assistant .gpt-history-btn {
            display: none;
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #1c2b45;
            cursor: pointer;
            flex: 0 0 auto;
        }

        .resident-assistant .gpt-scroll {
            flex: 1;
            min-height: 0;
            overflow: auto;
            padding: 24px 16px 12px;
            scrollbar-width: thin;
            scrollbar-color: #b0b6c3 transparent;
        }

        .resident-assistant .gpt-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .resident-assistant .gpt-scroll::-webkit-scrollbar-button {
            display: none;
            width: 0;
            height: 0;
        }

        .resident-assistant .gpt-scroll::-webkit-scrollbar-track {
            background: transparent;
            margin: 6px 0;
        }

        .resident-assistant .gpt-scroll::-webkit-scrollbar-thumb {
            background: #b0b6c3;
            border-radius: 999px;
        }

        .resident-assistant .gpt-scroll::-webkit-scrollbar-thumb:hover {
            background: #8b93a7;
        }

        .resident-assistant .gpt-thread,
        .resident-assistant .gpt-empty {
            width: min(760px, 100%);
            margin: 0 auto;
        }

        .resident-assistant .gpt-empty[hidden],
        .resident-assistant .gpt-thread[hidden] {
            display: none !important;
        }

        .resident-assistant .gpt-empty {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 18px;
            padding-bottom: 40px;
        }

        .resident-assistant .gpt-mark {
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

        .resident-assistant .gpt-empty h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #1c2b45;
        }

        .resident-assistant .gpt-chips {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            max-width: 640px;
        }

        .resident-assistant .gpt-chip {
            border: 1px solid #e5e5e5;
            background: #fff;
            color: #1c2b45;
            border-radius: 999px;
            padding: 8px 14px;
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
        }

        .resident-assistant .gpt-chip:hover {
            background: #f7f7f8;
        }

        .resident-assistant .gpt-row {
            display: flex;
            gap: 14px;
            padding: 16px 0;
        }

        .resident-assistant .gpt-row.user {
            justify-content: flex-end;
        }

        .resident-assistant .gpt-avatar {
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

        .resident-assistant .gpt-bubble {
            max-width: min(640px, 100%);
            font-size: 15px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .resident-assistant .gpt-row.user .gpt-bubble {
            background: #f4f4f4;
            border-radius: 18px;
            padding: 10px 14px;
        }

        .resident-assistant .gpt-bubble ol,
        .resident-assistant .gpt-bubble ul {
            margin: 8px 0 8px 1.25em;
            padding: 0;
            white-space: normal;
        }

        .resident-assistant .gpt-bubble ol { list-style: decimal; }
        .resident-assistant .gpt-bubble ul { list-style: disc; }
        .resident-assistant .gpt-bubble li { display: list-item; }
        .resident-assistant .gpt-bubble p { margin: 0 0 8px; }
        .resident-assistant .gpt-bubble p:last-child { margin-bottom: 0; }

        .resident-assistant .gpt-ticket-link {
            display: inline-flex;
            margin-top: 10px;
            padding: 8px 12px;
            background: #16325c;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .resident-assistant .gpt-live {
            margin: 0 16px;
            padding: 14px 0 0;
            background: transparent;
            color: #1c2b45;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .resident-assistant .gpt-live[hidden],
        .resident-assistant .gpt-live-notice[hidden] {
            display: none;
        }

        .resident-assistant.is-history-collapsed .gpt-live {
            padding-left: 52px;
        }

        .resident-assistant.is-history-collapsed .gpt-live-notice {
            margin-left: 56px;
            width: calc(100% - 72px);
        }

        .resident-assistant .gpt-live strong {
            display: block;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.3;
            color: #1c2b45;
        }

        .resident-assistant .gpt-live span {
            display: block;
            margin-top: 2px;
            color: #667085;
            font-size: 13px;
        }

        .resident-assistant .gpt-live-notice {
            width: calc(100% - 32px);
            margin: 12px 16px 0;
            padding: 12px 14px;
            border: 1px solid #e4e9f2;
            border-left: 4px solid #e0b32a;
            background: #fff;
            color: #1c2b45;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: inherit;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .resident-assistant .gpt-live-notice:hover {
            background: #f7f9fc;
        }

        .resident-assistant .gpt-live-notice-mark {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #16325c;
            color: #e0b32a;
            font-size: 14px;
        }

        .resident-assistant .gpt-live-notice-copy {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1;
        }

        .resident-assistant .gpt-live-notice-copy strong {
            color: #16325c;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.3;
        }

        .resident-assistant .gpt-live-notice-copy span {
            color: #667085;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
        }

        .resident-assistant .gpt-live-notice-action {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #16325c;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
        }

        .resident-assistant .gpt-row.staff .gpt-bubble {
            background: #eef3fb;
            border: 1px solid #d5e0f0;
            border-radius: 18px;
            padding: 10px 14px;
        }

        .resident-assistant .gpt-staff-label {
            display: block;
            margin-bottom: 4px;
            color: #16325c;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .resident-assistant.is-live .gpt-empty {
            display: none;
        }

        .resident-assistant .gpt-typing {
            display: flex;
            gap: 5px;
            padding-top: 8px;
        }

        .resident-assistant .gpt-typing span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #9aa0b4;
            animation: residentChatBlink 1.2s infinite;
        }

        .resident-assistant .gpt-typing span:nth-child(2) { animation-delay: .15s; }
        .resident-assistant .gpt-typing span:nth-child(3) { animation-delay: .3s; }

        @keyframes residentChatBlink {
            0%, 80%, 100% { opacity: .25; }
            40% { opacity: 1; }
        }

        .resident-assistant .gpt-composer-wrap {
            padding: 8px 16px 18px;
            background: #fff;
        }

        .resident-assistant .gpt-composer {
            width: min(760px, 100%);
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #e5e5e5;
            border-radius: 26px;
            padding: 8px 8px 8px 12px;
            box-shadow: 0 0 12px rgba(0, 0, 0, .04);
            background: #fff;
        }

        .resident-assistant .gpt-composer textarea {
            flex: 1;
            align-self: center;
            box-sizing: border-box;
            height: 36px;
            min-height: 36px;
            margin: 0;
            border: 0;
            resize: none;
            max-height: 160px;
            overflow: hidden;
            font-family: inherit;
            font-size: 14px !important;
            font-weight: 400;
            letter-spacing: 0;
            line-height: 20px;
            text-transform: none !important;
            outline: none;
            padding: 8px 0 !important;
            background: transparent;
            scrollbar-width: none;
        }

        .resident-assistant .gpt-composer textarea::placeholder {
            color: #8b93a7;
            font-size: 14px !important;
            font-weight: 400;
            line-height: 20px;
            letter-spacing: 0;
            text-transform: none !important;
            opacity: 1;
        }

        .resident-assistant .gpt-composer textarea::-webkit-scrollbar,
        .resident-assistant .gpt-composer textarea::-webkit-scrollbar-button {
            display: none;
            width: 0;
            height: 0;
        }

        .resident-assistant .gpt-new,
        .resident-assistant .gpt-send {
            border: 0;
            font-family: inherit;
            cursor: pointer;
        }

        .resident-assistant .gpt-new {
            height: 36px;
            padding: 0 10px;
            border-radius: 8px;
            background: transparent;
            color: #1c2b45;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex: 0 0 auto;
        }

        .resident-assistant .gpt-new:hover {
            background: #f4f4f4;
        }

        .resident-assistant .gpt-side-top .gpt-new {
            flex: 1;
            justify-content: flex-start;
        }

        .resident-assistant .gpt-send {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #16325c;
            color: #fff;
            flex: 0 0 auto;
        }

        .resident-assistant .gpt-send:disabled {
            opacity: .4;
            cursor: default;
        }

        .resident-assistant .gpt-note {
            width: min(760px, 100%);
            margin: 8px auto 0;
            text-align: center;
            color: #8b93a7;
            font-size: 12px;
        }

        .resident-assistant .gpt-side-backdrop {
            display: none;
        }

        @media (max-width: 800px) {
            .resident-assistant .gpt-empty h1 { font-size: 22px; }
            .resident-assistant .gpt-new span { display: none; }
            .resident-assistant .gpt-history-btn { display: inline-flex; align-items: center; justify-content: center; }
            .resident-assistant .gpt-side {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: min(280px, 86vw) !important;
                z-index: 260;
                transform: translateX(-105%);
                transition: transform .2s ease;
                padding-top: 12px;
                background: #fff;
            }
            .resident-assistant .gpt-side.open { transform: none; }
            .resident-assistant.is-history-collapsed .gpt-side {
                width: min(280px, 86vw) !important;
                transform: translateX(-105%) !important;
            }
            .resident-assistant .gpt-side-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 17, 30, .42);
                opacity: 0;
                pointer-events: none;
                z-index: 250;
                transition: opacity .2s ease;
            }
            .resident-assistant .gpt-side.open + .gpt-side-backdrop,
            .resident-assistant .gpt-side-backdrop.is-open {
                opacity: 1;
                pointer-events: auto;
            }
            .resident-assistant .gpt-expand {
                top: 8px;
                z-index: 6;
            }

            .resident-assistant .gpt-live-notice {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .resident-assistant .gpt-live-notice-action {
                margin-left: 48px;
            }
        }
    </style>
</head>

<body class="db-body">

    <?php
    $role = 'resident';
    $active = 'chatbot';
    $pageTitle = 'BIS Assistant';

    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>

        <div class="db-content resident-chat-page">
            <div class="resident-assistant">
                <aside class="gpt-side" id="gptSide">
                    <div class="gpt-side-top">
                        <button class="gpt-new" id="gptNewSide" type="button">
                            <i class="fas fa-plus"></i> <span>New chat</span>
                        </button>
                        <button class="gpt-collapse" id="gptCollapse" type="button" aria-label="Collapse chat history">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                    <button class="gpt-recents" id="gptRecents" type="button" aria-expanded="true">
                        Recents <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="gpt-list" id="gptList"></div>
                </aside>
                <div class="gpt-side-backdrop" id="gptSideBackdrop"></div>
                <div class="gpt-main">
                <button class="gpt-expand" id="gptExpand" type="button" aria-label="Show chat history">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="gpt-live" id="gptLive" hidden>
                    <div>
                        <strong id="gptLiveTitle">Live conversation</strong>
                        <span id="gptLiveSub"></span>
                    </div>
                </div>
                <button class="gpt-live-notice" id="gptLiveNotice" type="button" hidden>
                    <span class="gpt-live-notice-mark" aria-hidden="true"><i class="fas fa-check"></i></span>
                    <span class="gpt-live-notice-copy">
                        <strong>Ticket approved</strong>
                        <span>The <span id="gptLiveNoticeRole">Barangay staff</span> approved your ticket.</span>
                    </span>
                    <span class="gpt-live-notice-action">Open live conversation <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                </button>
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
                        <button class="gpt-history-btn" id="gptHistory" type="button" aria-label="Open chat history">
                            <i class="fas fa-history"></i>
                        </button>
                        <button class="gpt-new" id="gptNew" type="button">
                            <i class="fas fa-plus"></i> <span>New chat</span>
                        </button>
                        <textarea id="gptInput" rows="1" placeholder="Message BIS Assistant"></textarea>
                        <button class="gpt-send" id="gptSend" type="submit" aria-label="Send" disabled>
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </form>
                    <p class="gpt-note">Your chats are saved to your account and stay here after a new chat or a refresh.</p>
                </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chatEndpoint = '/resident/chatbot/api/chat';
        const thread = document.getElementById('gptThread');
        const empty = document.getElementById('gptEmpty');
        const list = document.getElementById('gptList');
        const side = document.getElementById('gptSide');
        const input = document.getElementById('gptInput');
        const sendBtn = document.getElementById('gptSend');
        const form = document.getElementById('gptForm');
        const savedChatKey = 'bisResidentChatId';
        const liveBanner = document.getElementById('gptLive');
        const liveTitle = document.getElementById('gptLiveTitle');
        const liveSub = document.getElementById('gptLiveSub');
        const liveNotice = document.getElementById('gptLiveNotice');
        const liveNoticeRole = document.getElementById('gptLiveNoticeRole');
        const assistant = document.querySelector('.resident-assistant');
        let conversationId = null;
        let sending = false;
        let pinnedConversation = false;
        let liveConversationId = 0;
        let liveLabel = '';
        let lastLiveMessageId = 0;

        function rememberChat(id) {
            if (id) {
                sessionStorage.setItem(savedChatKey, String(id));
            }
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

        function addMessage(text, sender) {
            const kind = sender === true ? 'user' : (sender === false ? 'assistant' : String(sender || 'assistant'));
            showEmpty(false);
            const row = document.createElement('div');
            row.className = 'gpt-row' + (kind === 'user' ? ' user' : (kind === 'staff' ? ' staff' : ''));
            if (kind !== 'user') {
                const avatar = document.createElement('div');
                avatar.className = 'gpt-avatar';
                avatar.innerHTML = kind === 'staff' ? '<i class="fas fa-headset"></i>' : '<i class="fas fa-robot"></i>';
                row.appendChild(avatar);
            }
            const bubble = document.createElement('div');
            bubble.className = 'gpt-bubble';
            if (kind === 'staff') {
                const label = document.createElement('span');
                label.className = 'gpt-staff-label';
                label.textContent = liveLabel || 'Barangay staff';
                bubble.appendChild(label);
                bubble.appendChild(document.createTextNode(text));
            } else if (kind === 'user') {
                bubble.textContent = text;
            } else {
                bubble.innerHTML = text;
            }
            row.appendChild(bubble);
            thread.appendChild(row);
            scrollDown();
        }

        function setTyping(on) {
            const existing = document.getElementById('gptTyping');
            if (existing) existing.remove();
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
            list.innerHTML = '';
            if (!conversations.length) {
                const note = document.createElement('p');
                note.className = 'gpt-history-empty';
                note.textContent = 'No saved chats yet.';
                list.appendChild(note);
                return;
            }
            conversations.forEach(function (item) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'gpt-item' + (Number(item.id) === Number(conversationId) ? ' active' : '');
                button.textContent = item.title || 'New chat';
                button.addEventListener('click', function () {
                    closePhoneHistory();
                    loadConversation(item.id);
                });
                list.appendChild(button);
            });
        }

        function showMessages(messages) {
            thread.innerHTML = '';
            (messages || []).forEach(function (item) {
                const sender = String(item.sender || '').toLowerCase();
                const kind = (sender === 'user' || sender === 'resident') ? 'user' : (sender === 'staff' ? 'staff' : 'assistant');
                addMessage(item.message || '', kind);
            });
            if ((messages || []).length === 0 && liveConversationId === 0) showEmpty(true);
            const last = (messages || [])[(messages || []).length - 1];
            lastLiveMessageId = last ? Number(last.id || 0) : 0;
        }

        function showLiveChrome(label, title) {
            liveLabel = label || 'Barangay staff';
            assistant.classList.add('is-live');
            liveBanner.hidden = false;
            liveNotice.hidden = true;
            liveTitle.textContent = 'Live conversation with the ' + liveLabel;
            liveSub.textContent = title ? title : 'Messages here go directly to that office.';
            input.placeholder = 'Message the ' + liveLabel;
        }

        function hideLiveChrome() {
            assistant.classList.remove('is-live');
            liveBanner.hidden = true;
            liveNotice.hidden = true;
            liveConversationId = 0;
            liveLabel = '';
            input.placeholder = 'Message BIS Assistant';
        }

        async function openLiveConversation(data) {
            liveConversationId = Number(data.conversation_id || 0);
            conversationId = liveConversationId;
            rememberChat(conversationId);
            showLiveChrome(data.staff_label, data.title);
            if (!sending) {
                showMessages(data.messages || []);
            }
            loadHistory(false);
        }

        async function checkLiveDesk() {
            try {
                const response = await fetch('/resident/support-ticket/live?_=' + Date.now(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                const data = await response.json();
                if (!data || data.live !== true) {
                    if (liveConversationId > 0 && Number(conversationId) === liveConversationId) {
                        hideLiveChrome();
                    }
                    return;
                }
                const incomingId = Number(data.conversation_id || 0);
                const last = (data.messages || [])[(data.messages || []).length - 1];
                const incomingLast = last ? Number(last.id || 0) : 0;
                if (Number(conversationId) === incomingId) {
                    showLiveChrome(data.staff_label, data.title);
                    liveConversationId = incomingId;
                    if (!sending && incomingLast !== lastLiveMessageId) {
                        showMessages(data.messages || []);
                    }
                    return;
                }
                if (!pinnedConversation) {
                    await openLiveConversation(data);
                    return;
                }
                liveNotice.hidden = false;
                if (liveNoticeRole) liveNoticeRole.textContent = data.staff_label || 'Barangay staff';
                liveNotice.onclick = function () {
                    pinnedConversation = false;
                    openLiveConversation(data);
                };
            } catch (error) {
                return;
            }
        }

        async function loadHistory(openActive) {
            const response = await fetch('/resident/chatbot/api/history?_=' + Date.now(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const data = await response.json();
            const conversations = Array.isArray(data.conversations) ? data.conversations : [];
            const savedId = Number(sessionStorage.getItem(savedChatKey) || 0);
            const saved = conversations.find(function (item) {
                return Number(item.id) === savedId;
            });
            if (openActive) {
                if (saved) {
                    conversationId = savedId;
                    await loadConversation(savedId);
                    return;
                }
                if (data.active_conversation_id) {
                    conversationId = Number(data.active_conversation_id);
                    rememberChat(conversationId);
                    showMessages(data.messages || []);
                }
            }
            renderList(conversations);
        }

        async function loadConversation(id) {
            const response = await fetch('/resident/chatbot/api/conversation/' + encodeURIComponent(id) + '?_=' + Date.now(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const data = await response.json();
            if (!data || data.success !== true) return;
            pinnedConversation = true;
            conversationId = Number(id);
            rememberChat(conversationId);
            showMessages(data.messages || []);
            if (String(data.support_mode || '') === 'human') {
                const staffRole = String((data.conversation && data.conversation.assigned_role) || '');
                showLiveChrome(staffRole === 'admin' ? 'Barangay Admin' : (staffRole === 'secretary' ? 'Barangay Secretary' : 'Barangay staff'), data.conversation && data.conversation.title);
                liveConversationId = conversationId;
            } else if (liveConversationId !== conversationId) {
                hideLiveChrome();
            }
            const history = await fetch('/resident/chatbot/api/history?_=' + Date.now(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const historyData = await history.json();
            renderList(Array.isArray(historyData.conversations) ? historyData.conversations : []);
        }

        async function startNewChat() {
            pinnedConversation = true;
            hideLiveChrome();
            if (conversationId && thread.childElementCount === 0) {
                showEmpty(true);
                closePhoneHistory();
                input.focus();
                return;
            }
            try {
                const response = await fetch('/resident/chatbot/api/conversation', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data && Number(data.conversation_id) > 0) {
                    conversationId = Number(data.conversation_id);
                    rememberChat(conversationId);
                }
            } catch (error) {
                conversationId = null;
            }
            thread.innerHTML = '';
            showEmpty(true);
            closePhoneHistory();
            await loadHistory(false);
            input.focus();
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
                    rememberChat(conversationId);
                }
                if (data && data.support_mode === 'human' && !data.response) {
                    await checkLiveDesk();
                } else {
                    addMessage(
                        (data && data.response) ? data.response : 'Sorry, I could not answer that. Please try again.',
                        false
                    );
                }
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

        document.getElementById('gptNew').addEventListener('click', startNewChat);
        document.getElementById('gptNewSide').addEventListener('click', startNewChat);
        const historyBackdrop = document.getElementById('gptSideBackdrop');
        function closePhoneHistory() {
            side.classList.remove('open');
            if (historyBackdrop) {
                historyBackdrop.classList.remove('is-open');
            }
        }
        function phoneHistory() {
            return window.matchMedia('(max-width: 800px)').matches;
        }
        function setHistoryOpen(open) {
            side.classList.toggle('open', open);
            historyBackdrop.classList.toggle('is-open', open && phoneHistory());
            if (!open) {
                assistant.classList.add('is-history-collapsed');
            } else {
                assistant.classList.remove('is-history-collapsed');
            }
            sessionStorage.setItem('bisChatHistoryCollapsed', open ? '0' : '1');
        }
        document.getElementById('gptHistory').addEventListener('click', function () {
            setHistoryOpen(!side.classList.contains('open'));
        });

        function setHistoryCollapsed(collapsed) {
            if (phoneHistory()) {
                setHistoryOpen(!collapsed);
                return;
            }
            assistant.classList.toggle('is-history-collapsed', collapsed);
            sessionStorage.setItem('bisChatHistoryCollapsed', collapsed ? '1' : '0');
        }
        document.getElementById('gptRecents').addEventListener('click', function () {
            const folded = list.classList.toggle('is-folded');
            this.setAttribute('aria-expanded', folded ? 'false' : 'true');
            const icon = this.querySelector('i');
            if (icon) {
                icon.className = folded ? 'fas fa-chevron-right' : 'fas fa-chevron-down';
            }
        });
        document.getElementById('gptCollapse').addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setHistoryCollapsed(true);
        });
        document.getElementById('gptExpand').addEventListener('click', function () {
            setHistoryCollapsed(false);
        });
        historyBackdrop.addEventListener('click', function () {
            setHistoryCollapsed(true);
        });
        if (!phoneHistory() && sessionStorage.getItem('bisChatHistoryCollapsed') === '1') {
            setHistoryCollapsed(true);
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
            checkLiveDesk();
            const starter = new URLSearchParams(window.location.search).get('q');
            if (starter) {
                history.replaceState({}, '', window.location.pathname);
                sendMessage(starter);
                return;
            }
            input.focus();
        });
        window.setInterval(checkLiveDesk, 4000);
    </script>
</body>

</html>
