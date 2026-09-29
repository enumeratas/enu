<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Household — Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        /* ── Page wrapper ── */
        .cf-page {
            width: 100%;
            max-width: none;
        }

        /* ── Header ── */
        .pf-page-header {
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            border-radius: 12px;
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .pf-page-header-logo img {
            width: 46px;
            height: 46px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .pf-page-header-text .republic {
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .55);
            margin-bottom: 2px;
        }

        .pf-page-header-text .barangay {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
        }

        .pf-page-header-text .formtitle {
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .5);
            margin-top: 2px;
        }

        /* ── Step tabs ── */
        .pf-page-tabs {
            display: flex;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(29, 36, 72, .07);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .pf-page-tab {
            flex: 1;
            padding: 12px 16px;
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            color: #9aa0b4;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            transition: background .18s, color .18s;
            border-bottom: 3px solid transparent;
        }

        .pf-page-tab:hover:not(.active) {
            color: #1d2448;
            background: #f8f9ff;
        }

        .pf-page-tab.active {
            color: #1d2448;
            background: #f0f2ff;
            border-bottom-color: #1d2448;
        }

        .pf-page-tab-num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #e8ecf4;
            color: #9aa0b4;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .18s, color .18s;
            flex-shrink: 0;
        }

        .pf-page-tab.active .pf-page-tab-num {
            background: #1d2448;
            color: #fff;
        }

        /* ── Section cards ── */
        .pf-page-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(29, 36, 72, .07);
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid #eef0f8;
        }

        .pf-page-section-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 11px 18px;
            background: #f8f9ff;
            border-bottom: 1.5px solid #e8ecf4;
            border-left: 4px solid #1d2448;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #1d2448;
        }

        .pf-page-section-bar i {
            color: #5b6fd6;
            font-size: 13px;
        }

        .pf-page-section-body {
            padding: 18px 20px;
        }

        /* ── Grid rows ── */
        .pf-row {
            display: grid;
            gap: 14px;
            margin-bottom: 14px;
        }

        .pf-row:last-child {
            margin-bottom: 0;
        }

        .pf-row-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .pf-row-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .pf-row-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        @media (max-width: 860px) {
            .pf-row-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .pf-row-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 520px) {

            .pf-row-4,
            .pf-row-3,
            .pf-row-2 {
                grid-template-columns: 1fr;
            }
        }

        /* ── Field label ── */
        .pf-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #4a5068;
            margin-bottom: 5px;
            display: block;
        }

        /* ── Input / select ── */
        .pf-ctrl {
            width: 100%;
            padding: 9px 11px;
            border: 1.5px solid #e2e5ef;
            border-radius: 7px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            box-sizing: border-box;
            transition: border-color .15s, box-shadow .15s;
        }

        .pf-ctrl:focus {
            border-color: #1d2448;
            box-shadow: 0 0 0 3px rgba(29, 36, 72, .07);
        }

        .pf-ctrl::placeholder {
            color: #c0c6d8;
        }

        .pf-ctrl[readonly] {
            background: #f0f4ff;
            color: #1d2448;
            font-weight: 700;
            letter-spacing: 1.5px;
            cursor: default;
        }

        /* ── Date field ── */
        .pf-date-wrap {
            position: relative;
        }

        .pf-date-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0b6cc;
            pointer-events: none;
            font-size: 12px;
        }

        /* ── Radios ── */
        .pf-radio-row {
            display: flex;
            flex-direction: column;
            gap: 7px;
            padding: 4px 0;
        }

        .pf-radio-row.horizontal {
            flex-direction: row;
            gap: 18px;
        }

        .pf-radio {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            color: #374151;
            cursor: pointer;
        }

        .pf-radio input {
            cursor: pointer;
            accent-color: #1d2448;
        }

        /* ── Checkboxes ── */
        .pf-check-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 16px;
            padding: 6px 0 2px;
        }

        .pf-check-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #6b7280;
        }

        .pf-check {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: #374151;
            cursor: pointer;
        }

        .pf-check input {
            cursor: pointer;
            accent-color: #1d2448;
        }

        .pf-married-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border: 1px solid #d0d5e0;
            border-radius: 5px;
            color: #4a5068;
            font-size: 12px;
            cursor: pointer;
            background: #fff;
        }

        .pf-married-toggle:has(input:checked) {
            background: #1d2448;
            border-color: #1d2448;
            color: #fff;
        }

        .pf-married-toggle input {
            accent-color: #1d2448;
        }

        /* ── Sub-section heading ── */
        .pf-sub-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #5b6fd6;
            margin-bottom: 12px;
            padding-top: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pf-sub-label:first-child {
            padding-top: 0;
        }

        /* ── Member cards (family) ── */
        .pf-member-card {
            background: #f8f9fc;
            border: 1.5px solid #e8ecf4;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 10px;
            position: relative;
        }

        .pf-member-card:last-child {
            margin-bottom: 0;
        }

        .pf-member-del {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 1.5px solid #fad4d4;
            background: #fff;
            color: #c0392b;
            font-size: 11px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
        }

        .pf-member-del:hover {
            background: #c0392b;
            color: #fff;
        }

        .pf-member-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .pf-member-fields .pf-field-wrap {
            flex: 1;
            min-width: 130px;
        }

        .pf-member-fields .pf-field-xs {
            flex: 0 0 90px;
        }

        .pf-member-fields .pf-field-sm {
            flex: 0 0 148px;
        }

        #childrenRows .pf-member-fields {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 10px;
            align-items: start;
        }

        #childrenRows .pf-member-fields>* {
            min-width: 0;
        }

        #childrenRows .pf-member-fields> :nth-child(1),
        #childrenRows .pf-member-fields> :nth-child(2),
        #childrenRows .pf-member-fields> :nth-child(3) {
            grid-column: span 3;
        }

        #childrenRows .pf-member-fields> :nth-child(4) {
            grid-column: span 1;
        }

        #childrenRows .pf-member-fields> :nth-child(5),
        #childrenRows .pf-member-fields> :nth-child(6),
        #childrenRows .pf-member-fields> :nth-child(7) {
            grid-column: span 2;
        }

        #childrenRows .pf-member-fields> :nth-child(8) {
            grid-column: span 4;
        }

        #childrenRows .pf-member-fields> :nth-child(9),
        #childrenRows .pf-member-fields> :nth-child(10),
        #childrenRows .pf-member-fields> :nth-child(11),
        #childrenRows .pf-member-fields> :nth-child(12) {
            grid-column: span 2;
        }

        #childrenRows .pf-member-fields>.pf-child-pwd-field {
            grid-column: 1 / -1;
        }

        /* ── Add-row button ── */
        .pf-add-row-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            background: #fff;
            border: 1.5px solid #1d2448;
            border-radius: 7px;
            font-size: 11.5px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            color: #1d2448;
            cursor: pointer;
            transition: background .15s;
        }

        .pf-add-row-btn:hover {
            background: #1d2448;
            color: #fff;
        }

        /* ── Occupation pills ── */
        .pf-occ-wrap {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .pf-occ-pills {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .pf-occ-pill {
            padding: 3px 9px;
            border-radius: 100px;
            border: 1.5px solid #e2e5ef;
            background: #fff;
            font-size: 11px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            color: #6b7280;
            cursor: pointer;
            transition: all .15s;
        }

        .pf-occ-pill.active,
        .pf-occ-pill:hover {
            background: #1d2448;
            border-color: #1d2448;
            color: #fff;
        }

        .pf-grade-wrap {
            display: none;
        }

        .pf-grade-wrap.visible {
            display: block;
        }

        .pf-grade-label {
            font-size: 10.5px;
            font-weight: 600;
            color: #9aa0b4;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 4px;
        }

        /* ── Certification ── */
        .pf-cert-box {
            background: #f5f7ff;
            border: 1px solid #dde2f5;
            border-radius: 10px;
            padding: 16px 18px;
            font-size: 12.5px;
            color: #4a5068;
            line-height: 1.7;
        }

        .pf-cert-date-wrap {
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pf-cert-date-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #6b7280;
            white-space: nowrap;
        }

        /* ── Footer ── */
        .pf-page-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 6px;
            margin-top: 4px;
        }

        .pf-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px 22px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            border: none;
            transition: opacity .18s, transform .13s;
        }

        .pf-page-btn:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .pf-page-btn--primary {
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
        }

        .pf-page-btn--outline {
            background: #fff;
            color: #4a5068;
            border: 1.5px solid #e2e5ef;
        }

        .pf-page-btn--outline:hover {
            border-color: #1d2448;
            color: #1d2448;
            opacity: 1;
        }

        /* ── Step panels ── */
        .pf-step {
            display: none;
        }

        .pf-step.active {
            display: block;
        }

        /* ── Field error state ── */
        .pf-ctrl.pf-error {
            border-color: #e74c3c !important;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, .12) !important;
        }

        .pf-error-msg {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            color: #c0392b;
            margin-top: 4px;
            font-weight: 500;
        }

        .pf-error-msg i {
            font-size: 10px;
            flex-shrink: 0;
        }

        /* ── Step-level error banner ── */
        .pf-step-error {
            display: none;
            align-items: flex-start;
            gap: 10px;
            background: #fff0f1;
            border: 1.5px solid #fad4d4;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: #c0392b;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        /* ── Document upload cards ── */
        .pf-id-upload {
            margin-top: 12px;
            padding: 14px;
            border: 1px solid #e2e6f2;
            border-radius: 12px;
            background: linear-gradient(135deg, #fbfcff, #f5f7ff);
        }

        .pf-id-label {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
            color: #26315d;
            font-size: 12px;
            font-weight: 700;
        }

        .pf-id-label i {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #e9edff;
            color: #5b6fd6;
            font-size: 13px;
        }

        .pf-req {
            margin-left: auto;
            padding: 3px 7px;
            border-radius: 5px;
            background: #fff0f1;
            color: #c0392b;
            font-size: 9px;
            letter-spacing: .35px;
        }

        .pf-file-drop {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 58px;
            padding: 10px 12px;
            border: 1.5px dashed #bfc8e8;
            border-radius: 9px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s, background .18s, box-shadow .18s;
        }

        .pf-file-drop:hover,
        .pf-file-drop:focus-within {
            border-color: #5b6fd6;
            background: #f8f9ff;
            box-shadow: 0 0 0 3px rgba(91, 111, 214, .1);
        }

        .pf-file-drop-icon {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 9px;
            background: #eef0fb;
            color: #5b6fd6;
            font-size: 15px;
        }

        .pf-file-drop-copy {
            min-width: 0;
            line-height: 1.35;
        }

        .pf-file-drop-title {
            display: block;
            color: #1d2448;
            font-size: 12px;
            font-weight: 700;
        }

        .pf-file-name {
            display: block;
            max-width: 100%;
            overflow: hidden;
            color: #5b6fd6;
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pf-file-input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .pf-file-hint {
            margin-top: 7px;
            color: #8b93aa;
            font-size: 10.5px;
        }

        .pf-file-hint i {
            margin-right: 4px;
            color: #5b6fd6;
        }

        .pf-id-pair {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .pf-ocr-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .pf-ocr-status {
            flex: 1 1 260px;
            font-size: 12px;
            font-weight: 600;
            color: #6b7291;
            padding: 8px 10px;
            border: 1px dashed #d7dce6;
            background: #f8f9ff;
            min-height: 34px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pf-ocr-status.is-pending {
            color: #8657c9;
            border-color: #c8b6ea;
            background: #f6f0ff;
        }

        .pf-ocr-status.is-ok {
            color: #197a3e;
            border-color: #a6d9b9;
            background: #ecf8f0;
            border-style: solid;
        }

        .pf-ocr-status.is-fail {
            color: #b5321a;
            border-color: #e7b3a5;
            background: #fdefeb;
            border-style: solid;
        }

        .pf-ocr-btn {
            border: 1px solid #5b6fd6;
            background: #fff;
            color: #5b6fd6;
            padding: 8px 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .pf-ocr-btn:hover:not(:disabled) {
            background: #5b6fd6;
            color: #fff;
        }

        .pf-ocr-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        @media (max-width: 640px) {
            .pf-id-pair {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {
            #childrenRows .pf-member-fields {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            #childrenRows .pf-member-fields>* {
                grid-column: span 1 !important;
            }

            #childrenRows .pf-member-fields> :nth-child(1),
            #childrenRows .pf-member-fields> :nth-child(2),
            #childrenRows .pf-member-fields> :nth-child(3),
            #childrenRows .pf-member-fields> :nth-child(8) {
                grid-column: span 2 !important;
            }

            .pf-id-label {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .pf-req {
                margin-left: 35px;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $active    = 'census';
    $pageTitle = 'Add Household';
    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16p x;">
                    <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <div class="cf-page">

                <!-- ── Header ── -->
                <div class="pf-page-header">
                    <div class="pf-page-header-logo">
                        <img src="/bacolod.png" alt="Seal">
                    </div>
                    <div class="pf-page-header-text">
                        <div class="republic">Republic of the Philippines</div>
                        <div class="barangay">Barangay Bacolod, Bato, Camarines Sur</div>
                        <div class="formtitle">Household Census Registration Form</div>
                    </div>
                </div>

                <!-- ── Tabs ── -->
                <div class="pf-page-tabs">
                    <button class="pf-page-tab active" id="tab1" type="button" onclick="goTo(1)">
                        <span class="pf-page-tab-num">1</span> Personal Information
                    </button>
                    <button class="pf-page-tab" id="tab2" type="button" onclick="goTo(2)">
                        <span class="pf-page-tab-num">2</span> Family Information
                    </button>
                </div>

                <!-- ── Form ── -->
                <form action="/<?= esc(session()->get('role')) ?>/census/store" method="post" id="censusForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- ── Validation error banner (shown when required fields are missing) ── -->
                    <div class="pf-step-error" id="formErrorBanner">
                        <i class="fas fa-exclamation-circle" style="font-size:16px;flex-shrink:0;margin-top:1px;"></i>
                        <span id="formErrorText">Please fill in all required fields before continuing.</span>
                    </div>

                    <!-- ════════════════════════════════════════════════════
                         STEP 1 — Personal Information
                         ════════════════════════════════════════════════════ -->
                    <div class="pf-step active" id="step1">

                        <!-- Household Head -->
                        <div class="pf-page-section">
                            <div class="pf-page-section-bar">
                                <i class="fas fa-user"></i> Household Head — Personal Information
                            </div>
                            <div class="pf-page-section-body">

                                <div class="pf-row pf-row-4">
                                    <div>
                                        <div class="pf-label">Last Name</div>
                                        <input type="text" class="pf-ctrl pf-upper pf-alpha" name="last_name" placeholder="DELA CRUZ" required>
                                    </div>
                                    <div>
                                        <div class="pf-label">First Name</div>
                                        <input type="text" class="pf-ctrl pf-upper pf-alpha" name="first_name" placeholder="JUAN" required>
                                    </div>
                                    <div>
                                        <div class="pf-label">Middle Name</div>
                                        <input type="text" class="pf-ctrl pf-upper pf-alpha" name="middle_name" placeholder="SANTOS">
                                    </div>
                                    <div>
                                        <div class="pf-label">Suffix</div>
                                        <select class="pf-ctrl" name="suffix">
                                            <option value="">— NONE —</option>
                                            <option>Jr</option>
                                            <option>Sr</option>
                                            <option>II</option>
                                            <option>III</option>
                                            <option>IV</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="pf-row pf-row-4">
                                    <div>
                                        <div class="pf-label">Date of Birth</div>
                                        <div class="pf-date-wrap">
                                            <input type="date" class="pf-ctrl" name="date_of_birth" required>
                                            <i class="fas fa-calendar-alt pf-date-icon"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="pf-label">Place of Birth</div>
                                        <input type="text" class="pf-ctrl pf-upper pf-alpha" name="place_of_birth" placeholder="CITY/MUNICIPALITY" required>
                                    </div>
                                    <div>
                                        <div class="pf-label">Gender</div>
                                        <select class="pf-ctrl" name="gender">
                                            <option>Male</option>
                                            <option>Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="pf-label">Civil Status</div>
                                        <select class="pf-ctrl" name="civil_status">
                                            <option>Single</option>
                                            <option>Married</option>
                                            <option>Widowed</option>
                                            <option>Separated</option>
                                            <option>Annulled</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="pf-row pf-row-4">
                                    <div>
                                        <div class="pf-label">Nationality</div>
                                        <input type="text" class="pf-ctrl pf-upper pf-alpha" name="nationality" value="FILIPINO" required>
                                    </div>
                                    <div>
                                        <div class="pf-label">Religion</div>
                                        <input type="text" class="pf-ctrl pf-upper pf-alpha" name="religion" placeholder="E.G. ROMAN CATHOLIC" required>
                                    </div>
                                    <div>
                                        <div class="pf-label">Occupation</div>
                                        <input type="text" class="pf-ctrl pf-upper pf-alpha" name="occupation" placeholder="E.G. FARMER">
                                    </div>
                                    <div>
                                        <div class="pf-label">Monthly Income (₱)</div>
                                        <input type="number" class="pf-ctrl" name="monthly_income" placeholder="0.00" min="0" step="0.01">
                                    </div>
                                </div>

                                <div class="pf-row pf-row-3">
                                    <div>
                                        <div class="pf-label">Contact Number</div>
                                        <input type="tel" class="pf-ctrl js-contact-number" name="contact_number" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)" required>
                                    </div>
                                    <div>
                                        <div class="pf-label">Educational Attainment</div>
                                        <select class="pf-ctrl" name="educational_attainment" required>
                                            <option value="">— Select —</option>
                                            <option>No Formal Education</option>
                                            <option>Elementary Level</option>
                                            <option>Elementary Graduate</option>
                                            <option>High School Level</option>
                                            <option>High School Graduate</option>
                                            <option>College Level</option>
                                            <option>College Graduate</option>
                                            <option>Vocational / Tech-Voc</option>
                                            <option>Post Graduate</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="pf-label">PhilHealth Number</div>
                                        <input type="text" class="pf-ctrl pf-philhealth" name="philhealth_no" placeholder="00000000000" maxlength="12" inputmode="numeric">
                                    </div>
                                </div>

                                <div class="pf-label" style="margin-top:4px;">Are you a Registered Voter?</div>
                                <div class="pf-radio-row horizontal">
                                    <label class="pf-radio"><input type="radio" name="registered_voter" value="1"> <span>Yes</span></label>
                                    <label class="pf-radio"><input type="radio" name="registered_voter" value="0"> <span>No</span></label>
                                </div>

                            </div>
                        </div>

                        <!-- Household Classification -->
                        <div class="pf-page-section">
                            <div class="pf-page-section-bar">
                                <i class="fas fa-tags"></i> Household Classification
                            </div>
                            <div class="pf-page-section-body">

                                <div class="pf-row pf-row-4">
                                    <div>
                                        <div class="pf-label">
                                            Household No.
                                            <span style="font-size:9px;color:#16c79a;font-weight:700;margin-left:4px;">AUTO</span>
                                        </div>
                                        <input type="text" class="pf-ctrl" name="household_no" id="householdNo" readonly>
                                    </div>
                                    <div>
                                        <div class="pf-label">Zone / Purok</div>
                                        <select class="pf-ctrl" name="zone" required>
                                            <option value="">— Select —</option>
                                            <option>Zone 1</option>
                                            <option>Zone 2</option>
                                            <option>Zone 3</option>
                                            <option>Zone 4</option>
                                            <option>Zone 5</option>
                                            <option>Zone 6</option>
                                            <option>Zone 7</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="pf-label">Years of Residency</div>
                                        <input type="number" class="pf-ctrl" name="years_of_residency" placeholder="0" min="0" required>
                                    </div>
                                    <div>
                                        <div class="pf-label">House Ownership</div>
                                        <select class="pf-ctrl" name="house_ownership" id="houseOwnershipSelect" onchange="toggleNumFamilies(this.value)">
                                            <option>Owned</option>
                                            <option>Rented</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="pf-check-row">
                                    <span class="pf-check-label">Household belongs to:</span>
                                    <label class="pf-check"><input type="checkbox" name="is_4ps" value="1" id="head_is_4ps" onchange="toggleIdUpload('head_is_4ps','id_4ps_wrap')"> <span>4Ps Beneficiary</span></label>
                                    <label class="pf-check"><input type="checkbox" name="is_senior_citizen" value="1" id="head_is_senior" onchange="toggleIdUpload('head_is_senior','id_senior_wrap')"> <span>Senior Citizen</span></label>
                                    <label class="pf-check"><input type="checkbox" name="is_solo_parent" value="1" id="head_is_solo" onchange="toggleIdUpload('head_is_solo','id_solo_wrap')"> <span>Solo Parent</span></label>
                                    <label class="pf-check"><input type="checkbox" name="is_indigenous" value="1"> <span>Indigenous People</span></label>
                                </div>

                                <!-- Conditional ID uploads (visible only when the related checkbox is on) -->
                                <div id="id_4ps_wrap" class="pf-id-upload" style="display:none;" data-id-block data-id-key="4ps">
                                    <label class="pf-id-label">
                                        <i class="fas fa-id-card"></i> 4Ps Beneficiary ID <span class="pf-req">* REQUIRED</span>
                                    </label>
                                    <div class="pf-id-pair">
                                        <label class="pf-file-drop">
                                            <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Front of ID</span><span class="pf-file-name">No file selected</span></span>
                                            <input type="file" name="id_4ps" accept="image/*,application/pdf" class="pf-file pf-file-input" data-ocr-front>
                                        </label>
                                        <label class="pf-file-drop">
                                            <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Back of ID</span><span class="pf-file-name">No file selected</span></span>
                                            <input type="file" name="id_4ps_back" accept="image/*,application/pdf" class="pf-file pf-file-input" data-ocr-back>
                                        </label>
                                    </div>
                                    <div class="pf-file-hint"><i class="fas fa-info-circle"></i> PDF, JPG, or PNG - maximum 5 MB each. OCR will compare the ID with the personal information.</div>
                                    <input type="hidden" name="id_4ps_verified" value="0" data-ocr-flag>
                                    <div class="pf-ocr-row"><div class="pf-ocr-status" data-ocr-status>Fill in the name and date of birth, then upload the front and back of the ID.</div><button type="button" class="pf-ocr-btn" data-ocr-run><i class="fas fa-fingerprint"></i> Verify with OCR</button></div>
                                </div>
                                <div id="id_senior_wrap" class="pf-id-upload" style="display:none;" data-id-block data-id-key="senior">
                                    <label class="pf-id-label"><i class="fas fa-id-card"></i> Senior Citizen ID / Photo <span style="font-weight:400;color:#6b7291;">(optional)</span></label>
                                    <div class="pf-id-pair">
                                        <label class="pf-file-drop">
                                            <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Front of ID</span><span class="pf-file-name">No file selected</span></span>
                                            <input type="file" name="id_senior" accept="image/*,application/pdf" class="pf-file pf-file-input" data-ocr-front>
                                        </label>
                                        <label class="pf-file-drop">
                                            <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Back of ID</span><span class="pf-file-name">No file selected</span></span>
                                            <input type="file" name="id_senior_back" accept="image/*,application/pdf" class="pf-file pf-file-input" data-ocr-back>
                                        </label>
                                    </div>
                                    <div class="pf-file-hint"><i class="fas fa-info-circle"></i> Optional. PDF, JPG, or PNG - maximum 5 MB each. OCR will compare the ID with the personal information.</div>
                                    <input type="hidden" name="id_senior_verified" value="0" data-ocr-flag>
                                    <div class="pf-ocr-row"><div class="pf-ocr-status" data-ocr-status>Fill in the name and date of birth, then upload the front and back of the ID.</div><button type="button" class="pf-ocr-btn" data-ocr-run><i class="fas fa-fingerprint"></i> Verify with OCR</button></div>
                                </div>
                                <div id="id_solo_wrap" class="pf-id-upload" style="display:none;" data-id-block data-id-key="solo">
                                    <label class="pf-id-label">
                                        <i class="fas fa-id-card"></i> Solo Parent ID <span class="pf-req">* REQUIRED</span>
                                    </label>
                                    <div class="pf-id-pair">
                                        <label class="pf-file-drop">
                                            <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Front of ID</span><span class="pf-file-name">No file selected</span></span>
                                            <input type="file" name="id_solo_parent" accept="image/*,application/pdf" class="pf-file pf-file-input" data-ocr-front>
                                        </label>
                                        <label class="pf-file-drop">
                                            <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                            <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Back of ID</span><span class="pf-file-name">No file selected</span></span>
                                            <input type="file" name="id_solo_parent_back" accept="image/*,application/pdf" class="pf-file pf-file-input" data-ocr-back>
                                        </label>
                                    </div>
                                    <div class="pf-file-hint"><i class="fas fa-info-circle"></i> PDF, JPG, or PNG - maximum 5 MB each. OCR will compare the ID with the personal information.</div>
                                    <input type="hidden" name="id_solo_parent_verified" value="0" data-ocr-flag>
                                    <div class="pf-ocr-row"><div class="pf-ocr-status" data-ocr-status>Fill in the name and date of birth, then upload the front and back of the ID.</div><button type="button" class="pf-ocr-btn" data-ocr-run><i class="fas fa-fingerprint"></i> Verify with OCR</button></div>
                                </div>

                                <div style="margin-top:10px;">
                                    <div class="pf-label" style="margin-bottom:4px;">PWD?</div>
                                    <div style="display:flex;gap:14px;align-items:center;">
                                        <label class="pf-radio"><input type="checkbox" name="is_pwd" value="1" id="head_is_pwd" onchange="togglePwdType(this,'head_pwd_type_wrap',true);toggleIdUpload('head_is_pwd','id_pwd_wrap','checkbox')"> <span>Yes — PWD Member</span></label>
                                    </div>
                                    <div id="head_pwd_type_wrap" style="display:none;margin-top:6px;">
                                        <input type="text" class="pf-ctrl" name="pwd_type" id="head_pwd_type" placeholder="Specify disability (e.g. Visual, Hearing, Physical, Intellectual…)" maxlength="120">
                                    </div>
                                    <div id="id_pwd_wrap" class="pf-id-upload" style="display:none;" data-id-block data-id-key="pwd">
                                        <label class="pf-id-label">
                                            <i class="fas fa-id-card"></i> PWD ID Card <span class="pf-req">* REQUIRED</span>
                                        </label>
                                        <div class="pf-id-pair">
                                            <label class="pf-file-drop">
                                                <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                                <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Front of ID</span><span class="pf-file-name">No file selected</span></span>
                                                <input type="file" name="id_pwd" accept="image/*,application/pdf" class="pf-file pf-file-input" data-ocr-front>
                                            </label>
                                            <label class="pf-file-drop">
                                                <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                                <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Back of ID</span><span class="pf-file-name">No file selected</span></span>
                                                <input type="file" name="id_pwd_back" accept="image/*,application/pdf" class="pf-file pf-file-input" data-ocr-back>
                                            </label>
                                        </div>
                                        <div class="pf-file-hint"><i class="fas fa-info-circle"></i> PDF, JPG, or PNG - maximum 5 MB each. OCR will compare the ID with the personal information.</div>
                                        <input type="hidden" name="id_pwd_verified" value="0" data-ocr-flag>
                                        <div class="pf-ocr-row"><div class="pf-ocr-status" data-ocr-status>Fill in the name and date of birth, then upload the front and back of the ID.</div><button type="button" class="pf-ocr-btn" data-ocr-run><i class="fas fa-fingerprint"></i> Verify with OCR</button></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Water & Sanitation -->
                        <div class="pf-page-section">
                            <div class="pf-page-section-bar">
                                <i class="fas fa-tint"></i> Access to Safe Water &amp; Sanitation Facility
                            </div>
                            <div class="pf-page-section-body">

                                <div class="pf-sub-label"><i class="fas fa-water"></i> Access to Safe Water</div>
                                <div class="pf-row pf-row-2">
                                    <div>
                                        <div class="pf-label">1. Basic Safe Water Source</div>
                                        <div class="pf-radio-row">
                                            <label class="pf-radio"><input type="radio" name="water_source" value="I"> <span>Level I — Point Source (e.g. protected well, spring)</span></label>
                                            <label class="pf-radio"><input type="radio" name="water_source" value="II"> <span>Level II — Communal Faucet / Stand Post</span></label>
                                            <label class="pf-radio"><input type="radio" name="water_source" value="III"> <span>Level III — Individual House Connection (piped water)</span></label>
                                            <label class="pf-radio"><input type="radio" name="water_source" value="none"> <span>No Safe Water Source</span></label>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="pf-label">2. Using Safety-Managed Water Service</div>
                                        <div class="pf-radio-row">
                                            <label class="pf-radio"><input type="radio" name="water_managed" value="yes"> <span>Yes — Water is safely managed</span></label>
                                            <label class="pf-radio"><input type="radio" name="water_managed" value="no"> <span>No — Not safely managed</span></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="pf-sub-label" style="border-top:1px solid #f0f2f8;"><i class="fas fa-toilet"></i> Sanitation Facility</div>
                                <div class="pf-row pf-row-2">
                                    <div>
                                        <div class="pf-label">1. Basic Sanitation Facility</div>
                                        <div class="pf-radio-row">
                                            <label class="pf-radio"><input type="radio" name="sanitation_basic" value="with"> <span>With Basic Sanitation Facility</span></label>
                                            <label class="pf-radio"><input type="radio" name="sanitation_basic" value="without"> <span>Without Basic Sanitation Facility</span></label>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="pf-label">2. Using Safely Managed Sanitation Services</div>
                                        <div class="pf-radio-row">
                                            <label class="pf-radio"><input type="radio" name="sanitation_managed" value="with"> <span>With Safely Managed Sanitation</span></label>
                                            <label class="pf-radio"><input type="radio" name="sanitation_managed" value="without"> <span>Without Safely Managed Sanitation</span></label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="pf-page-footer" style="display:flex;justify-content:flex-end;">
                            <button class="pf-page-btn pf-page-btn--primary" id="nextBtn" type="button" onclick="goNext()">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>

                    </div><!-- end step 1 -->

                    <!-- ════════════════════════════════════════════════════
                         STEP 2 — Family Information
                         ════════════════════════════════════════════════════ -->
                    <div class="pf-step" id="step2">

                        <!-- Spouse -->
                        <div class="pf-page-section">
                            <div class="pf-page-section-bar">
                                <i class="fas fa-ring"></i> Spouse
                            </div>
                            <div class="pf-page-section-body">
                                <div class="pf-row pf-row-4">
                                    <div>
                                        <div class="pf-label">Last Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="spouse_last_name" placeholder="LAST NAME">
                                    </div>
                                    <div>
                                        <div class="pf-label">First Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="spouse_first_name" placeholder="FIRST NAME">
                                    </div>
                                    <div>
                                        <div class="pf-label">Middle Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="spouse_middle_name" placeholder="MIDDLE NAME">
                                    </div>
                                    <div>
                                        <div class="pf-label">Suffix</div>
                                        <select class="pf-ctrl" name="spouse_suffix">
                                            <option value="">— NONE —</option>
                                            <option>Jr</option>
                                            <option>Sr</option>
                                            <option>II</option>
                                            <option>III</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="pf-row pf-row-4">
                                    <div>
                                        <div class="pf-label">Date of Birth</div>
                                        <div class="pf-date-wrap">
                                            <input type="date" class="pf-ctrl" name="spouse_dob">
                                            <i class="fas fa-calendar-alt pf-date-icon"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="pf-label">Gender</div>
                                        <select class="pf-ctrl" name="spouse_gender">
                                            <option value="">— Select —</option>
                                            <option>Male</option>
                                            <option>Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="pf-label">Occupation</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="spouse_occupation" placeholder="E.G. HOUSEWIFE">
                                    </div>
                                    <div>
                                        <div class="pf-label">Monthly Income (₱)</div><input type="number" class="pf-ctrl" name="spouse_income" placeholder="0.00" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="pf-row pf-row-3">
                                    <div>
                                        <div class="pf-label">Educational Attainment</div>
                                        <select class="pf-ctrl" name="spouse_educational_attainment">
                                            <option value="">— Select —</option>
                                            <option>No Formal Education</option>
                                            <option>Elementary Level</option>
                                            <option>Elementary Graduate</option>
                                            <option>High School Level</option>
                                            <option>High School Graduate</option>
                                            <option>College Level</option>
                                            <option>College Graduate</option>
                                            <option>Vocational / Tech-Voc</option>
                                            <option>Post Graduate</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="pf-label">PhilHealth Number</div><input type="text" class="pf-ctrl pf-philhealth" name="spouse_philhealth" placeholder="00000000000" maxlength="12" inputmode="numeric">
                                    </div>
                                    <div>
                                        <div class="pf-label">Registered Voter?</div>
                                        <div class="pf-radio-row horizontal">
                                            <label class="pf-radio"><input type="radio" name="spouse_registered_voter" value="1"> <span>Yes</span></label>
                                            <label class="pf-radio"><input type="radio" name="spouse_registered_voter" value="0" checked> <span>No</span></label>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="pf-label">PWD?</div>
                                        <div class="pf-radio-row horizontal" id="spouse_pwd_row">
                                            <label class="pf-radio"><input type="radio" name="spouse_pwd" value="1" onchange="togglePwdType(this,'spouse_pwd_type_wrap')"> <span>Yes</span></label>
                                            <label class="pf-radio"><input type="radio" name="spouse_pwd" value="0" checked onchange="togglePwdType(this,'spouse_pwd_type_wrap')"> <span>No</span></label>
                                        </div>
                                        <div id="spouse_pwd_type_wrap" style="display:none;margin-top:6px;">
                                            <input type="text" class="pf-ctrl" name="spouse_pwd_type" placeholder="Specify disability (e.g. Visual, Hearing…)" maxlength="120">
                                            <div class="pf-id-upload" style="margin-top:10px;">
                                                <label class="pf-id-label"><i class="fas fa-id-card"></i> PWD Photo / ID <span class="pf-req">* REQUIRED</span></label>
                                                <label class="pf-file-drop">
                                                    <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                                    <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Choose a document to upload</span><span class="pf-file-name">No file selected</span></span>
                                                    <input type="file" name="spouse_id_pwd" accept="image/*,application/pdf" class="pf-file pf-file-input">
                                                </label>
                                                <div class="pf-file-hint"><i class="fas fa-info-circle"></i> PDF, JPG, or PNG - maximum 5 MB</div>
                                            </div>
                                        </div>
                                        <!-- <div style="margin-top:8px;">
                                            <label class="pf-check"><input type="checkbox" name="spouse_senior" value="1"> <span>Senior (60+)</span></label>
                                            <input type="file" name="spouse_id_senior" accept="image/*,application/pdf" style="width:100%;margin-top:5px;font-size:11px;">
                                        </div> -->

                                        <div class="pf-check-row">
                                            <label class="pf-check"><input type="checkbox" name="spouse_senior" value="1" id="spouse_is_senior" onchange="toggleIdUpload('spouse_id_senior','id_senior_wrap')"> <span>Senior Citizen</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Children -->
                            <div class="pf-page-section" id="childrenSection">
                                <div class="pf-page-section-bar">
                                    <i class="fas fa-child"></i> Child(ren)
                                    <span style="flex:1;"></span>
                                    <button type="button" class="pf-add-row-btn" onclick="addChildRow()" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.4);color:#fff;font-size:11px;padding:4px 12px;">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                </div>
                                <div class="pf-page-section-body" id="childrenRows">
                                    <?= childRowHTML(0) ?>
                                </div>
                            </div>

                            <!-- Other Household Members -->
                            <div class="pf-page-section">
                                <div class="pf-page-section-bar">
                                    <i class="fas fa-users"></i> Other Household Members
                                    <em style="font-weight:400;font-size:10px;margin-left:4px;">(other than Spouse/Children)</em>
                                    <span style="flex:1;"></span>
                                    <button type="button" class="pf-add-row-btn" onclick="addOtherRow()" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.4);color:#fff;font-size:11px;padding:4px 12px;">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                </div>
                                <div class="pf-page-section-body" id="otherRows">
                                    <?= otherRowHTML(0) ?>
                                </div>
                            </div>

                            <!-- Certification -->
                            <div class="pf-page-section">
                                <div class="pf-page-section-body">
                                    <div class="pf-cert-box">
                                        <p>I hereby certify that the information provided above is true and correct to the best of my knowledge.</p>
                                        <div class="pf-cert-date-wrap">
                                            <span class="pf-cert-date-label">Date of Transaction:</span>
                                            <div class="pf-date-wrap" style="flex:0 0 200px;">
                                                <input type="date" class="pf-ctrl" id="recordedDate" name="recorded_date">
                                                <i class="fas fa-calendar-alt pf-date-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- end step 2 -->

                        <!-- ── Footer navigation ── -->
                        <div class="pf-page-footer">
                            <button class="pf-page-btn pf-page-btn--outline" type="button" id="prevBtn" onclick="goPrev()" style="display:none;">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <div style="flex:1;"></div>
                            <a href="/<?= esc(session()->get('role')) ?>/census" class="pf-page-btn pf-page-btn--outline">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button class="pf-page-btn pf-page-btn--primary" id="saveBtn" type="submit" style="display:none;">
                                <i class="fas fa-save"></i> Save Record
                            </button>
                        </div>

                </form>
            </div><!-- /.cf-page -->

        </div><!-- /.db-content -->
    </div><!-- /.db-main -->

    <?php
    /* PHP helpers for initial row HTML — also used in JS templates */
    function childRowHTML(int $i): string
    {
        $gradeOpts = '<option value="">— Select Grade / Year —</option>
            <optgroup label="Elementary"><option>Grade 1</option><option>Grade 2</option><option>Grade 3</option><option>Grade 4</option><option>Grade 5</option><option>Grade 6</option></optgroup>
            <optgroup label="Junior High School"><option>Grade 7</option><option>Grade 8</option><option>Grade 9</option><option>Grade 10</option></optgroup>
            <optgroup label="Senior High School"><option>Grade 11</option><option>Grade 12</option></optgroup>
            <optgroup label="College / University"><option>1st Year College</option><option>2nd Year College</option><option>3rd Year College</option><option>4th Year College</option><option>5th Year College</option></optgroup>
            <optgroup label="Vocational / Technical"><option>1st Year Tech-Voc</option><option>2nd Year Tech-Voc</option></optgroup>';
        return '<div class="pf-member-card">
            <div class="pf-member-fields">
                <div class="pf-field-wrap"><div class="pf-label">Last Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="child_last_name[]" placeholder="LAST NAME"></div>
                <div class="pf-field-wrap"><div class="pf-label">First Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="child_first_name[]" placeholder="FIRST NAME"></div>
                <div class="pf-field-wrap"><div class="pf-label">Middle Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="child_middle_name[]" placeholder="MIDDLE NAME"></div>
                <div class="pf-field-xs"><div class="pf-label">Suffix</div><select class="pf-ctrl" name="child_suffix[]"><option value="">—NONE—</option><option>Jr</option><option>Sr</option><option>II</option><option>III</option></select></div>
                <div class="pf-field-sm"><div class="pf-label">Date of Birth</div><div class="pf-date-wrap"><input type="date" class="pf-ctrl" name="child_dob[]"><i class="fas fa-calendar-alt pf-date-icon"></i></div></div>
                <div class="pf-field-xs"><div class="pf-label">Gender</div><select class="pf-ctrl" name="child_gender[]"><option value="">—Select—</option><option>Male</option><option>Female</option></select></div>
                <div class="pf-field-sm"><div class="pf-label">Civil Status</div><input type="hidden" name="child_marital_status[]" value="Single"><label class="pf-married-toggle"><input type="checkbox" onchange="toggleChildMarried(this)"><span>Married</span></label></div>
                <div class="pf-field-wrap"><div class="pf-label">Occupation</div>
                    <div class="pf-occ-wrap">
                        <div class="pf-occ-pills">
                            <button type="button" class="pf-occ-pill" data-val="STUDENT" onclick="setOcc(this,\'STUDENT\')">Student</button>
                            <button type="button" class="pf-occ-pill" data-val="WORKING STUDENT" onclick="setOcc(this,\'WORKING STUDENT\')">Working Student</button>
                            <button type="button" class="pf-occ-pill" data-val="OUT OF SCHOOL" onclick="setOcc(this,\'OUT OF SCHOOL\')">Out of School</button>
                        </div>
                        <input type="text" class="pf-ctrl pf-upper pf-alpha" name="child_occupation[]" placeholder="OR TYPE EXACT OCCUPATION">
                        <div class="pf-grade-wrap"><div class="pf-grade-label">Current Grade / Year</div><select class="pf-ctrl" name="child_grade[]">' . $gradeOpts . '</select></div>
                    </div>
                </div>
                <div class="pf-field-sm"><div class="pf-label">Monthly Income (₱)</div><input type="number" class="pf-ctrl" name="child_income[]" placeholder="0.00" min="0" step="0.01"></div>
                <div class="pf-field-sm"><div class="pf-label">PhilHealth No.</div><input type="text" class="pf-ctrl pf-philhealth" name="child_philhealth[]" placeholder="00000000000" maxlength="12" inputmode="numeric"></div>
                <div class="pf-field-sm"><div class="pf-label">Voter?</div>
                    <div style="display:flex;gap:12px;padding:7px 0;">
                        <label class="pf-radio"><input type="radio" name="child_voter[' . $i . ']" value="1"> <span>Yes</span></label>
                        <label class="pf-radio"><input type="radio" name="child_voter[' . $i . ']" value="0" checked> <span>No</span></label>
                    </div>
                </div>
                <div class="pf-field-sm pf-child-pwd-field"><div class="pf-label">PWD?</div>
                        <div style="display:flex;gap:12px;padding:7px 0;">
                            <label class="pf-radio"><input type="radio" name="child_pwd[' . $i . ']" value="1" onchange="togglePwdType(this,&#39;child_pwd_type_wrap_&#39;+' . $i . ')"> <span>Yes</span></label>
                            <label class="pf-radio"><input type="radio" name="child_pwd[' . $i . ']" value="0" checked onchange="togglePwdType(this,&#39;child_pwd_type_wrap_&#39;+' . $i . ')"> <span>No</span></label>
                        </div>
                        <div id="child_pwd_type_wrap_' . $i . '" style="display:none;margin-top:6px;">
                            <input type="text" class="pf-ctrl" name="child_pwd_type[]" placeholder="Specify disability (e.g. Visual, Hearing...)" maxlength="120">
                            <div class="pf-id-upload" style="margin-top:10px;">
                                <label class="pf-id-label"><i class="fas fa-id-card"></i> PWD Photo / ID <span class="pf-req">* REQUIRED</span></label>
                                <label class="pf-file-drop">
                                    <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                    <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Choose a document to upload</span><span class="pf-file-name">No file selected</span></span>
                                    <input type="file" name="child_id_pwd[]" accept="image/*,application/pdf" class="pf-file pf-file-input">
                                </label>
                                <div class="pf-file-hint"><i class="fas fa-info-circle"></i> PDF, JPG, or PNG - maximum 5 MB</div>
                            </div>
                        </div>
                        <div style="margin-top:8px;">
                            <label class="pf-check"><input type="checkbox" name="child_senior[' . $i . ']" value="1"> <span>Senior (60+)</span></label>
                            <input type="file" name="child_id_senior[]" accept="image/*,application/pdf" style="width:100%;margin-top:5px;font-size:11px;">
                        </div>
            </div>
            <button type="button" class="pf-member-del" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
        </div>';
    }

    function otherRowHTML(int $i): string
    {
        return '<div class="pf-member-card">
            <div class="pf-member-fields">
                <div class="pf-field-wrap"><div class="pf-label">Last Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="other_last_name[]" placeholder="LAST NAME"></div>
                <div class="pf-field-wrap"><div class="pf-label">First Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="other_first_name[]" placeholder="FIRST NAME"></div>
                <div class="pf-field-wrap"><div class="pf-label">Middle Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="other_middle_name[]" placeholder="MIDDLE NAME"></div>
                <div class="pf-field-xs"><div class="pf-label">Suffix</div><select class="pf-ctrl" name="other_suffix[]"><option value="">—NONE—</option><option>Jr</option><option>Sr</option><option>II</option><option>III</option></select></div>
                <div class="pf-field-sm"><div class="pf-label">Date of Birth</div><div class="pf-date-wrap"><input type="date" class="pf-ctrl" name="other_dob[]"><i class="fas fa-calendar-alt pf-date-icon"></i></div></div>
                <div class="pf-field-xs"><div class="pf-label">Gender</div><select class="pf-ctrl" name="other_gender[]"><option value="">—Select—</option><option>Male</option><option>Female</option></select></div>
                <div class="pf-field-sm"><div class="pf-label">Relationship</div>
                    <select class="pf-ctrl" name="other_relationship[]">
                        <option value="">— Select —</option>
                        <option>Father</option>
                        <option>Mother</option>
                        <option>Sibling</option>
                        <option>Grandparent</option>
                        <option>Grandchild</option>
                        <option>Aunt</option>
                        <option>Uncle</option>
                        <option>Father-in-Law</option>
                        <option>Mother-in-Law</option>
                        <option>Cousin</option>
                        <option>Other Relative</option>
                        <option>Non-relative</option>
                    </select>
                </div>
                <div class="pf-field-sm"><div class="pf-label">Voter?</div>
                    <div style="display:flex;gap:12px;padding:7px 0;">
                        <label class="pf-radio"><input type="radio" name="other_voter[' . $i . ']" value="1"> <span>Yes</span></label>
                        <label class="pf-radio"><input type="radio" name="other_voter[' . $i . ']" value="0" checked> <span>No</span></label>
                    </div>
                </div>
                <div class="pf-field-sm"><div class="pf-label">PWD?</div>
                    <div style="display:flex;gap:12px;padding:7px 0;">
                        <label class="pf-radio"><input type="radio" name="other_pwd[' . $i . ']" value="1" onchange="togglePwdType(this,&#39;other_pwd_type_wrap_&#39;+' . $i . ')"> <span>Yes</span></label>
                        <label class="pf-radio"><input type="radio" name="other_pwd[' . $i . ']" value="0" checked onchange="togglePwdType(this,&#39;other_pwd_type_wrap_&#39;+' . $i . ')"> <span>No</span></label>
                        <label class="pf-check"><input type="checkbox" name="other_senior[' . $i . ']" value="1" checked onchange="toggleSeniorType(this,&#39;other_senior_type_wrap_&#39;+' . $i . ')"> <span>Senior (60+)</span></label>

                    </div>
                    <div id="other_pwd_type_wrap_' . $i . '" style="display:none;margin-top:6px;">
                        <input type="text" class="pf-ctrl" name="other_pwd_type[]" placeholder="Specify disability (e.g. Visual, Hearing...)" maxlength="120">
                    </div>
                    <div id="other_senior_type_wrap_' . $i . '" style="display:none;margin-top:6px;">
                        <input type="file" name="other_id_senior[]" accept="image/*,application/pdf" style="width:100%;margin-top:5px;font-size:11px;">
                    </div>
                </div>
            </div>
            <button type="button" class="pf-member-del" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
        </div>';
    }
    ?>

    <script>
        // ── Shared dwelling helpers ───────────────────────────────────────────
        function genGroupCode() {
            return 'SHR-' + String(Math.floor(10000 + Math.random() * 90000));
        }

        function rebuildFamilyCards(n) {
            const count = Math.max(2, Math.min(10, parseInt(n) || 2));
            const container = document.getElementById('sharedFamilyCards');
            if (!container) return;
            container.innerHTML = '';
            for (let i = 2; i <= count; i++) {
                const div = document.createElement('div');
                div.style.cssText = 'background:#fff;border:1px solid #dde2f5;border-radius:8px;padding:10px 12px;margin-bottom:8px;display:flex;align-items:center;gap:10px;';
                div.innerHTML = '<span style="background:#e8ecf4;color:#4a5068;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;">Family ' + i + '</span>' +
                    '<span style="font-size:12px;color:#9aa0b4;">Register separately — use the same Shared Group Code above</span>';
                container.appendChild(div);
            }
        }

        function toggleNumFamilies(val) {
            const row = document.getElementById('numFamiliesRow');
            const input = document.getElementById('numFamiliesInput');
            const grpIn = document.getElementById('sharedGroupInput');
            const grpDis = document.getElementById('sharedGroupDisplay');
            const famIn = document.getElementById('familyNumberInput');
            if (!row) return;
            if (val === 'Shared') {
                row.style.display = 'block';
                if (input && (!input.value || parseInt(input.value) < 2)) input.value = 2;
                if (grpIn && !grpIn.value) {
                    const code = genGroupCode();
                    grpIn.value = code;
                    if (grpDis) grpDis.textContent = code;
                }
                if (famIn) famIn.value = 1;
                const familySelect = document.getElementById('familyNumberSelect');
                if (familySelect) familySelect.value = 1;
                rebuildFamilyCards(input ? input.value : 2);
            } else {
                row.style.display = 'none';
                if (input) input.value = 1;
                if (grpIn) grpIn.value = '';
                if (grpDis) grpDis.textContent = '';
                if (famIn) famIn.value = 1;
            }
        }

        const sharedGroupRecords = <?= json_encode($sharedGroups ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

        function selectSharedGroup(groupCode) {
            const groupInput = document.getElementById('sharedGroupInput');
            const groupDisplay = document.getElementById('sharedGroupDisplay');
            const familySelect = document.getElementById('familyNumberSelect');
            const familyInput = document.getElementById('familyNumberInput');
            const recordsBox = document.getElementById('existingFamilyRecords');
            const records = sharedGroupRecords.filter(record => record.shared_address_group === groupCode);

            if (!groupCode) {
                const newCode = genGroupCode();
                groupInput.value = newCode;
                groupDisplay.textContent = newCode;
                Array.from(familySelect.options).forEach(option => {
                    option.disabled = false;
                    option.textContent = 'Family ' + option.value;
                });
                familySelect.value = '1';
                familyInput.value = '1';
                recordsBox.style.display = 'none';
                recordsBox.innerHTML = '';
                return;
            }

            groupInput.value = groupCode;
            groupDisplay.textContent = groupCode;
            const occupied = records.map(record => String(record.family_number));
            Array.from(familySelect.options).forEach(option => {
                option.disabled = occupied.includes(option.value);
                option.textContent = 'Family ' + option.value + (option.disabled ? ' (already registered)' : '');
            });
            const available = Array.from(familySelect.options).find(option => !option.disabled);
            familySelect.value = available ? available.value : '1';
            familyInput.value = familySelect.value;
            recordsBox.style.display = 'block';
            recordsBox.innerHTML = '<div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:6px;">Registered families in this group</div>' +
                records.map(record => '<div style="background:#fff;border:1px solid #dde2f5;border-radius:8px;padding:8px 10px;margin-bottom:6px;font-size:11.5px;color:#4a5068;"><strong>Family ' + record.family_number + '</strong> — ' + escapeHtml(record.first_name + ' ' + record.last_name) + ' <span style="color:#9aa0b4;">(Household No. ' + escapeHtml(record.household_no) + ')</span></div>').join('');
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>'"]/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            } [character]));
        }

        // ── PWD type toggle ───────────────────────────────────────────────────
        // Works for both radio buttons (value="1"/"0") and checkboxes (checked)
        function togglePwdType(input, wrapId, isCheckbox) {
            const wrap = document.getElementById(wrapId);
            if (!wrap) return;
            const show = isCheckbox ? input.checked : (input.value === '1');
            if (show) {
                wrap.style.display = 'block';
                const inp = wrap.querySelector('input');
                if (inp) inp.focus();
                const fileEl = wrap.querySelector('input[type="file"]');
                if (fileEl) fileEl.required = true;
            } else {
                wrap.style.display = 'none';
                const inp = wrap.querySelector('input');
                if (inp) inp.value = '';
                const fileEl = wrap.querySelector('input[type="file"]');
                if (fileEl) {
                    fileEl.required = false;
                    fileEl.value = '';
                }
            }
        }

        // ── ID upload visibility toggle ───────────────────────────────────────
        function toggleIdUpload(inputId, wrapId, mode) {
            const wrap = document.getElementById(wrapId);
            if (!wrap) return;
            let show;
            if (mode === 'checkbox' || mode === undefined) {
                const cb = document.getElementById(inputId);
                show = cb && cb.checked;
            } else {
                const rb = document.getElementById(inputId);
                show = rb && rb.value === '1';
            }
            wrap.style.display = show ? 'block' : 'none';
            // Clear the file if hiding
            if (!show) {
                const fileEl = wrap.querySelector('input[type="file"]');
                if (fileEl) fileEl.value = '';
                const nameEl = wrap.querySelector('.pf-file-name');
                if (nameEl) nameEl.textContent = 'No file selected';
            }
        }

        // Show the selected document name inside its upload card.
        document.addEventListener('change', function(e) {
            if (!e.target.matches('.pf-file')) return;
            const name = e.target.files && e.target.files[0] ? e.target.files[0].name : 'No file selected';
            const nameEl = e.target.closest('.pf-file-drop')?.querySelector('.pf-file-name');
            if (nameEl) nameEl.textContent = name;

            // Any change to an ID upload invalidates the previous OCR result.
            const block = e.target.closest('[data-id-block]');
            if (block) {
                resetOcrStatus(block);
                maybeRunOcr(block);
            }
        });

        // ── OCR-backed ID verification ────────────────────────────────────────
        function ocrHeadName() {
            const first = (document.querySelector('input[name="first_name"]')?.value || '').trim();
            const middle = (document.querySelector('input[name="middle_name"]')?.value || '').trim();
            const last = (document.querySelector('input[name="last_name"]')?.value || '').trim();
            return [first, middle, last].filter(Boolean).join(' ');
        }

        function ocrHeadDob() {
            return (document.querySelector('input[name="date_of_birth"]')?.value || '').trim();
        }

        function setOcrStatus(block, state, message) {
            const statusEl = block.querySelector('[data-ocr-status]');
            if (!statusEl) return;
            statusEl.classList.remove('is-pending', 'is-ok', 'is-fail');
            if (state) statusEl.classList.add(state);
            const icon = state === 'is-ok'
                ? '<i class="fas fa-check-circle"></i>'
                : state === 'is-fail'
                    ? '<i class="fas fa-exclamation-circle"></i>'
                    : state === 'is-pending'
                        ? '<i class="fas fa-spinner fa-spin"></i>'
                        : '<i class="fas fa-fingerprint"></i>';
            statusEl.innerHTML = icon + ' <span>' + message + '</span>';
        }

        function resetOcrStatus(block) {
            const flag = block.querySelector('[data-ocr-flag]');
            if (flag) flag.value = '0';
            setOcrStatus(block, null, 'Fill in the name and date of birth, then upload the front and back of the ID.');
        }

        async function runOcr(block) {
            const front = block.querySelector('[data-ocr-front]');
            const back = block.querySelector('[data-ocr-back]');
            const runBtn = block.querySelector('[data-ocr-run]');
            const flag = block.querySelector('[data-ocr-flag]');
            const name = ocrHeadName();
            const dob = ocrHeadDob();

            if (!front || !front.files || !front.files[0]) {
                setOcrStatus(block, 'is-fail', 'Pick the front photo of the ID first.');
                return;
            }
            if (!name) {
                setOcrStatus(block, 'is-fail', 'Type the full name in personal information first.');
                return;
            }

            const form = new FormData();
            form.append('id_front', front.files[0]);
            if (back && back.files && back.files[0]) {
                form.append('id_back', back.files[0]);
            }
            form.append('full_name', name);
            if (dob) form.append('date_of_birth', dob);

            setOcrStatus(block, 'is-pending', 'Reading the ID with OCR…');
            if (runBtn) runBtn.disabled = true;

            // The OCR route lives under the current role prefix so the session
            // cookie for that role is picked up automatically.
            const rolePrefix = (location.pathname.match(/^\/(secretary|captain|council)(?=\/)/) || [null, 'secretary'])[1];
            const ocrUrl = '/' + rolePrefix + '/ocr/verify-id';

            try {
                const response = await fetch(ocrUrl, { method: 'POST', body: form, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const raw = await response.text();
                let data = null;
                try { data = raw ? JSON.parse(raw) : null; } catch (_) { data = null; }
                if (!response.ok || !data || data.ok === false) {
                    let message;
                    if (data && data.error) {
                        message = data.error;
                    } else if (raw) {
                        // Prefer the CI4 error page title if we got HTML back.
                        const titleMatch = raw.match(/<title[^>]*>([^<]{1,200})<\/title>/i);
                        const h1Match = raw.match(/<h1[^>]*>([\s\S]{1,240}?)<\/h1>/i);
                        const bodyMatch = raw.match(/<p[^>]*>([\s\S]{5,240}?)<\/p>/i);
                        const summary = (titleMatch && titleMatch[1].trim())
                            || (h1Match && h1Match[1].replace(/<[^>]+>/g, ' ').trim())
                            || (bodyMatch && bodyMatch[1].replace(/<[^>]+>/g, ' ').trim())
                            || raw.replace(/<script[\s\S]*?<\/script>/gi, ' ').replace(/<style[\s\S]*?<\/style>/gi, ' ').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 240);
                        message = 'OCR service returned HTTP ' + response.status + '. ' + summary;
                    } else {
                        message = 'OCR service returned HTTP ' + response.status + ' with an empty body.';
                    }
                    if (flag) flag.value = '0';
                    setOcrStatus(block, 'is-fail', message);
                    return;
                }
                if (flag) flag.value = data.verified ? '1' : '0';
                setOcrStatus(block, data.verified ? 'is-ok' : 'is-fail', data.reason || (data.verified ? 'Verified.' : 'Could not verify.'));
            } catch (err) {
                if (flag) flag.value = '0';
                setOcrStatus(block, 'is-fail', 'Network error while verifying the ID: ' + (err && err.message ? err.message : err));
            } finally {
                if (runBtn) runBtn.disabled = false;
            }
        }

        function maybeRunOcr(block) {
            const front = block.querySelector('[data-ocr-front]');
            const back = block.querySelector('[data-ocr-back]');
            const hasFront = front && front.files && front.files[0];
            const hasBack = back && back.files && back.files[0];
            const name = ocrHeadName();
            if (hasFront && hasBack && name) {
                runOcr(block);
            }
        }

        document.addEventListener('click', function(e) {
            const runBtn = e.target.closest('[data-ocr-run]');
            if (!runBtn) return;
            e.preventDefault();
            const block = runBtn.closest('[data-id-block]');
            if (block) runOcr(block);
        });

        // Re-verify when the head name or DOB changes and a front image is already picked.
        ['first_name', 'middle_name', 'last_name', 'date_of_birth'].forEach(function(field) {
            const el = document.querySelector('input[name="' + field + '"]');
            if (!el) return;
            el.addEventListener('change', function() {
                document.querySelectorAll('[data-id-block]').forEach(function(block) {
                    resetOcrStatus(block);
                });
            });
        });

        // ── Init ──────────────────────────────────────────────────────────────
        (function() {
            // Auto-generate household number
            document.getElementById('householdNo').value =
                String(Math.floor(10000 + Math.random() * 90000));
            // Auto-set today as recorded date
            document.getElementById('recordedDate').value =
                new Date().toISOString().split('T')[0];
        })();

        // ── Step navigation ───────────────────────────────────────────────────
        let currentStep = 1;

        function goTo(step) {
            currentStep = step;
            document.querySelectorAll('.pf-step').forEach((el, i) =>
                el.classList.toggle('active', i + 1 === step)
            );
            document.getElementById('tab1').classList.toggle('active', step === 1);
            document.getElementById('tab2').classList.toggle('active', step === 2);
            document.getElementById('prevBtn').style.display = step > 1 ? '' : 'none';
            document.getElementById('nextBtn').style.display = step < 2 ? '' : 'none';
            document.getElementById('saveBtn').style.display = step === 2 ? '' : 'none';
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function goNext() {
            if (currentStep < 2) {
                if (!validateStep(1)) return; // block navigation if step 1 has errors
                goTo(currentStep + 1);
            }
        }

        function goPrev() {
            if (currentStep > 1) goTo(currentStep - 1);
        }

        // ── Dynamic rows ──────────────────────────────────────────────────────
        let _childIdx = 1;
        let _otherIdx = 1;

        function gradeOptions() {
            return `<option value="">— Select Grade / Year —</option>
                <optgroup label="Elementary"><option>Grade 1</option><option>Grade 2</option><option>Grade 3</option><option>Grade 4</option><option>Grade 5</option><option>Grade 6</option></optgroup>
                <optgroup label="Junior High School"><option>Grade 7</option><option>Grade 8</option><option>Grade 9</option><option>Grade 10</option></optgroup>
                <optgroup label="Senior High School"><option>Grade 11</option><option>Grade 12</option></optgroup>
                <optgroup label="College / University"><option>1st Year College</option><option>2nd Year College</option><option>3rd Year College</option><option>4th Year College</option><option>5th Year College</option></optgroup>
                <optgroup label="Vocational / Technical"><option>1st Year Tech-Voc</option><option>2nd Year Tech-Voc</option></optgroup>`;
        }

        function addChildRow() {
            const i = _childIdx++;
            document.getElementById('childrenRows').insertAdjacentHTML('beforeend', `
                <div class="pf-member-card">
                    <div class="pf-member-fields">
                        <div class="pf-field-wrap"><div class="pf-label">Last Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="child_last_name[]" placeholder="LAST NAME"></div>
                        <div class="pf-field-wrap"><div class="pf-label">First Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="child_first_name[]" placeholder="FIRST NAME"></div>
                        <div class="pf-field-wrap"><div class="pf-label">Middle Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="child_middle_name[]" placeholder="MIDDLE NAME"></div>
                        <div class="pf-field-xs"><div class="pf-label">Suffix</div><select class="pf-ctrl" name="child_suffix[]"><option value="">—NONE—</option><option>Jr</option><option>Sr</option><option>II</option><option>III</option></select></div>
                        <div class="pf-field-sm"><div class="pf-label">Date of Birth</div><div class="pf-date-wrap"><input type="date" class="pf-ctrl" name="child_dob[]"><i class="fas fa-calendar-alt pf-date-icon"></i></div></div>
                        <div class="pf-field-xs"><div class="pf-label">Gender</div><select class="pf-ctrl" name="child_gender[]"><option value="">—Select—</option><option>Male</option><option>Female</option></select></div>
                        <div class="pf-field-sm"><div class="pf-label">Civil Status</div><input type="hidden" name="child_marital_status[]" value="Single"><label class="pf-married-toggle"><input type="checkbox" onchange="toggleChildMarried(this)"><span>Married</span></label></div>
                        <div class="pf-field-wrap"><div class="pf-label">Occupation</div>
                            <div class="pf-occ-wrap">
                                <div class="pf-occ-pills">
                                    <button type="button" class="pf-occ-pill" data-val="STUDENT" onclick="setOcc(this,'STUDENT')">Student</button>
                                    <button type="button" class="pf-occ-pill" data-val="WORKING STUDENT" onclick="setOcc(this,'WORKING STUDENT')">Working Student</button>
                                    <button type="button" class="pf-occ-pill" data-val="OUT OF SCHOOL" onclick="setOcc(this,'OUT OF SCHOOL')">Out of School</button>
                                </div>
                                <input type="text" class="pf-ctrl pf-upper pf-alpha" name="child_occupation[]" placeholder="OR TYPE EXACT OCCUPATION">
                                <div class="pf-grade-wrap"><div class="pf-grade-label">Current Grade / Year</div><select class="pf-ctrl" name="child_grade[]">${gradeOptions()}</select></div>
                            </div>
                        </div>
                        <div class="pf-field-sm"><div class="pf-label">Monthly Income (₱)</div><input type="number" class="pf-ctrl" name="child_income[]" placeholder="0.00" min="0" step="0.01"></div>
                        <div class="pf-field-sm"><div class="pf-label">PhilHealth No.</div><input type="text" class="pf-ctrl pf-philhealth" name="child_philhealth[]" placeholder="00000000000" maxlength="12" inputmode="numeric"></div>
                        <div class="pf-field-sm"><div class="pf-label">Voter?</div>
                            <div style="display:flex;gap:12px;padding:7px 0;">
                                <label class="pf-radio"><input type="radio" name="child_voter[${i}]" value="1"> <span>Yes</span></label>
                                <label class="pf-radio"><input type="radio" name="child_voter[${i}]" value="0" checked> <span>No</span></label>
                            </div>
                        </div>
                        <div class="pf-field-sm pf-child-pwd-field"><div class="pf-label">PWD?</div>
                        <div style="display:flex;gap:12px;padding:7px 0;">
                            <label class="pf-radio"><input type="radio" name="child_pwd[${i}]" value="1" onchange="togglePwdType(this,'child_pwd_type_wrap_${i}')"> <span>Yes</span></label>
                            <label class="pf-radio"><input type="radio" name="child_pwd[${i}]" value="0" checked onchange="togglePwdType(this,'child_pwd_type_wrap_${i}')"> <span>No</span></label>
                        </div>
                        <div id="child_pwd_type_wrap_${i}" style="display:none;margin-top:6px;">
                            <input type="text" class="pf-ctrl" name="child_pwd_type[]" placeholder="Specify disability (e.g. Visual, Hearing...)" maxlength="120">
                            <div class="pf-id-upload" style="margin-top:10px;">
                                <label class="pf-id-label"><i class="fas fa-id-card"></i> PWD Photo / ID <span class="pf-req">* REQUIRED</span></label>
                                <label class="pf-file-drop">
                                    <span class="pf-file-drop-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                    <span class="pf-file-drop-copy"><span class="pf-file-drop-title">Choose a document to upload</span><span class="pf-file-name">No file selected</span></span>
                                    <input type="file" name="child_id_pwd[]" accept="image/*,application/pdf" class="pf-file pf-file-input">
                                </label>
                                <div class="pf-file-hint"><i class="fas fa-info-circle"></i> PDF, JPG, or PNG - maximum 5 MB</div>
                            </div>
                        </div>
                </div>
                    </div>
                    <button type="button" class="pf-member-del" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                </div>`);
        }

        function addOtherRow() {
            const i = _otherIdx++;
            document.getElementById('otherRows').insertAdjacentHTML('beforeend', `
                <div class="pf-member-card">
                    <div class="pf-member-fields">
                        <div class="pf-field-wrap"><div class="pf-label">Last Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="other_last_name[]" placeholder="LAST NAME"></div>
                        <div class="pf-field-wrap"><div class="pf-label">First Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="other_first_name[]" placeholder="FIRST NAME"></div>
                        <div class="pf-field-wrap"><div class="pf-label">Middle Name</div><input type="text" class="pf-ctrl pf-upper pf-alpha" name="other_middle_name[]" placeholder="MIDDLE NAME"></div>
                        <div class="pf-field-xs"><div class="pf-label">Suffix</div><select class="pf-ctrl" name="other_suffix[]"><option value="">—NONE—</option><option>Jr</option><option>Sr</option><option>II</option><option>III</option></select></div>
                        <div class="pf-field-sm"><div class="pf-label">Date of Birth</div><div class="pf-date-wrap"><input type="date" class="pf-ctrl" name="other_dob[]"><i class="fas fa-calendar-alt pf-date-icon"></i></div></div>
                        <div class="pf-field-xs"><div class="pf-label">Gender</div><select class="pf-ctrl" name="other_gender[]"><option value="">—Select—</option><option>Male</option><option>Female</option></select></div>
                        <div class="pf-field-sm"><div class="pf-label">Relationship</div>
                            <select class="pf-ctrl" name="other_relationship[]">
                                <option value="">— Select —</option>
                                <option>Father</option><option>Mother</option><option>Sibling</option>
                                <option>Grandparent</option><option>Grandchild</option><option>Aunt/Uncle</option>
                                <option>Cousin</option><option>Other Relative</option><option>Non-relative</option>
                            </select>
                        </div>
                        <div class="pf-field-sm"><div class="pf-label">Voter?</div>
                            <div style="display:flex;gap:12px;padding:7px 0;">
                                <label class="pf-radio"><input type="radio" name="other_voter[${i}]" value="1"> <span>Yes</span></label>
                                <label class="pf-radio"><input type="radio" name="other_voter[${i}]" value="0" checked> <span>No</span></label>
                            </div>
                        </div>
                        <div class="pf-field-sm"><div class="pf-label">PWD?</div>
                    <div style="display:flex;gap:12px;padding:7px 0;">
                        <label class="pf-radio"><input type="radio" name="other_pwd[${i}]" value="1" onchange="togglePwdType(this,'other_pwd_type_wrap_${i}')"> <span>Yes</span></label>
                        <label class="pf-radio"><input type="radio" name="other_pwd[${i}]" value="0" checked onchange="togglePwdType(this,'other_pwd_type_wrap_${i}')"> <span>No</span></label>
                    </div>
                    <div id="other_pwd_type_wrap_${i}" style="display:none;margin-top:6px;">
                        <input type="text" class="pf-ctrl" name="other_pwd_type[]" placeholder="Specify disability (e.g. Visual, Hearing...)" maxlength="120">
                    </div>
                </div>
                    </div>
                    <button type="button" class="pf-member-del" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                </div>`);
        }

        function removeRow(btn) {
            btn.closest('.pf-member-card').remove();
        }

        function toggleChildMarried(checkbox) {
            const row = checkbox.closest('.pf-member-card');
            const status = row ? row.querySelector('input[name="child_marital_status[]"]') : null;
            const dob = row ? row.querySelector('input[name="child_dob[]"]') : null;
            if (dob && isMinorDate(dob.value)) {
                checkbox.checked = false;
                if (status) status.value = 'Single';
                showMinorCivilStatusError(row);
                return;
            }
            if (status) status.value = checkbox.checked ? 'Married' : 'Single';
        }

        function isMinorDate(value) {
            if (!value) return false;
            const birthDate = new Date(value + 'T00:00:00');
            if (Number.isNaN(birthDate.getTime())) return false;
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const birthdayPassed = today.getMonth() > birthDate.getMonth() ||
                (today.getMonth() === birthDate.getMonth() && today.getDate() >= birthDate.getDate());
            if (!birthdayPassed) age--;
            return age < 18;
        }

        function showMinorCivilStatusError(row) {
            if (!row || row.querySelector('.minor-civil-status-error')) return;
            const error = document.createElement('small');
            error.className = 'minor-civil-status-error';
            error.style.cssText = 'display:block;color:#c0392b;font-size:11px;margin-top:4px;';
            error.textContent = 'Under 18: Please confirm the Civil Status?';
            const statusField = row.querySelector('input[name="child_marital_status[]"]');
            if (statusField?.parentElement) statusField.parentElement.appendChild(error);
        }

        function syncMinorChildCivilStatus(row) {
            const dob = row?.querySelector('input[name="child_dob[]"]');
            const checkbox = row?.querySelector('.pf-married-toggle input[type="checkbox"]');
            const status = row?.querySelector('input[name="child_marital_status[]"]');
            if (!dob || !checkbox || !status) return true;

            const minor = isMinorDate(dob.value);
            if (minor) {
                checkbox.checked = false;
                checkbox.disabled = true;
                status.value = 'Single';
                showMinorCivilStatusError(row);
            } else {
                checkbox.disabled = false;
                const error = row.querySelector('.minor-civil-status-error');
                if (error) error.remove();
            }
            return !(minor && status.value !== 'Single');
        }

        document.addEventListener('change', function(e) {
            if (e.target.name === 'child_dob[]') {
                syncMinorChildCivilStatus(e.target.closest('.pf-member-card'));
            }
        });

        // ── Occupation quick-pick ─────────────────────────────────────────────
        function setOcc(btn, value) {
            const wrap = btn.closest('.pf-occ-wrap');
            const input = wrap.querySelector('input[name="child_occupation[]"]');
            const pills = wrap.querySelectorAll('.pf-occ-pill');
            const alreadyActive = btn.classList.contains('active');
            pills.forEach(p => p.classList.remove('active'));
            if (alreadyActive) {
                input.value = '';
            } else {
                btn.classList.add('active');
                input.value = value;
            }
            toggleGradeField(wrap, input.value);
        }

        function toggleGradeField(wrap, val) {
            const gw = wrap.querySelector('.pf-grade-wrap');
            if (gw) gw.classList.toggle('visible', val.trim().toUpperCase() === 'STUDENT');
        }

        // ── Solo Parent validation + highlight ───────────────────────────────
        document.addEventListener('change', function(e) {
            if (e.target.name === 'is_solo_parent') {
                const bar = document.querySelector('#childrenSection .pf-page-section-bar');
                if (e.target.checked) {
                    bar.style.background = 'linear-gradient(90deg,#c0392b,#e74c3c)';
                } else {
                    bar.style.background = '';
                    const err = document.getElementById('soloParentError');
                    if (err) err.remove();
                }
            }
        });

        // ── Field validation helpers ──────────────────────────────────────────

        /**
         * Mark a field as invalid: red border + "This field is required" tooltip badge.
         * The badge appears top-right of the label, matching the reference design.
         */
        function setFieldError(fieldEl, labelEl) {
            fieldEl.classList.add('pf-error');

            // Red label
            if (labelEl) labelEl.style.color = '#c0392b';

            // Create badge if not already there
            const wrappedLabel = fieldEl.closest('[data-field-group]') || fieldEl.parentElement;
            const existingBadge = wrappedLabel.querySelector('.pf-req-badge');
            if (existingBadge) return;

            const badge = document.createElement('span');
            badge.className = 'pf-req-badge';
            badge.innerHTML = '<i class="fas fa-exclamation-circle"></i> This field is required';
            badge.style.cssText =
                'display:inline-flex;align-items:center;gap:5px;' +
                'background:#c0392b;color:#fff;font-size:11px;font-weight:700;' +
                'padding:4px 9px;border-radius:6px;white-space:nowrap;' +
                'margin-left:auto;float:right;';

            if (labelEl) {
                labelEl.style.display = 'flex';
                labelEl.style.alignItems = 'center';
                labelEl.style.justifyContent = 'space-between';
                labelEl.appendChild(badge);
            }

            // Clear error on input/change
            fieldEl.addEventListener('input', () => clearFieldError(fieldEl, labelEl), {
                once: true
            });
            fieldEl.addEventListener('change', () => clearFieldError(fieldEl, labelEl), {
                once: true
            });
        }

        function clearFieldError(fieldEl, labelEl) {
            fieldEl.classList.remove('pf-error');
            fieldEl.style.borderColor = '';
            fieldEl.style.boxShadow = '';
            if (labelEl) {
                labelEl.style.color = '';
                labelEl.style.display = '';
                labelEl.style.alignItems = '';
                labelEl.style.justifyContent = '';
                const badge = labelEl.querySelector('.pf-req-badge');
                if (badge) badge.remove();
            }
        }

        /**
         * Validate all required fields in step 1.
         * Returns true if valid, false if any errors found.
         */
        function validateStep(step) {
            const banner = document.getElementById('formErrorBanner');
            const bannerT = document.getElementById('formErrorText');

            if (step === 1) {
                // Fields: [name, label-selector]
                const required = [
                    ['last_name', 'Last Name'],
                    ['first_name', 'First Name'],
                    ['date_of_birth', 'Date of Birth'],
                    ['place_of_birth', 'Place of Birth'],
                    ['occupation', 'Occupation'],
                    ['years_of_residency', 'Years of Residency'],
                ];

                let firstError = null;
                let errorCount = 0;

                required.forEach(([name, labelText]) => {
                    const field = document.querySelector(`#censusForm [name="${name}"]`);
                    if (!field) return;

                    // Find the label (.pf-label) that's the previous sibling or within same parent
                    const parent = field.closest('div');
                    const label = parent ? parent.querySelector('.pf-label') : null;

                    const empty = field.value.trim() === '' || field.value === '0';
                    if (empty) {
                        setFieldError(field, label);
                        if (!firstError) firstError = field;
                        errorCount++;
                    } else {
                        clearFieldError(field, label);
                    }
                });

                if (errorCount > 0) {
                    bannerT.textContent = errorCount === 1 ?
                        'Please fill in the required field marked above.' :
                        `${errorCount} required field${errorCount > 1 ? 's are' : ' is'} missing. Please complete them before continuing.`;
                    banner.style.display = 'flex';

                    // Scroll to the first invalid field
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        setTimeout(() => firstError.focus(), 400);
                    }
                    return false;
                }
            }

            // Hide banner on success
            if (banner) banner.style.display = 'none';
            return true;
        }

        // ── Form submit validation ────────────────────────────────────────────
        document.getElementById('censusForm').addEventListener('submit', function(e) {
            // Validate step 1 fields before final submission
            if (!validateStep(1)) {
                e.preventDefault();
                goTo(1); // Switch back to step 1 if user was on step 2
                return;
            }

            let minorCivilStatusError = false;
            document.querySelectorAll('#childrenRows .pf-member-card').forEach(row => {
                if (!syncMinorChildCivilStatus(row)) minorCivilStatusError = true;
            });
            if (minorCivilStatusError) {
                e.preventDefault();
                goTo(2);
                alert('A child under 18 must have Civil Status set to Single.');
                return;
            }

            // ── ID upload validation: required for 4PS, Solo Parent, and PWD ─
            const idChecks = [{
                    cb: 'input[name="is_4ps"]',
                    file: 'input[name="id_4ps"]',
                    wrap: 'id_4ps_wrap',
                    label: '4Ps Beneficiary ID'
                },
                {
                    cb: 'input[name="is_solo_parent"]',
                    file: 'input[name="id_solo_parent"]',
                    wrap: 'id_solo_wrap',
                    label: 'Solo Parent ID'
                },
                {
                    cb: 'input[name="is_pwd"]',
                    file: 'input[name="id_pwd"]',
                    wrap: 'id_pwd_wrap',
                    label: 'PWD ID'
                },
            ];
            let idErrors = [];
            let firstMissingWrap = null;
            idChecks.forEach(cfg => {
                const cbEl = document.querySelector(cfg.cb);
                if (cbEl && cbEl.checked) {
                    const fileEl = document.querySelector(cfg.file);
                    const hasFile = fileEl && fileEl.files && fileEl.files.length > 0;
                    if (!hasFile) {
                        idErrors.push(cfg.label);
                        const wrapEl = document.getElementById(cfg.wrap);
                        if (wrapEl) {
                            wrapEl.style.outline = '2px solid #c0392b';
                            wrapEl.style.borderRadius = '8px';
                            setTimeout(() => {
                                wrapEl.style.outline = '';
                            }, 3000);
                            if (!firstMissingWrap) firstMissingWrap = wrapEl;
                        }
                    }
                }
            });
            if (idErrors.length > 0) {
                e.preventDefault();
                goTo(1);
                if (firstMissingWrap) {
                    firstMissingWrap.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
                const msg = idErrors.length === 1 ?
                    `<strong>${idErrors[0]}</strong> upload is required.` :
                    `Please upload the following required IDs: <strong>${idErrors.join(', ')}</strong>.`;
                let err = document.getElementById('idUploadsError');
                if (!err) {
                    err = document.createElement('div');
                    err.id = 'idUploadsError';
                    err.style.cssText = 'background:#fff0f1;border:1px solid #fad4d4;border-radius:8px;padding:10px 14px;font-size:12.5px;color:#c0392b;display:flex;align-items:center;gap:8px;margin-bottom:14px;';
                    err.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>' + msg + '</span>';
                    document.getElementById('censusForm').insertAdjacentElement('afterbegin', err);
                } else {
                    err.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>' + msg + '</span>';
                }
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                return;
            }
            const prevErr = document.getElementById('idUploadsError');
            if (prevErr) prevErr.remove();

            // Solo Parent requires at least one child
            const isSoloParent = document.querySelector('input[name="is_solo_parent"]')?.checked;
            if (isSoloParent) {
                const childRows = document.querySelectorAll('#childrenRows .pf-member-card');
                const hasChild = Array.from(childRows).some(row => {
                    const ln = row.querySelector('input[name="child_last_name[]"]');
                    return ln && ln.value.trim() !== '';
                });
                if (!hasChild) {
                    e.preventDefault();
                    goTo(2);
                    const section = document.getElementById('childrenSection');
                    section.style.outline = '2px solid #c0392b';
                    section.style.borderRadius = '14px';
                    setTimeout(() => {
                        section.style.outline = '';
                    }, 3000);
                    section.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    let err = document.getElementById('soloParentError');
                    if (!err) {
                        err = document.createElement('div');
                        err.id = 'soloParentError';
                        err.style.cssText = 'background:#fff0f1;border:1px solid #fad4d4;border-radius:8px;padding:10px 14px;font-size:12.5px;color:#c0392b;display:flex;align-items:center;gap:8px;margin-bottom:14px;';
                        err.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>A <strong>Solo Parent</strong> record requires at least one child. Please add the child\'s information.</span>';
                        document.getElementById('childrenRows').insertAdjacentElement('beforebegin', err);
                    }
                    return;
                }
            }

            const err = document.getElementById('soloParentError');
            if (err) err.remove();

            // Force uppercase on text inputs
            document.querySelectorAll('#censusForm input[type="text"]').forEach(f => {
                f.value = f.value.toUpperCase();
            });
        });

        // ── Live input: uppercase, philhealth digits, alpha-only ─────────────
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('pf-upper')) {
                const pos = e.target.selectionStart;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(pos, pos);
            }
            if (e.target.classList.contains('pf-philhealth')) {
                e.target.value = e.target.value.replace(/\D/g, '');
            }
            if (e.target.classList.contains('pf-alpha')) {
                const pos = e.target.selectionStart;
                e.target.value = e.target.value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ\s\-'.]/g, '');
                e.target.setSelectionRange(pos, pos);
            }
            if (e.target.name === 'child_occupation[]') {
                const wrap = e.target.closest('.pf-occ-wrap');
                if (!wrap) return;
                const val = e.target.value.trim().toUpperCase();
                wrap.querySelectorAll('.pf-occ-pill').forEach(p => {
                    p.classList.toggle('active', p.dataset.val === val);
                });
                toggleGradeField(wrap, val);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('pf-philhealth')) {
                const allowed = [8, 9, 13, 27, 46, 37, 38, 39, 40, 35, 36];
                if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].includes(e.keyCode)) return;
                if (allowed.includes(e.keyCode)) return;
                if (e.key < '0' || e.key > '9') e.preventDefault();
            }
            if (e.target.classList.contains('pf-alpha')) {
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                const allowed = [8, 9, 13, 27, 32, 37, 38, 39, 40, 35, 36, 45, 46];
                if (allowed.includes(e.keyCode)) return;
                if (e.key >= '0' && e.key <= '9') e.preventDefault();
            }
        });

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );
    </script>
    <script>
        function markRequiredFields() {
            document.querySelectorAll('[required]').forEach(field => {
                let parent = field.parentElement;
                for (let level = 0; level < 4 && parent; level++, parent = parent.parentElement) {
                    const label = parent.querySelector('.pf-label, .pf-field-label');
                    if (!label) continue;
                    if (!label.querySelector('[data-required-marker]')) {
                        const marker = document.createElement('span');
                        marker.dataset.requiredMarker = 'true';
                        marker.textContent = ' *';
                        marker.style.cssText = 'color:#c0392b;font-weight:700;';
                        label.appendChild(marker);
                    }
                    break;
                }
            });
        }

        markRequiredFields();
        new MutationObserver(markRequiredFields).observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
</body>

</html>