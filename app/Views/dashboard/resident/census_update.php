<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census Update Authorization</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        body {
            background: #f4f7fb;
        }

        .census-update-shell {
            width: 100%;
            max-width: none;
            margin: 24px 0;
            padding: 0 24px 40px;
            box-sizing: border-box;
        }

        .census-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(29, 36, 72, .08);
            border: 1px solid #eaedf7;
            overflow: hidden;
        }

        .census-header {
            padding: 28px 28px 18px;
            border-bottom: 1px solid #edf1f7;
            background: linear-gradient(135deg, #eff4ff, #f9fbff);
        }

        .census-header h1 {
            margin: 0;
            font-size: 28px;
            color: #1d2448;
        }

        .census-header p {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .census-body {
            padding: 28px;
        }

        .census-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: #4a5068;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            border: 1.5px solid #dde4f0;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            background: #fff;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .census-actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .db-btn {
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .db-btn--primary {
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
        }

        .db-btn--outline {
            background: #fff;
            color: #1d2448;
            border: 1.5px solid #dfe5f2;
        }

        .census-note {
            margin-top: 18px;
            padding: 14px 16px;
            background: #fff8ee;
            border: 1px solid #f6d7a8;
            border-radius: 10px;
            color: #7a4200;
            font-size: 12.5px;
        }
    </style>
</head>

<body class="bis-dash">
    <div class="census-update-shell">
        <div class="census-card">
            <div class="census-header">
                <h1><i class="fas fa-user-edit"></i> Household Head Update Form</h1>
                <p>Please review and update the household details below. This form is intended for the household head and will be reviewed by the barangay secretary.</p>
            </div>
            <div class="census-body">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                        <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                        <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/resident/census-update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">

                    <?php $canEditHousehold = (bool) ($householdAccess['can_edit_household'] ?? false); ?>
                    <?php if (! $canEditHousehold): ?>
                        <div class="census-note" style="background:#eef5ff;border-color:#cfe0ff;color:#21446f; margin-bottom:18px;">
                            <strong>Access Notice:</strong> You are linked as a household member, not the household head. You may update your personal information below. Household-level changes must be submitted by the household head and approved by the secretary.
                        </div>
                    <?php endif; ?>

                    <div class="census-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="address">Household Address</label>
                            <textarea id="address" name="address" placeholder="House No./Street/Purok/Sitio, Zone, Barangay Bacolod" <?= $canEditHousehold ? '' : 'disabled' ?>><?= esc($household['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="household_no">Household Number</label>
                            <input id="household_no" type="text" value="<?= esc($household['household_no'] ?? '') ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="zone">Zone</label>
                            <input id="zone" type="text" value="<?= esc($household['zone'] ?? '') ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="civil_status">Civil Status</label>
                            <select id="civil_status" name="civil_status">
                                <?php foreach (['Single', 'Married', 'Widowed', 'Separated', 'Annulled', 'Live-in'] as $status): ?>
                                    <option value="<?= esc($status) ?>" <?= (($household['civil_status'] ?? '') === $status) ? 'selected' : '' ?>><?= esc($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="occupation">Occupation</label>
                            <input id="occupation" name="occupation" type="text" value="<?= esc($household['occupation'] ?? '') ?>" placeholder="Occupation">
                        </div>
                        <div class="form-group">
                            <label for="monthly_income">Monthly Income</label>
                            <input id="monthly_income" name="monthly_income" type="number" step="0.01" min="0" value="<?= esc($household['monthly_income'] ?? '0') ?>" placeholder="0.00">
                        </div>
                    </div>

                    <div class="census-note">
                        <strong>Note:</strong> This household update request is for household-head review and approval by the barangay secretary. Supporting documents may be required for address changes or household separation requests.
                    </div>

                    <div class="census-actions">
                        <a href="/resident/dashboard" class="db-btn db-btn--outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                        <button type="submit" class="db-btn db-btn--primary" <?= $canEditHousehold ? '' : 'disabled' ?>><i class="fas fa-paper-plane"></i> Submit for Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>