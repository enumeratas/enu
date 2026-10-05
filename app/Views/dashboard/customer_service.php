<?php

$role = strtolower(
    (string) (
        $role ??
        session()->get('role') ??
        'secretary'
    )
);

$active = 'customer_service';

$pageTitle =
    $pageTitle ??
    'Customer Service';

if (
    ! in_array(
        $role,
        [
            'admin',
            'secretary',
            'captain'
        ],
        true
    )
) {
    return redirect()->to('/');
}

$roleLabel =
    ucfirst($role);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= esc($pageTitle) ?> | Bacolod BIS
    </title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">

    <style>
        /* =========================================================
           RESET
           ========================================================= */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
        }

        /* =========================================================
           MAIN PAGE
           ========================================================= */

        .customer-service-page {
            width: 100%;
            min-height: 0;
            padding: 0;
            background: transparent;
        }


        /* =========================================================
           PAGE HEADER
           ========================================================= */

        .cs-header {
            width: 100%;

            margin-bottom: 18px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;
        }

        .cs-title-area {
            min-width: 0;
        }

        .cs-title-area h1 {
            margin: 0;

            font-size: 24px;

            line-height: 1.25;

            font-weight: 700;

            color: #1f2937;
        }

        .cs-title-area p {
            margin:
                6px 0 0;

            font-size: 13px;

            line-height: 1.5;

            color: #64748b;
        }

        .cs-header-badge {
            flex-shrink: 0;

            height: 38px;

            padding:
                0 13px;

            border:
                1px solid #e5e7eb;

            border-radius: 9px;

            background: #ffffff;

            display: inline-flex;

            align-items: center;

            gap: 7px;

            color: #475569;

            font-size: 12px;

            font-weight: 600;
        }

        .cs-header-badge i {
            color: #2563eb;

            font-size: 13px;
        }


        /* =========================================================
           MAIN TWO-COLUMN AREA
           ========================================================= */

        .cs-layout {
            width: 100%;

            height:
                calc(100vh - 210px);

            min-height: 590px;

            display: grid;

            grid-template-columns:
                320px minmax(0, 1fr);

            gap: 16px;
        }

        .cs-panel {
            min-width: 0;

            min-height: 0;

            background: #ffffff;

            border:
                1px solid #e5e7eb;

            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 2px 10px rgba(15,
                    23,
                    42,
                    0.04);
        }


        /* =========================================================
           QUEUE
           ========================================================= */

        .cs-queue-panel {
            display: flex;

            flex-direction: column;
        }

        .cs-queue-header {
            min-height: 62px;

            padding:
                0 16px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            border-bottom:
                1px solid #eef0f4;
        }

        .cs-queue-title {
            display: flex;

            align-items: center;

            gap: 9px;

            color: #1f2937;

            font-size: 14px;

            font-weight: 700;
        }

        .cs-queue-title i {
            color: #2563eb;

            font-size: 14px;
        }

        .cs-count {
            min-width: 24px;

            height: 24px;

            padding:
                0 7px;

            border-radius: 999px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            background: #eff6ff;

            color: #2563eb;

            font-size: 11px;

            font-weight: 700;
        }

        .cs-queue-status[hidden] {
            display: none;
        }

        .cs-queue-status {
            min-height: 37px;

            padding:
                0 16px;

            display: flex;

            align-items: center;

            border-bottom:
                1px solid #eef0f4;

            color: #94a3b8;

            font-size: 11px;
        }

        .cs-queue-list {
            flex: 1;

            overflow-y: auto;
        }

        .support-conversation-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            width: 100%;
            margin: 0;
            padding: 14px 16px;
            border: 0;
            border-bottom: 1px solid #e6ebf3;
            border-radius: 0;
            background: #fff;
            color: #16325c;
            font-family: inherit;
            text-align: left;
            cursor: pointer;
            box-shadow: none;
            appearance: none;
            -webkit-appearance: none;
        }

        .support-conversation-item:hover {
            background: #f7f9fc;
        }

        .support-conversation-item.active {
            background: #f4f7fb;
            box-shadow: inset 3px 0 0 #16325c;
        }

        .support-request-avatar {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2f8;
            color: #16325c;
            font-size: 14px;
        }

        .support-request-body {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .support-request-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .support-request-name {
            min-width: 0;
            overflow: hidden;
            color: #16325c;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .support-request-time {
            flex: 0 0 auto;
            color: #8b93a7;
            font-size: 11px;
            white-space: nowrap;
        }

        .support-request-status,
        .support-request-preview {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            overflow: hidden;
            color: #5c677d;
            font-size: 12px;
            line-height: 1.4;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .support-request-preview {
            color: #8b93a7;
        }

        .support-request-dot {
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            background: #e0b32a;
        }

        .support-request-dot.human {
            background: #1f8a70;
        }

        .support-request-dot.closed {
            background: #9aa0b4;
        }

        .support-request-dot.ai {
            background: #16325c;
        }

        .cs-queue-empty {
            padding:
                50px 20px;

            text-align: center;

            color: #94a3b8;
        }

        .cs-queue-empty i {
            display: block;

            margin-bottom: 12px;

            color: #cbd5e1;

            font-size: 30px;
        }

        .cs-queue-empty p {
            margin: 0;

            font-size: 12px;

            line-height: 1.5;
        }

        .cs-conversation-item {
            width: 100%;

            padding:
                13px 15px;

            border: 0;

            border-bottom:
                1px solid #f1f5f9;

            background: #ffffff;

            text-align: left;

            cursor: pointer;

            display: block;

            transition:
                background 0.15s ease;
        }

        .cs-conversation-item:hover {
            background: #f8fafc;
        }

        .cs-conversation-item.active {
            background: #eff6ff;
        }

        .cs-conversation-top {
            width: 100%;

            display: flex;

            align-items: center;

            gap: 10px;
        }

        .cs-avatar {
            width: 38px;
            height: 38px;

            min-width: 38px;

            border-radius: 50%;

            background: #eff6ff;

            color: #2563eb;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 11px;

            font-weight: 700;
        }

        .cs-conversation-main {
            min-width: 0;

            flex: 1;
        }

        .cs-conversation-name {
            display: block;

            overflow: hidden;

            white-space: nowrap;

            text-overflow: ellipsis;

            color: #334155;

            font-size: 12px;

            font-weight: 700;
        }

        .cs-conversation-preview {
            display: block;

            margin-top: 3px;

            overflow: hidden;

            white-space: nowrap;

            text-overflow: ellipsis;

            color: #94a3b8;

            font-size: 10px;
        }

        .cs-conversation-meta {
            min-width: 55px;

            display: flex;

            flex-direction: column;

            align-items: flex-end;

            gap: 5px;
        }

        .cs-time {
            white-space: nowrap;

            color: #94a3b8;

            font-size: 9px;
        }

        .cs-mode-badge {
            padding:
                3px 6px;

            border-radius: 999px;

            white-space: nowrap;

            text-transform: uppercase;

            font-size: 8px;

            font-weight: 700;
        }

        .cs-mode-badge.waiting {
            background: #fef3c7;

            color: #92400e;
        }

        .cs-mode-badge.human {
            background: #dcfce7;

            color: #166534;
        }


        /* =========================================================
           CHAT PANEL
           ========================================================= */

        .cs-chat-panel {
            position: relative;
            display: flex;

            flex-direction: column;
        }

        .cs-chat-header {
            min-height: 70px;

            padding:
                12px 16px;

            border-bottom:
                1px solid #eef0f4;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;
        }

        .cs-resident-info {
            min-width: 0;

            display: flex;

            align-items: center;

            gap: 10px;
        }

        .cs-resident-avatar {
            width: 40px;
            height: 40px;

            min-width: 40px;

            border-radius: 50%;

            background: #eff6ff;

            color: #2563eb;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 12px;

            font-weight: 700;
        }

        .cs-resident-details {
            min-width: 0;
        }

        .cs-resident-name {
            margin: 0;

            overflow: hidden;

            white-space: nowrap;

            text-overflow: ellipsis;

            font-size: 13px;

            font-weight: 700;

            color: #334155;
        }

        .cs-resident-status {
            margin-top: 4px;

            display: flex;

            align-items: center;

            gap: 6px;

            font-size: 10px;

            color: #94a3b8;
        }

        .cs-status-dot {
            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #94a3b8;
        }

        .cs-status-dot.waiting {
            background: #f59e0b;
        }

        .cs-status-dot.human {
            background: #22c55e;
        }

        .cs-status-dot.closed {
            background: #94a3b8;
        }

        .cs-actions {
            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 6px;

            flex-wrap: wrap;
        }

        .cs-btn {
            height: 34px;

            padding:
                0 10px;

            border:
                1px solid #dbe1e8;

            border-radius: 8px;

            background: #ffffff;

            color: #475569;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 6px;

            cursor: pointer;

            font-family: inherit;

            font-size: 10px;

            font-weight: 600;

            transition:
                0.15s ease;
        }

        .cs-btn:hover:not(:disabled) {
            background: #f8fafc;

            border-color: #cbd5e1;
        }

        .cs-btn:disabled {
            opacity: 0.45;

            cursor: not-allowed;
        }

        .cs-btn.takeover {
            background: #2563eb;

            border-color: #2563eb;

            color: #ffffff;
        }

        .cs-btn.takeover:hover:not(:disabled) {
            background: #1d4ed8;

            border-color: #1d4ed8;
        }

        .cs-btn.return-ai {
            background: #f0f9ff;

            border-color: #bae6fd;

            color: #0369a1;
        }

        .cs-btn.close-support {
            background: #fef2f2;

            border-color: #fecaca;

            color: #b91c1c;
        }


        /* =========================================================
           EMPTY CHAT
           ========================================================= */

        .cs-chat-empty {
            flex: 1;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px;

            text-align: center;

            background: #f8fafc;

            color: #94a3b8;
        }

        .cs-chat-empty-inner i {
            margin-bottom: 12px;

            color: #cbd5e1;

            font-size: 40px;
        }

        .cs-chat-empty-inner h3 {
            margin:
                0 0 6px;

            color: #475569;

            font-size: 15px;
        }

        .cs-chat-empty-inner p {
            max-width: 380px;

            margin: 0 auto;

            font-size: 11px;

            line-height: 1.6;

            color: #94a3b8;
        }


        /* =========================================================
           MESSAGES
           ========================================================= */

        .cs-chat-messages {
            flex: 1;

            min-height: 0;

            overflow-y: auto;

            padding:
                18px;

            display: flex;

            flex-direction: column;

            gap: 13px;

            background: #f8fafc;
        }

        .cs-message-row {
            width: 100%;

            display: flex;
        }

        .cs-message-row.user {
            justify-content: flex-start;
        }

        .cs-message-row.assistant,
        .cs-message-row.staff {
            justify-content: flex-end;
        }

        .cs-message-bubble-wrap {
            max-width:
                min(72%,
                    600px);
        }

        .cs-message-label {
            margin-bottom: 4px;

            color: #64748b;

            font-size: 9px;

            font-weight: 700;
        }

        .cs-message-row.assistant .cs-message-label,
        .cs-message-row.staff .cs-message-label {
            text-align: right;
        }

        .cs-message-bubble {
            padding:
                9px 12px;

            border-radius: 11px;

            font-size: 11px;

            line-height: 1.55;

            white-space: pre-wrap;

            word-break: break-word;
        }

        .cs-message-row.user .cs-message-bubble {
            background: #ffffff;

            border:
                1px solid #e5e7eb;

            border-bottom-left-radius: 3px;

            color: #475569;
        }

        .cs-message-row.assistant .cs-message-bubble {
            background: #e0edff;

            color: #1e3a8a;

            border-bottom-right-radius: 3px;
        }

        .cs-message-row.staff .cs-message-bubble {
            background: #2563eb;

            color: #ffffff;

            border-bottom-right-radius: 3px;
        }

        .cs-message-time {
            margin-top: 4px;

            color: #94a3b8;

            font-size: 8px;
        }

        .cs-message-row.assistant .cs-message-time,
        .cs-message-row.staff .cs-message-time {
            text-align: right;
        }


        /* =========================================================
           NOTICE
           ========================================================= */

        .cs-notice {
            display: none;

            margin:
                0 14px 10px;

            padding:
                8px 10px;

            border-radius: 7px;

            font-size: 10px;
        }

        .cs-notice.show {
            display: block;
        }

        .cs-notice.info {
            background: #eff6ff;

            color: #1d4ed8;
        }

        .cs-notice.success {
            background: #f0fdf4;

            color: #15803d;
        }

        .cs-notice.error {
            background: #fef2f2;

            color: #b91c1c;
        }


        /* =========================================================
           COMPOSER
           ========================================================= */

        .cs-composer {
            padding:
                10px 12px;

            border-top:
                1px solid #eef0f4;

            background: #ffffff;

            display: grid;

            grid-template-columns:
                minmax(0, 1fr) 42px;

            gap: 8px;
        }

        .cs-message-input {
            width: 100%;

            min-height: 42px;

            max-height: 110px;

            resize: vertical;

            padding:
                10px 11px;

            border:
                1px solid #dbe1e8;

            border-radius: 8px;

            outline: none;

            font-family: inherit;

            font-size: 11px;

            color: #334155;

            background: #ffffff;
        }

        .cs-message-input:focus {
            border-color: #93c5fd;

            box-shadow:
                0 0 0 2px rgba(37,
                    99,
                    235,
                    0.08);
        }

        .cs-send-btn {
            min-height: 42px;

            border: 0;

            border-radius: 8px;

            background: #2563eb;

            color: #ffffff;

            cursor: pointer;

            font-size: 13px;
        }

        .cs-send-btn:hover:not(:disabled) {
            background: #1d4ed8;
        }

        .cs-send-btn:disabled {
            opacity: 0.45;

            cursor: not-allowed;
        }


        /* =========================================================
           RESPONSIVE
           ========================================================= */

        @media (max-width: 1100px) {

            .cs-layout {
                grid-template-columns:
                    280px minmax(0,
                        1fr);
            }

        }


        @media (max-width: 850px) {

            .cs-layout {
                height: auto;
            }

            .cs-header {
                align-items: flex-start;

                flex-direction: column;
            }

            .cs-layout {
                height: auto;

                min-height: 0;

                grid-template-columns:
                    1fr;
            }

            .cs-queue-panel {
                min-height: 340px;
            }

            .cs-chat-panel {
                min-height: 620px;
            }

            .cs-chat-header {
                align-items: flex-start;

                flex-direction: column;
            }

            .cs-actions {
                width: 100%;

                justify-content: flex-start;
            }

        }

        .cs-queue-tools {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .cs-queue-toggle,
        .cs-queue-expand {
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            background: #fff;
            color: #1c2b45;
            cursor: pointer;
        }

        .cs-queue-expand {
            display: none;
            position: absolute;
            top: 14px;
            left: 12px;
            z-index: 5;
        }

        .cs-queue-header {
            gap: 8px;
        }

        .cs-layout.is-queue-collapsed {
            grid-template-columns: 0 minmax(0, 1fr);
            gap: 0;
        }

        .cs-layout.is-queue-collapsed .cs-queue-panel {
            border: 0;
            overflow: hidden;
        }

        .cs-layout.is-queue-collapsed .cs-queue-expand {
            display: inline-flex;
        }

        .cs-layout.is-queue-collapsed .cs-chat-header {
            padding-left: 52px;
        }

        @media (max-width: 850px) {
            .cs-layout.is-queue-collapsed {
                grid-template-columns: 1fr;
            }

            .cs-layout.is-queue-collapsed .cs-queue-panel {
                display: none;
            }
        }
    </style>

</head>

<body class="db-body">

    <?php

    echo view(
        'dashboard/sidebar',
        [
            'role'   => $role,
            'active' => $active,
            'pageTitle' => $pageTitle,
        ]
    );

    ?>

    <div class="db-main">
        <?php include APPPATH . 'Views/dashboard/topbar.php'; ?>
        <div class="db-content">
    <main class="customer-service-page">

        <!-- =====================================================
         PAGE HEADER
         ====================================================== -->

        <div class="cs-header">

            <div class="cs-title-area">

                <h1>
                    Customer Service
                </h1>

                <p>
                    Manage resident support requests and communicate directly with clients.
                </p>

            </div>

            <div class="cs-header-badge">

                <i class="fas fa-headset"></i>

                <span>
                    <?= esc($roleLabel) ?> Support Desk
                </span>

            </div>

        </div>


        <!-- =====================================================
         MAIN CUSTOMER SERVICE AREA
         ====================================================== -->

        <section class="cs-layout">


            <!-- =================================================
             SUPPORT REQUEST QUEUE
             ================================================== -->

            <div class="cs-panel cs-queue-panel">

                <div class="cs-queue-header">

                    <div class="cs-queue-title">

                        <i class="fas fa-inbox"></i>

                        <span>
                            Support Requests
                        </span>

                    </div>

                    <div class="cs-queue-tools">
                        <span
                            id="supportRequestCount"
                            class="cs-count">
                            0
                        </span>
                        <button
                            id="csQueueToggle"
                            class="cs-queue-toggle"
                            type="button"
                            aria-label="Collapse support requests"
                            aria-expanded="true"
                            title="Collapse support requests">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>

                </div>


                <div
                    id="supportQueueStatus"
                    class="cs-queue-status">
                    Loading support requests...
                </div>


                <div
                    id="supportConversationList"
                    class="cs-queue-list">

                    <div class="cs-queue-empty">

                        <i class="fas fa-spinner fa-spin"></i>

                        <p>
                            Loading support conversations...
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
             CONVERSATION
             ================================================== -->

            <div class="cs-panel cs-chat-panel">
                <button class="cs-queue-expand" id="csQueueExpand" type="button" aria-label="Show support requests" title="Show support requests">
                    <i class="fas fa-chevron-right"></i>
                </button>


                <!-- CHAT HEADER -->

                <div class="cs-chat-header">

                    <div class="cs-resident-info">

                        <div
                            id="supportResidentAvatar"
                            class="cs-resident-avatar">
                            ?
                        </div>

                        <div class="cs-resident-details">

                            <h2
                                id="supportResidentName"
                                class="cs-resident-name">
                                Select a conversation
                            </h2>

                            <div class="cs-resident-status">

                                <span
                                    id="supportStatusDot"
                                    class="cs-status-dot"></span>

                                <span
                                    id="supportStatusText">
                                </span>

                            </div>

                        </div>

                    </div>


                    <div class="cs-actions">

                        <button
                            id="closeSupportConversation"
                            type="button"
                            class="cs-btn close-support"
                            disabled>
                            <i class="fas fa-check-circle"></i>

                            Close
                        </button>

                    </div>

                </div>


                <!-- EMPTY STATE -->

                <div
                    id="supportChatEmpty"
                    class="cs-chat-empty">

                    <div class="cs-chat-empty-inner">

                        <i class="fas fa-comments"></i>

                            <h3>
                                Select a resident
                            </h3>

                        <p>
                            Select a resident from the support queue to view the conversation.
                        </p>

                    </div>

                </div>


                <!-- MESSAGES -->

                <div
                    id="supportChatMessages"
                    class="cs-chat-messages"
                    style="display:none;"></div>


                <!-- NOTICE -->

                <div
                    id="supportNotice"
                    class="cs-notice"></div>


                <!-- COMPOSER -->

                <div
                    id="supportComposer"
                    class="cs-composer"
                    style="display:none;">

                    <textarea
                        id="staffMessageInput"
                        class="cs-message-input"
                        rows="1"
                        placeholder="Type your message to the resident..."
                        disabled></textarea>

                    <button
                        id="staffSendButton"
                        type="button"
                        class="cs-send-btn"
                        title="Send message"
                        disabled>
                        <i class="fas fa-paper-plane"></i>
                    </button>

                </div>

            </div>

        </section>

    </main>
        </div>
    </div>

    <div class="db-modal-overlay" id="csCloseModal" role="dialog" aria-modal="true" aria-labelledby="csCloseModalTitle" aria-hidden="true">
        <div class="db-modal" role="document" style="max-width:440px;">
            <div class="db-modal-header">
                <h3 id="csCloseModalTitle"><i class="fas fa-check-circle"></i> Close conversation</h3>
                <button type="button" class="db-modal-close" id="csCloseModalDismiss" aria-label="Cancel">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="db-modal-body">
                <p style="margin:0;color:#6b7280;font-size:14px;line-height:1.6;">Close this conversation and return the resident to the BIS Assistant?</p>
            </div>
            <div class="db-modal-footer">
                <button type="button" class="db-btn db-btn--outline" id="csCloseModalCancel">Cancel</button>
                <button type="button" class="db-btn db-btn--danger" id="csCloseModalConfirm"><i class="fas fa-check"></i> Close</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const layout = document.querySelector('.cs-layout');
            const collapse = document.getElementById('csQueueToggle');
            const expand = document.getElementById('csQueueExpand');
            if (!layout || !collapse || !expand) return;
            const storageKey = 'bisCsQueueCollapsed';

            function setCollapsed(collapsed) {
                layout.classList.toggle('is-queue-collapsed', collapsed);
                collapse.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                sessionStorage.setItem(storageKey, collapsed ? '1' : '0');
            }

            collapse.addEventListener('click', function () {
                setCollapsed(true);
            });
            expand.addEventListener('click', function () {
                setCollapsed(false);
            });

            if (sessionStorage.getItem(storageKey) === '1') {
                setCollapsed(true);
            }
        })();
    </script>
    <script src="<?= base_url('js/customer_service.js?v=7') ?>"></script>

</body>

</html>