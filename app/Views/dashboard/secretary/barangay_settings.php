<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Fees - BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .bset-page-title h1 { margin: 0 0 6px; font-size: 22px; color: #1a1d2e; }
        .bset-page-title p { margin: 0; color: #6b7280; font-size: 13.5px; }
        .bset-card {
            background: #fff;
            border: 1px solid #e8ecf4;
            border-radius: 14px;
            padding: 22px 24px;
            margin-bottom: 18px;
        }
        .bset-card-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f0f2fa;
        }
        .bset-card-header .icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f0f2ff;
            color: #5b6fd6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .bset-card-header h3 { margin: 0; font-size: 15px; font-weight: 600; color: #1a1d2e; }
        .bset-card-header p { margin: 3px 0 0; font-size: 12px; color: #9aa0b4; }
        .bset-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px;
        }
        @media (max-width: 760px) {
            .bset-grid { grid-template-columns: 1fr; }
        }
        .bset-field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .45px;
            margin-bottom: 6px;
        }
        .bset-field input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13.5px;
            color: #1a1d2e;
            outline: none;
            background: #fff;
        }
        .bset-field input:focus { border-color: #5b6fd6; }
        .fee-table { width: 100%; border-collapse: collapse; }
        .fee-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 0 0 10px;
            border-bottom: 1px solid #eef1f7;
        }
        .fee-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f3f5fb;
            vertical-align: middle;
        }
        .fee-table tr:last-child td { border-bottom: none; }
        .fee-name { font-size: 13.5px; font-weight: 600; color: #1a1d2e; }
        .fee-input {
            width: 140px;
            padding: 8px 10px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13.5px;
            color: #1a1d2e;
        }
        .fee-preview {
            display: inline-flex;
            min-width: 84px;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: #eef4ff;
            color: #1d2448;
            font-size: 12px;
            font-weight: 700;
        }
        .fee-preview.is-free { background: #edfaf5; color: #0f7a62; }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = session()->get('role') === 'admin' ? 'admin' : 'secretary';
    $active    = 'barangay_settings';
    $pageTitle = 'Document Fees';
    $savedSettings = (new \App\Models\BarangaySettingsModel())->getAll();
    $captainName = official_display_name($savedSettings['captain_name'] ?? '');
    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <div class="bset-page-title" style="margin-bottom:20px;">
                <h1>Document Fees</h1>
                <p>Set the amounts that appear on the resident request form and on printed documents. Identity and official names stay on this page so clearance templates stay in sync.</p>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:18px;">
                    <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--danger" style="margin-bottom:18px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom:20px;">
                <a href="<?= site_url($role . '/clearance') ?>" class="db-btn db-btn--outline" style="display:inline-flex;align-items:center;gap:8px;">
                    <i class="fas fa-arrow-left"></i> Back to Templates
                </a>
            </div>

            <?php if (empty($settingsGrouped)): ?>
                <div class="db-alert db-alert--danger">
                    <i class="fas fa-database"></i>
                    The <strong>barangay_settings</strong> table does not exist yet.
                    Please run <code>php spark migrate</code> in the terminal first.
                </div>
            <?php else: ?>

                <?php if ($captainName !== ''): ?>
                <div style="background:#f0f2ff;border:1.5px solid #c9d0f5;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
                    <div style="width:38px;height:38px;border-radius:10px;background:#5b6fd6;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-user-tie" style="color:#fff;font-size:16px;"></i>
                    </div>
                    <div>
                        <div style="font-size:11.5px;font-weight:600;color:#5b6fd6;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">
                            Active Punong Barangay
                        </div>
                        <div style="font-size:15px;font-weight:700;color:#1a1d2e;">
                            <?= esc($captainName) ?>
                        </div>
                        <div style="font-size:12px;color:#6b7280;margin-top:2px;">
                            Automatically updated when a Captain account is appointed.
                            To change, go to
                            <a href="<?= site_url($role . '/create-account') ?>" style="color:#5b6fd6;">Create Official</a>.
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <form method="post" action="<?= site_url($role . '/barangay-settings/save') ?>">
                    <?= csrf_field() ?>

                    <?php
                    $groupMeta = [
                        'identity' => ['icon' => 'fa-landmark', 'title' => 'Barangay Identity', 'desc' => 'Name and location used on document letterheads. These are the same values used on the report front page.'],
                        'officials' => ['icon' => 'fa-user-tie', 'title' => 'Barangay Officials', 'desc' => 'Names printed on document signatures.'],
                        'fees' => ['icon' => 'fa-file-invoice-dollar', 'title' => 'Document Fees', 'desc' => 'Enter 100, ₱100.00, or Free. The preview on the right is what residents will see.'],
                    ];
                    $orderedGroups = ['fees', 'identity', 'officials'];
                    foreach ($orderedGroups as $group):
                        $rows = $settingsGrouped[$group] ?? [];
                        if ($rows === []) {
                            continue;
                        }
                        $meta = $groupMeta[$group];
                    ?>
                        <div class="bset-card">
                            <div class="bset-card-header">
                                <div class="icon"><i class="fas <?= $meta['icon'] ?>"></i></div>
                                <div>
                                    <h3><?= esc($meta['title']) ?></h3>
                                    <p><?= esc($meta['desc']) ?></p>
                                </div>
                            </div>

                            <?php if ($group === 'fees'): ?>
                                <table class="fee-table">
                                    <thead>
                                        <tr>
                                            <th style="width:48%;">Document</th>
                                            <th style="width:26%;">Amount</th>
                                            <th style="width:26%;">Shown to resident</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $row): ?>
                                            <tr>
                                                <td>
                                                    <div class="fee-name"><?= esc($row['label']) ?></div>
                                                </td>
                                                <td>
                                                    <input
                                                        class="fee-input js-fee-input"
                                                        type="text"
                                                        id="<?= esc($row['setting_key']) ?>"
                                                        name="<?= esc($row['setting_key']) ?>"
                                                        value="<?= esc($row['setting_value'] ?? '') ?>"
                                                        placeholder="Free or 50">
                                                </td>
                                                <td>
                                                    <span class="fee-preview" data-fee-preview><?= esc(\Config\ClearanceDocuments::displayFee((string) ($row['setting_value'] ?? ''))) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="bset-grid">
                                    <?php foreach ($rows as $row): ?>
                                        <div class="bset-field">
                                            <label for="<?= esc($row['setting_key']) ?>"><?= esc($row['label']) ?></label>
                                            <input
                                                type="text"
                                                id="<?= esc($row['setting_key']) ?>"
                                                name="<?= esc($row['setting_key']) ?>"
                                                value="<?= esc($row['setting_value'] ?? '') ?>"
                                                placeholder="<?= esc($row['label']) ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button type="submit" class="db-btn db-btn--primary" style="display:inline-flex;align-items:center;gap:8px;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="<?= site_url($role . '/clearance') ?>" class="db-btn db-btn--outline">Cancel</a>
                    </div>
                </form>

            <?php endif; ?>

        </div>
    </div>
    <script>
        function formatFeePreview(raw) {
            var value = String(raw || '').trim();
            if (value === '') return 'Free';
            var plain = value.replace(/[₱,\s]/g, '');
            if (plain.toLowerCase() === 'free') return 'Free';
            if (!isNaN(plain) && plain !== '') {
                var amount = parseFloat(plain);
                if (amount <= 0) return 'Free';
                return '₱' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            return value;
        }
        document.querySelectorAll('.js-fee-input').forEach(function (input) {
            var preview = input.closest('tr').querySelector('[data-fee-preview]');
            function refresh() {
                var text = formatFeePreview(input.value);
                preview.textContent = text;
                preview.classList.toggle('is-free', text === 'Free');
            }
            input.addEventListener('input', refresh);
            refresh();
        });
    </script>
</body>

</html>
