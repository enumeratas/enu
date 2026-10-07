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
            padding-top: 72px;
        }

        @media (max-width: 900px) {
            body.db-body:has(.resident-chat-page) .db-main {
                padding-top: 118px;
            }
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
        .resident-assistant .gpt-send,
        .resident-assistant .gpt-mic {
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

        .resident-assistant .gpt-send:disabled,
        .resident-assistant .gpt-mic:disabled {
            opacity: .4;
            cursor: default;
        }

        .resident-assistant .gpt-mic {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #eef2f7;
            color: #16325c;
            flex: 0 0 auto;
        }

        .resident-assistant .gpt-mic:hover:not(:disabled) {
            background: #e3e9f2;
        }

        .resident-assistant .gpt-mic.is-recording {
            background: #c62828;
            color: #fff;
            animation: residentMicPulse 1.1s ease-in-out infinite;
        }

        .resident-assistant .gpt-mic.is-busy {
            background: #16325c;
            color: #fff;
        }

        @keyframes residentMicPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(198, 40, 40, .35); }
            50% { transform: scale(1.04); box-shadow: 0 0 0 8px rgba(198, 40, 40, 0); }
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
                            <button class="gpt-chip" type="button">What is the available date for appointment?</button>
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
                        <button class="gpt-mic" id="gptMic" type="button" aria-label="Start voice message" title="Speak to BIS Assistant">
                            <i class="fas fa-microphone"></i>
                        </button>
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
        const transcribeEndpoint = '/resident/chatbot/api/transcribe';
        const speakEndpoint = '/resident/chatbot/api/speak';
        const thread = document.getElementById('gptThread');
        const empty = document.getElementById('gptEmpty');
        const list = document.getElementById('gptList');
        const side = document.getElementById('gptSide');
        const input = document.getElementById('gptInput');
        const sendBtn = document.getElementById('gptSend');
        const micBtn = document.getElementById('gptMic');
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
        let mediaRecorder = null;
        let mediaStream = null;
        let recordedChunks = [];
        let recordingTimer = null;
        let voiceAudio = null;
        let speechQueue = [];
        let speechPauseTimer = null;
        let micArming = false;
        // Voice-activity detection state. Auto-stops the recorder shortly
        // after the user stops talking so Whisper sees clean, trimmed audio.
        let vadAudioCtx = null;
        let vadAnalyser = null;
        let vadSource = null;
        let vadBuffer = null;
        let vadRafId = null;
        let vadStartedAt = 0;
        let vadLastSpeechAt = 0;
        let vadPeakLevel = 0;
        let vadNoiseFloor = 0;        // adaptive ambient noise RMS, learned during warmup
        let vadDynThreshold = 0;      // absolute RMS a frame must beat to count as speech
        let vadHeardSpeech = false;
        let vadAutoStopped = false;
        // Absolute floor so we never go below "actual silence". The DYNAMIC
        // threshold used at runtime is `max(VAD_MIN_THRESHOLD, noiseFloor * N)`
        // so that in a noisy room (fan, aircon, TV) we auto-raise the bar and
        // ignore the background, while in a quiet room we stay very sensitive.
        const VAD_MIN_THRESHOLD = 0.010;
        const VAD_NOISE_MULTIPLIER = 2.8;          // speech must be ~3x louder than ambient
        const VAD_SILENCE_MS = 1600;               // auto-stop after this much trailing silence
        const VAD_MIN_UTTERANCE_MS = 400;          // require at least this much speech first
        const VAD_WARMUP_MS = 400;                 // ambient calibration window
        const VAD_MAX_RECORD_MS = 45000;           // absolute hard cap
        const micChimeUrl = '/sounds/' + encodeURIComponent('Voicy_   iOS 16 Siri Sound .mp3');
        const micChime = new Audio(micChimeUrl);
        micChime.preload = 'auto';
        micChime.volume = 0.45;
        const defaultPlaceholder = 'Message BIS Assistant';

        function playMicChime() {
            return new Promise(function (resolve) {
                let settled = false;
                const finish = function () {
                    if (settled) return;
                    settled = true;
                    micChime.removeEventListener('ended', finish);
                    micChime.removeEventListener('error', finish);
                    resolve();
                };
                try {
                    micChime.pause();
                    micChime.currentTime = 0;
                    micChime.addEventListener('ended', finish);
                    micChime.addEventListener('error', finish);
                    const play = micChime.play();
                    if (play && typeof play.catch === 'function') {
                        play.catch(function () { finish(); });
                    }
                    setTimeout(finish, 700);
                } catch (error) {
                    finish();
                }
            });
        }


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
            input.placeholder = defaultPlaceholder;
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

        async function sendMessage(text, options) {
            const message = text.trim();
            const shouldSpeak = !!(options && options.speak);
            if (message === '' || sending) return;
            sending = true;
            stopSpeech();
            setMicIdle();
            resizeInput();
            input.value = '';
            resizeInput();
            addMessage(message, true);
            setTyping(true);
            try {
                const body = new URLSearchParams();
                body.append('message', message);
                body.append('conversation_id', conversationId ? String(conversationId) : '');
                if (shouldSpeak) {
                    body.append('source', 'voice');
                }
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
                    let reply = (data && data.response) ? data.response : 'Sorry, I could not answer that. Please try again.';
                    if (shouldSpeak) {
                        reply = withTagalogVoiceNotice(message, reply);
                    }
                    addMessage(reply, false);
                    if (shouldSpeak && data && data.response) {
                        speakReply(reply);
                    }
                }
                loadHistory(false);
            } catch (error) {
                setTyping(false);
                addMessage('Sorry, I could not connect to the BIS service. Please try again.', false);
            } finally {
                sending = false;
                setMicIdle();
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

        function replaceSpeechPhrases(text, map) {
            map.slice().sort(function (a, b) { return b[0].length - a[0].length; }).forEach(function (pair) {
                const pattern = new RegExp('(?<![A-Za-z0-9])' + pair[0].replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(?![A-Za-z0-9])', 'gi');
                text = text.replace(pattern, pair[1]);
            });
            return text;
        }

        function applyBisLetterSpelling(text) {
            return replaceSpeechPhrases(text, [
                ['b. i. s.', 'B I S'],
                ['b.i.s.', 'B I S'],
                ['bis', 'B I S']
            ]);
        }

        function applySpeechPronunciation(text) {
            return replaceSpeechPhrases(text, [
                ['punong barangay', 'poonong barangguy'],
                ['barangay bacolod', 'barangguy bahhkoahlod'],
                ['barangay hall', 'barangguy hall'],
                ['barangay captain', 'barangguy captain'],
                ['barangay secretary', 'barangguy secretary'],
                ['sangguniang kabataan', 'sanggooneeang kahbahtahahn'],
                ['camarines sur', 'kahmareeness soor'],
                ['barangays', 'barangguys'],
                ['barangay', 'barangguy'],
                ['bacolod', 'bahhkoahlod'],
                ['camarines', 'kahmareeness'],
                ['kagawad', 'kahgawad'],
                ['kapitan', 'kahpeetahn'],
                ['kalihim', 'kahleehim'],
                ['indigency', 'indijensee'],
                ['sertipiko', 'sairteepeeko'],
                ['dokumento', 'dokoomentoh'],
                ['residente', 'rehseedenteh'],
                ['opisina', 'opeeseenah'],
                ['kalendaryo', 'kahlendahryo'],
                ['aktibidad', 'akteebeedahd'],
                ['bayad', 'bahyahd'],
                ['purok', 'poorok'],
                ['bato', 'bahtoh']
            ]);
        }

        function withTagalogVoiceNotice(userText, reply) {
            if (detectSpeechLanguage(userText) !== 'tagalog') return reply;
            const notice = 'Tagalog voice is not supported.';
            const plain = String(reply || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
            if (plain.toLowerCase().indexOf(notice.toLowerCase()) === 0) return reply;
            return notice + ' ' + reply;
        }

        function detectSpeechLanguage(text) {
            const tokens = String(text).toLowerCase().replace(/[^a-z0-9ñáéíóúàèìòù\s\-]/gi, ' ').split(/\s+/).filter(Boolean);
            const tagalogWords = {
                ang: 1, mga: 1, ng: 1, sa: 1, ay: 1, po: 1, opo: 1, ho: 1,
                ako: 1, ikaw: 1, kayo: 1, kami: 1, tayo: 1, sila: 1,
                ko: 1, mo: 1, namin: 1, ninyo: 1, nila: 1, natin: 1, nyo: 1, niyo: 1, niya: 1,
                ano: 1, anong: 1, alin: 1, saan: 1, kailan: 1, bakit: 1, paano: 1, pano: 1,
                sino: 1, ilan: 1, ilang: 1, pila: 1,
                ba: 1, naman: 1, lang: 1, pala: 1, kasi: 1, kung: 1,
                gusto: 1, kailangan: 1, pwede: 1, puwede: 1, pwedeng: 1, puwedeng: 1, maaari: 1,
                ito: 1, iyan: 1, iyon: 1, yan: 1, yun: 1, yung: 1, nung: 1, doon: 1, dito: 1, dun: 1,
                meron: 1, mayroon: 1, mayroong: 1, wala: 1, walang: 1, hindi: 1, huwag: 1,
                salamat: 1, kumusta: 1, kamusta: 1, pasensya: 1, pasensiya: 1, paumanhin: 1,
                oras: 1, opisina: 1, tulong: 1, dokumento: 1, sertipiko: 1,
                humingi: 1, kumuha: 1, magkano: 1, sige: 1,
                magandang: 1, umaga: 1, hapon: 1, gabi: 1,
                nagsasara: 1, bukas: 1, sarado: 1,
                nga: 1, daw: 1, raw: 1, rin: 1,
                paki: 1, pakiusap: 1, pakisagot: 1,
                para: 1, parang: 1, tungkol: 1, ukol: 1,
                mabuti: 1, oo: 1, hoy: 1,
                ngunit: 1, lamang: 1, tanong: 1, nito: 1,
                serbisyo: 1, serbisyong: 1, kaugnay: 1, makakatulong: 1
            };
            const englishWords = {
                the: 1, is: 1, are: 1, was: 1, were: 1, am: 1, be: 1, been: 1, being: 1,
                what: 1, whats: 1, how: 1, where: 1, when: 1, why: 1, who: 1, which: 1,
                can: 1, could: 1, would: 1, should: 1, will: 1, please: 1,
                this: 1, that: 1, these: 1, those: 1,
                do: 1, does: 1, did: 1, dont: 1,
                i: 1, my: 1, me: 1, we: 1, our: 1, you: 1, your: 1,
                a: 1, an: 1, of: 1, for: 1, to: 1, and: 1, or: 1, with: 1, from: 1, on: 1, in: 1, at: 1,
                need: 1, want: 1, help: 1, hello: 1, hi: 1, hey: 1, thanks: 1, thank: 1,
                request: 1, documents: 1, document: 1, office: 1, hours: 1, events: 1, calendar: 1,
                available: 1, about: 1, tell: 1, give: 1, show: 1, list: 1,
                open: 1, close: 1, closing: 1, schedule: 1, speak: 1, talk: 1
            };
            let tagalogScore = 0;
            let englishScore = 0;
            tokens.forEach(function (token) {
                if (tagalogWords[token]) tagalogScore += 1;
                if (englishWords[token]) englishScore += 1;
            });
            if (tagalogScore > englishScore) return 'tagalog';
            if (englishScore > tagalogScore) return 'english';
            return tagalogScore > 0 ? 'tagalog' : 'english';
        }

        function yearToSpokenWords(year) {
            if (year >= 2000 && year <= 2099) {
                const rest = year - 2000;
                if (rest < 10) return 'twenty oh ' + smallNumberToSpokenWords(rest);
                return 'twenty ' + smallNumberToSpokenWords(rest).replace(/ /g, '-');
            }
            if (year >= 1900 && year <= 1999) {
                const rest = year - 1900;
                if (rest === 0) return 'nineteen hundred';
                return 'nineteen ' + smallNumberToSpokenWords(rest);
            }
            return String(year);
        }

        function smallNumberToSpokenWords(number) {
            const ones = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
            const teens = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
            const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
            if (number < 10) return ones[number];
            if (number < 20) return teens[number - 10];
            const ten = Math.floor(number / 10);
            const one = number % 10;
            return one === 0 ? tens[ten] : tens[ten] + ' ' + ones[one];
        }

        function dayToOrdinalSpokenWords(day) {
            const special = {
                1: 'first', 2: 'second', 3: 'third', 4: 'fourth', 5: 'fifth',
                6: 'sixth', 7: 'seventh', 8: 'eighth', 9: 'ninth', 10: 'tenth',
                11: 'eleventh', 12: 'twelfth', 13: 'thirteenth', 14: 'fourteenth',
                15: 'fifteenth', 16: 'sixteenth', 17: 'seventeenth', 18: 'eighteenth',
                19: 'nineteenth', 20: 'twentieth', 21: 'twenty first', 22: 'twenty second',
                23: 'twenty third', 24: 'twenty fourth', 25: 'twenty fifth',
                26: 'twenty sixth', 27: 'twenty seventh', 28: 'twenty eighth',
                29: 'twenty ninth', 30: 'thirtieth', 31: 'thirty first'
            };
            return special[day] || String(day);
        }

        function expandDatesForSpeech(text) {
            const weekdays = 'Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday';
            const months = 'January|February|March|April|May|June|July|August|September|October|November|December';
            const withYearSource = '\\b(' + weekdays + ')[,]?\\s+(' + months + ')\\s+(\\d{1,2})(?:st|nd|rd|th)?(?:[,]|\\s+of)?\\s+((?:19|20)\\d{2})\\b';
            text = String(text).replace(/\b\d{1,2}\.\s+/g, '');
            const years = [];
            String(text).replace(new RegExp(withYearSource, 'gi'), function (_, weekday, month, day, year) {
                years.push(parseInt(year, 10));
                return _;
            });
            const sameYear = years.length > 0 && years.every(function (year) { return year === years[0]; });
            let yearPrefixUsed = false;
            text = text.replace(new RegExp(withYearSource, 'gi'), function (_, weekday, month, day, year) {
                const yearNum = parseInt(year, 10);
                let date = weekday + ', ' + month + ' ' + dayToOrdinalSpokenWords(parseInt(day, 10));
                if (sameYear) {
                    if (!yearPrefixUsed) {
                        yearPrefixUsed = true;
                        date = 'in ' + yearToSpokenWords(yearNum) + ', ' + date;
                    }
                } else {
                    date += ', ' + yearToSpokenWords(yearNum);
                }
                return date + ',';
            });
            text = text.replace(
                new RegExp('\\b(' + months + ')\\s+(\\d{1,2})(?:st|nd|rd|th)?(?:[,]|\\s+of)?\\s+((?:19|20)\\d{2})\\b', 'gi'),
                function (_, month, day, year) {
                    return month + ' ' + dayToOrdinalSpokenWords(parseInt(day, 10)) + ', ' + yearToSpokenWords(parseInt(year, 10)) + ',';
                }
            );
            text = text.replace(
                new RegExp('\\b(' + weekdays + ')[,]?\\s+(' + months + ')\\s+(\\d{1,2})(?:st|nd|rd|th)?\\b', 'gi'),
                function (_, weekday, month, day) {
                    return weekday + ', ' + month + ' ' + dayToOrdinalSpokenWords(parseInt(day, 10)) + ',';
                }
            );
            return text;
        }

        function expandClockTimesForSpeech(text) {
            return String(text).replace(/\b(\d{1,2})(?::(\d{2}))?\s*(A\.?\s*M\.?|P\.?\s*M\.?)\b/gi, function (_, hour, minute, mer) {
                const hourNum = parseInt(hour, 10);
                const minuteNum = minute ? parseInt(minute, 10) : 0;
                const side = /p/i.test(mer) ? 'P M' : 'A M';
                let spoken = smallNumberToSpokenWords(hourNum === 0 ? 12 : hourNum);
                if (minuteNum > 0) {
                    spoken += ' ' + (minuteNum < 10 ? 'oh ' + smallNumberToSpokenWords(minuteNum) : smallNumberToSpokenWords(minuteNum));
                }
                return spoken + ' ' + side;
            });
        }

        function expandYearsForSpeech(text) {
            return String(text).replace(/\b((?:19|20)\d{2})\b/g, function (_, year) {
                return yearToSpokenWords(parseInt(year, 10));
            });
        }

        function integerToSpokenWords(number) {
            if (number < 100) return smallNumberToSpokenWords(number);
            if (number < 1000) {
                const rest = number % 100;
                const spoken = smallNumberToSpokenWords(Math.floor(number / 100)) + ' hundred';
                return rest > 0 ? spoken + ' ' + smallNumberToSpokenWords(rest) : spoken;
            }
            const rest = number % 1000;
            const spoken = integerToSpokenWords(Math.floor(number / 1000)) + ' thousand';
            return rest > 0 ? spoken + ' ' + integerToSpokenWords(rest) : spoken;
        }

        function expandMoneyForSpeech(text) {
            return String(text).replace(/\b(\d+)(?:\.\d{1,2})?\s*pesos?\b/gi, function (_, amount) {
                return integerToSpokenWords(parseInt(amount, 10)) + ' pesos';
            });
        }

        function expandRemainingDigitsForSpeech(text) {
            return String(text).replace(/\d+/g, function (digits) {
                return digits.split('').map(function (digit) {
                    return smallNumberToSpokenWords(parseInt(digit, 10));
                }).join(' ');
            });
        }

        function expandAddressDotsForSpeech(text) {
            function speakDotsInAddress(token) {
                token = String(token).replace(/https?:\/\//gi, '');
                token = token.replace(/@/g, ' at ');
                token = token.replace(/[\/?&=#_]/g, ' ');
                token = token.replace(/\./g, ' dot ');
                return token.replace(/\s+/g, ' ').trim();
            }
            text = String(text).replace(/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/gi, function (match) {
                return speakDotsInAddress(match);
            });
            text = text.replace(/(?:https?:\/\/|www\.)[^\s,;<>]+/gi, function (match) {
                return speakDotsInAddress(match.replace(/^https?:\/\//i, ''));
            });
            text = text.replace(/\b(?:[a-z0-9-]+\.)+(?:com|org|net|edu|gov|ph|io|co|info|xyz|online|site|html)\b/gi, function (match) {
                return speakDotsInAddress(match);
            });
            return text;
        }

        function softenSpeechPunctuation(text) {
            text = String(text).replace(/[•·●▪◦]/g, ' ');
            text = text.replace(/\.{2,}|…/g, ',');
            text = text.replace(/[.!?;:]+/g, ',');
            text = text.replace(/[()\[\]{}"“”'‘’]/g, ' ');
            text = text.replace(/\s*,(?:\s*,)+/g, ',');
            return text.replace(/\s+/g, ' ').replace(/^[\s,]+|[\s,]+$/g, '');
        }

        function plainTextFromHtml(html) {
            const box = document.createElement('div');
            box.innerHTML = html;
            box.querySelectorAll('br, p, div, li').forEach(function (node) {
                node.after(document.createTextNode(', '));
            });
            return String(box.textContent || box.innerText || '');
        }

        function stripSpeechText(html) {
            let text = plainTextFromHtml(html);
            const language = detectSpeechLanguage(text);
            text = text.replace(/[*_#`~]+/g, '');
            text = text.replace(/[₱P?]\s*(\d+)(?:\.00)?\b/g, '$1 pesos');
            text = text.replace(/\bPHP\s*(\d+)(?:\.00)?\b/gi, '$1 pesos');
            text = text.replace(/\b(\d+)(?:\.00)?\s*pesos?\b/gi, '$1 pesos');
            text = text.replace(/^\s*[-•–—]\s+/gm, '');
            text = text.replace(/\s+[-–—:]+\s+/g, ', ');
            text = text.replace(/[|/\\•→←]/g, ' ');
            text = applyBisLetterSpelling(text);
            if (language !== 'tagalog') {
                text = applySpeechPronunciation(text);
                text = expandMoneyForSpeech(text);
                text = expandDatesForSpeech(text);
                text = expandClockTimesForSpeech(text);
                text = expandYearsForSpeech(text);
                text = expandRemainingDigitsForSpeech(text);
            }
            text = expandAddressDotsForSpeech(text);
            text = softenSpeechPunctuation(text);
            return text.replace(/\s+/g, ' ').trim();
        }

        function stopSpeech() {
            speechQueue = [];
            if (speechPauseTimer) {
                clearTimeout(speechPauseTimer);
                speechPauseTimer = null;
            }
            if (voiceAudio) {
                voiceAudio.pause();
                voiceAudio.removeAttribute('src');
                voiceAudio.load();
                voiceAudio = null;
            }
            if (window.speechSynthesis) {
                window.speechSynthesis.cancel();
            }
        }

        function voiceLangMatches(voice, prefix) {
            const lang = String(voice && voice.lang || '').toLowerCase();
            const aliases = (prefix === 'fil' || prefix === 'tl') ? ['fil', 'tl'] : [prefix];
            return aliases.some(function (alias) {
                return lang === alias || lang.indexOf(alias + '-') === 0;
            });
        }

        function pickFemaleVoice(preferredLang) {
            if (!window.speechSynthesis) return null;
            const allVoices = window.speechSynthesis.getVoices() || [];
            if (!allVoices.length) return null;
            const prefix = String(preferredLang || 'en').toLowerCase().split('-')[0];
            const matched = allVoices.filter(function (voice) {
                return voiceLangMatches(voice, prefix);
            });
            const voices = matched.length ? matched : ((prefix === 'fil' || prefix === 'tl') ? [] : allVoices);
            if (!voices.length) return null;
            const neuralHints = ['natural', 'neural', 'online', 'premium', 'google', 'aria', 'jenny', 'samantha'];
            const femaleHints = [
                'female', 'zira', 'samantha', 'susan', 'hazel', 'karen', 'moira',
                'tessa', 'fiona', 'victoria', 'jenny', 'aria', 'linda', 'heera',
                'eva', 'sara', 'sarah', 'michelle', 'catherine', 'anna', 'emma',
                'sonia', 'hannah', 'autumn', 'diana', 'hapita', 'dalisay',
                'filipino', 'philippines', 'woman', 'girl'
            ];
            const maleHints = [
                'male', 'david', 'mark', 'james', 'george', 'daniel', 'thomas',
                'richard', 'ravi', 'sean', 'troy', 'austin', 'man', 'boy'
            ];
            let best = null;
            let bestScore = -100;
            voices.forEach(function (voice) {
                const name = String(voice.name || '').toLowerCase();
                let score = 4;
                neuralHints.forEach(function (hint) {
                    if (name.indexOf(hint) !== -1) score += 28;
                });
                femaleHints.forEach(function (hint) {
                    if (name.indexOf(hint) !== -1) score += 12;
                });
                maleHints.forEach(function (hint) {
                    if (name.indexOf(hint) !== -1) score -= 24;
                });
                if (name.indexOf('zira') !== -1) score -= 8;
                if (score > bestScore) {
                    bestScore = score;
                    best = voice;
                }
            });
            return best;
        }

        if (window.speechSynthesis) {
            window.speechSynthesis.addEventListener('voiceschanged', function () {
                pickFemaleVoice('fil-PH');
                pickFemaleVoice('en');
            });
        }

        function whenVoicesReady(done) {
            if (!window.speechSynthesis) {
                done();
                return;
            }
            if ((window.speechSynthesis.getVoices() || []).length) {
                done();
                return;
            }
            let finished = false;
            const finish = function () {
                if (finished) return;
                finished = true;
                window.speechSynthesis.removeEventListener('voiceschanged', finish);
                done();
            };
            window.speechSynthesis.addEventListener('voiceschanged', finish);
            setTimeout(finish, 600);
        }

        function speakWithBrowser(text, language) {
            if (!window.speechSynthesis || !text) return;
            whenVoicesReady(function () {
                startBrowserSpeech(text, language);
            });
        }

        function startBrowserSpeech(text, language) {
            stopSpeech();
            const tagalog = language === 'tagalog' || detectSpeechLanguage(text) === 'tagalog';
            const lang = tagalog ? 'fil-PH' : 'en-US';
            const matchingVoice = pickFemaleVoice(lang);
            let spoken = text;
            let voice = matchingVoice;
            if (tagalog && !matchingVoice) {
                spoken = applySpeechPronunciation(text);
                voice = pickFemaleVoice('en');
            }
            const pieces = tagalog
                ? String(spoken).split(/\s*,\s*/).map(function (part) { return part.trim(); }).filter(Boolean)
                : (String(spoken).length > 420
                    ? String(spoken).split(/(?<=[.!?])\s+/).map(function (part) { return part.trim(); }).filter(Boolean)
                    : [spoken]);

            pieces.forEach(function (piece) {
                const utterance = new SpeechSynthesisUtterance(piece);
                utterance.lang = lang;
                utterance.rate = tagalog ? 0.9 : 1;
                utterance.pitch = tagalog ? 1 : 1.02;
                if (voice) utterance.voice = voice;
                window.speechSynthesis.speak(utterance);
            });
        }

        async function speakReply(html) {
            const language = detectSpeechLanguage(plainTextFromHtml(html));
            const text = stripSpeechText(html);
            if (!text) return;
            if (language === 'tagalog') {
                speakWithBrowser(text, 'tagalog');
                return;
            }
            try {
                const response = await fetch(speakEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'audio/wav, application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ text: text })
                });
                const type = (response.headers.get('content-type') || '').toLowerCase();
                if (!response.ok || type.indexOf('audio') === -1) {
                    speakWithBrowser(text, language);
                    return;
                }
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                stopSpeech();
                voiceAudio = new Audio(url);
                voiceAudio.playbackRate = 0.96;
                voiceAudio.onended = function () {
                    URL.revokeObjectURL(url);
                    voiceAudio = null;
                };
                voiceAudio.onerror = function () {
                    URL.revokeObjectURL(url);
                    voiceAudio = null;
                    speakWithBrowser(text, language);
                };
                await voiceAudio.play();
            } catch (error) {
                speakWithBrowser(text, language);
            }
        }

        function setMicIdle() {
            if (!micBtn) return;
            micBtn.classList.remove('is-recording', 'is-busy');
            micBtn.disabled = sending;
            micBtn.setAttribute('aria-label', 'Start voice message');
            micBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            input.disabled = false;
            if (!liveConversationId) {
                input.placeholder = defaultPlaceholder;
            }
            resizeInput();
        }

        function setMicRecording() {
            micBtn.classList.add('is-recording');
            micBtn.classList.remove('is-busy');
            micBtn.disabled = false;
            micBtn.setAttribute('aria-label', 'Stop recording');
            micBtn.innerHTML = '<i class="fas fa-stop"></i>';
            input.placeholder = 'Listening… tap the mic to stop';
        }

        function setMicPreparing() {
            if (!micBtn) return;
            micBtn.classList.remove('is-recording', 'is-busy');
            micBtn.disabled = true;
            micBtn.setAttribute('aria-label', 'Preparing…');
            micBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
            input.placeholder = 'Get ready to speak…';
        }

        function setMicBusy() {
            micBtn.classList.remove('is-recording');
            micBtn.classList.add('is-busy');
            micBtn.disabled = true;
            micBtn.setAttribute('aria-label', 'Transcribing');
            micBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
            input.placeholder = 'Hearing your question…';
        }

        function stopMediaTracks() {
            if (mediaStream) {
                mediaStream.getTracks().forEach(function (track) {
                    track.stop();
                });
                mediaStream = null;
            }
        }

        function pickRecorderType() {
            const types = [
                'audio/webm;codecs=opus',
                'audio/webm',
                'audio/mp4',
                'audio/ogg;codecs=opus'
            ];
            for (let i = 0; i < types.length; i += 1) {
                if (window.MediaRecorder && MediaRecorder.isTypeSupported(types[i])) {
                    return types[i];
                }
            }
            return '';
        }

        function startVoiceActivityDetection(stream) {
            try {
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (!Ctx) return;
                vadAudioCtx = new Ctx();
                // High-pass filter cuts the low-frequency rumble that fans,
                // aircon, and PC noise sit in (<80 Hz). The analyser then sees
                // mostly the voice band, so background hum does not inflate
                // the measured RMS. This is cheap and very effective.
                const highpass = vadAudioCtx.createBiquadFilter();
                highpass.type = 'highpass';
                highpass.frequency.value = 90;
                vadAnalyser = vadAudioCtx.createAnalyser();
                vadAnalyser.fftSize = 1024;
                vadAnalyser.smoothingTimeConstant = 0.5;
                vadSource = vadAudioCtx.createMediaStreamSource(stream);
                vadSource.connect(highpass);
                highpass.connect(vadAnalyser);
                vadBuffer = new Float32Array(vadAnalyser.fftSize);
                vadStartedAt = performance.now();
                vadLastSpeechAt = vadStartedAt;
                vadPeakLevel = 0;
                vadNoiseFloor = 0;
                vadDynThreshold = VAD_MIN_THRESHOLD;
                vadHeardSpeech = false;
                vadAutoStopped = false;

                // Running estimate of ambient noise during the warmup.
                let noiseSamples = 0;
                let noiseSum = 0;

                const tick = function () {
                    if (!vadAnalyser) return;
                    vadAnalyser.getFloatTimeDomainData(vadBuffer);
                    // Root mean square gives a stable loudness estimate per frame.
                    let sumSquares = 0;
                    for (let i = 0; i < vadBuffer.length; i++) {
                        const v = vadBuffer[i];
                        sumSquares += v * v;
                    }
                    const rms = Math.sqrt(sumSquares / vadBuffer.length);
                    if (rms > vadPeakLevel) vadPeakLevel = rms;

                    const now = performance.now();
                    const elapsed = now - vadStartedAt;

                    if (elapsed <= VAD_WARMUP_MS) {
                        // Learn the room's noise floor.
                        noiseSum += rms;
                        noiseSamples++;
                    } else {
                        // Lock in the dynamic threshold once (at warmup end).
                        if (vadDynThreshold === VAD_MIN_THRESHOLD && noiseSamples > 0) {
                            vadNoiseFloor = noiseSum / noiseSamples;
                            const adaptive = vadNoiseFloor * VAD_NOISE_MULTIPLIER;
                            vadDynThreshold = Math.max(VAD_MIN_THRESHOLD, adaptive);
                        }
                        // Slow adaptive drift of the noise floor during
                        // silence so continuous background noise does not
                        // eventually be mistaken for speech.
                        if (rms < vadDynThreshold) {
                            vadNoiseFloor = vadNoiseFloor * 0.995 + rms * 0.005;
                            const adaptive = vadNoiseFloor * VAD_NOISE_MULTIPLIER;
                            vadDynThreshold = Math.max(VAD_MIN_THRESHOLD, adaptive);
                        }
                        if (rms > vadDynThreshold) {
                            vadLastSpeechAt = now;
                            if (!vadHeardSpeech) vadHeardSpeech = true;
                        }
                    }

                    // Auto-stop once we have heard real speech followed by a
                    // short tail of silence. This is what makes the clip clean
                    // for Whisper and prevents trailing-silence hallucinations.
                    if (
                        vadHeardSpeech
                        && (now - vadLastSpeechAt) > VAD_SILENCE_MS
                        && (vadLastSpeechAt - vadStartedAt) > VAD_MIN_UTTERANCE_MS
                        && mediaRecorder
                        && mediaRecorder.state === 'recording'
                    ) {
                        vadAutoStopped = true;
                        try { mediaRecorder.stop(); } catch (e) { /* ignore */ }
                        return;
                    }

                    vadRafId = requestAnimationFrame(tick);
                };
                vadRafId = requestAnimationFrame(tick);
            } catch (e) {
                // VAD is best-effort; the hard cap + manual stop still work.
                vadAnalyser = null;
            }
        }

        function stopVoiceActivityDetection() {
            if (vadRafId) {
                cancelAnimationFrame(vadRafId);
                vadRafId = null;
            }
            if (vadSource) {
                try { vadSource.disconnect(); } catch (e) {}
                vadSource = null;
            }
            vadAnalyser = null;
            vadBuffer = null;
            if (vadAudioCtx) {
                const ctx = vadAudioCtx;
                vadAudioCtx = null;
                // close() returns a Promise; ignore it. This fully frees the
                // audio graph so the next recording starts from a clean slate
                // instead of inheriting stale AGC/filter state (the main
                // reason the second attempt previously went silent).
                try { ctx.close(); } catch (e) {}
            }
        }

        // Collect the last few chat turns as plain text so the backend can
        // forward them to Whisper's `prompt` field, biasing recognition toward
        // on-topic vocabulary (names, document types, Taglish phrases).
        function collectRecentContext() {
            if (!thread) return '';
            const bubbles = thread.querySelectorAll('.gpt-bubble');
            if (!bubbles || bubbles.length === 0) return '';
            const take = Math.min(bubbles.length, 6);
            const parts = [];
            for (let i = bubbles.length - take; i < bubbles.length; i++) {
                const txt = (bubbles[i].innerText || bubbles[i].textContent || '').trim();
                if (txt) parts.push(txt.replace(/\s+/g, ' '));
            }
            // Keep the payload modest; backend also clamps.
            let joined = parts.join(' | ');
            if (joined.length > 600) joined = joined.slice(-600);
            return joined;
        }

        async function startVoiceRecording() {
            if (sending || micArming || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    addMessage('Voice chat needs a microphone in this browser.', false);
                }
                return;
            }
            micArming = true;
            stopSpeech();
            // Hard-reset any leftover state from the previous recording.
            // This is what fixes the "first attempt works, second attempt
            // returns 'I could not hear a clear question'" bug: Chrome's AGC
            // and the MediaRecorder's encoder keep internal state that leaks
            // between recordings if the stream is not fully released.
            recordedChunks = [];
            stopVoiceActivityDetection();
            stopMediaTracks();
            if (mediaRecorder) {
                try { mediaRecorder.ondataavailable = null; } catch (e) {}
                try { mediaRecorder.onstop = null; } catch (e) {}
                mediaRecorder = null;
            }
            setMicPreparing();
            sendBtn.disabled = true;
            await playMicChime();
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                        channelCount: 1,
                        sampleRate: 48000,
                        sampleSize: 16,
                        // Chrome-only legacy constraints. They are ignored on
                        // other browsers but Chrome still honours them and
                        // they turn on the strongest voice-focused filters.
                        googEchoCancellation: true,
                        googAutoGainControl: true,
                        googNoiseSuppression: true,
                        googHighpassFilter: true,
                        googTypingNoiseDetection: true,
                        googAudioMirroring: false
                    }
                });
            } catch (error) {
                micArming = false;
                setMicIdle();
                addMessage('Please allow microphone access to talk to BIS Assistant.', false);
                return;
            }
            const mimeType = pickRecorderType();
            // Opus @ 64 kbps mono gives Whisper plenty of fidelity while
            // keeping the upload small. 48 kbps was borderline-too-low and
            // sometimes produced empty frames on the first chunk after a
            // repeat recording.
            const recorderOpts = { audioBitsPerSecond: 64000 };
            if (mimeType) recorderOpts.mimeType = mimeType;
            try {
                mediaRecorder = new MediaRecorder(mediaStream, recorderOpts);
            } catch (e) {
                // Fall back to browser defaults if the chosen options are rejected.
                mediaRecorder = new MediaRecorder(mediaStream);
            }
            mediaRecorder.ondataavailable = function (event) {
                if (event.data && event.data.size > 0) {
                    recordedChunks.push(event.data);
                }
            };
            mediaRecorder.onstop = function () {
                const type = mediaRecorder && mediaRecorder.mimeType ? mediaRecorder.mimeType : 'audio/webm';
                const heardSpeech = vadHeardSpeech;
                const peak = vadPeakLevel;
                const noiseFloor = vadNoiseFloor;
                const dynThreshold = vadDynThreshold;
                mediaRecorder = null;
                stopVoiceActivityDetection();
                stopMediaTracks();
                if (recordingTimer) {
                    clearTimeout(recordingTimer);
                    recordingTimer = null;
                }
                const blob = new Blob(recordedChunks, { type: type });
                // Diagnostic for the browser DevTools console. If the next
                // attempt fails, these numbers tell us WHY (empty blob, peak
                // too low, threshold too high, etc).
                try {
                    console.log('[BIS voice] stop',
                        'blobBytes=' + blob.size,
                        'chunks=' + recordedChunks.length,
                        'heardSpeech=' + heardSpeech,
                        'peakRMS=' + peak.toFixed(4),
                        'noiseFloor=' + noiseFloor.toFixed(4),
                        'threshold=' + dynThreshold.toFixed(4));
                } catch (e) {}
                // Short-circuit only truly dead audio (user tapped the mic
                // but did not actually speak). We require BOTH no detected
                // speech frames AND a very low peak level - this way a soft
                // speaker still gets a chance at Whisper instead of being
                // bounced here.
                if (!heardSpeech && peak < 0.004) {
                    setMicIdle();
                    addMessage('I did not hear anything. Please tap the mic and speak clearly.', false);
                    return;
                }
                playMicChime().then(function () {
                    transcribeRecording(blob);
                });
            };
            // Request a data chunk every 250 ms so if the user stops talking
            // mid-utterance there is already buffered audio to send.
            try { mediaRecorder.start(250); } catch (e) { mediaRecorder.start(); }
            micArming = false;
            setMicRecording();
            startVoiceActivityDetection(mediaStream);
            // Hard cap in case the browser never fires the VAD auto-stop.
            recordingTimer = setTimeout(function () {
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                }
            }, VAD_MAX_RECORD_MS);
        }

        function stopVoiceRecording() {
            if (recordingTimer) {
                clearTimeout(recordingTimer);
                recordingTimer = null;
            }
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                return;
            }
            stopVoiceActivityDetection();
            stopMediaTracks();
            setMicIdle();
        }

        async function transcribeRecording(blob) {
            if (!blob || blob.size < 800) {
                setMicIdle();
                addMessage('I did not catch that. Please tap the mic and try again.', false);
                return;
            }
            setMicBusy();
            try {
                const body = new FormData();
                const extension = blob.type.indexOf('mp4') !== -1 ? 'mp4' : (blob.type.indexOf('ogg') !== -1 ? 'ogg' : 'webm');
                body.append('audio', blob, 'voice.' + extension);
                // Give Whisper the recent conversation as a biasing prompt.
                const context = collectRecentContext();
                if (context) body.append('context', context);
                const response = await fetch(transcribeEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: body
                });
                const data = await response.json();
                setMicIdle();
                if (!data || data.success !== true || !data.text) {
                    addMessage((data && data.response) ? data.response : 'I could not hear a clear question. Please try again.', false);
                    return;
                }
                await sendMessage(data.text, { speak: true });
            } catch (error) {
                setMicIdle();
                addMessage('Voice chat could not reach the BIS service. Please try again.', false);
            }
        }

        if (micBtn) {
            micBtn.addEventListener('click', function () {
                if (micBtn.classList.contains('is-recording')) {
                    stopVoiceRecording();
                    return;
                }
                if (sending || micArming || micBtn.classList.contains('is-busy')) return;
                startVoiceRecording();
            });
        }

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
