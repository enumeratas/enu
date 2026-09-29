<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deceased Accounts - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .da-card {
            background: #fff;
            border: 1px solid #e2e6ee;
            margin-bottom: 18px;
        }

        .da-card-head {
            padding: 14px 18px;
            border-bottom: 1px solid #e2e6ee;
            border-top: 3px solid #e0b32a;
        }

        .da-card-head h2 {
            margin: 0 0 4px;
            font-size: 16px;
            color: #1c2b45;
        }

        .da-card-head p {
            margin: 0;
            font-size: 12px;
            color: #6b7689;
        }

        .da-body {
            padding: 18px;
        }

        .da-choices {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .da-choice {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 14px;
            border: 1px solid #d7dce6;
            cursor: pointer;
        }

        .da-choice.is-on {
            border-color: #16325c;
            background: #f4f6fd;
        }

        .da-choice strong {
            display: block;
            font-size: 13px;
            color: #1c2b45;
        }

        .da-choice span {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #6b7689;
        }

        .da-empty {
            text-align: center;
            padding: 36px 16px;
            color: #6b7689;
        }

        @media (max-width: 700px) {
            .da-choices {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = $role ?? (session()->get('role') ?: 'secretary');
    $active    = 'deceased_accounts';
    $pageTitle = 'Deceased Accounts';
    $action    = $action ?? 'block';
    $accounts  = $accounts ?? [];
    include(APPPATH . 'Views/dashboard/sidebar.php');
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="da-card">
                <div class="da-card-head">
                    <h2>What happens to the login</h2>
                    <p>This rule runs automatically when a household head or member with a resident account is marked deceased.</p>
                </div>
                <form class="da-body" method="post" action="/<?= esc($role) ?>/deceased-accounts/policy">
                    <?= csrf_field() ?>
                    <div class="da-choices">
                        <label class="da-choice <?= $action === 'block' ? 'is-on' : '' ?>">
                            <input type="radio" name="deceased_account_action" value="block" <?= $action === 'block' ? 'checked' : '' ?>>
                            <div>
                                <strong>Block the login</strong>
                                <span>The resident can no longer sign in. The census record stays. Clearing the deceased mark restores the login.</span>
                            </div>
                        </label>
                        <label class="da-choice <?= $action === 'keep' ? 'is-on' : '' ?>">
                            <input type="radio" name="deceased_account_action" value="keep" <?= $action === 'keep' ? 'checked' : '' ?>>
                            <div>
                                <strong>Keep the login</strong>
                                <span>The census mark still applies, but the account stays as it is. Use this only if you will decide later on this page.</span>
                            </div>
                        </label>
                    </div>
                    <div style="margin-top:14px;display:flex;justify-content:flex-end;">
                        <button type="submit" class="db-btn db-btn--primary">
                            <i class="fas fa-save"></i> Save policy
                        </button>
                    </div>
                </form>
            </div>

            <div class="da-card">
                <div class="da-card-head">
                    <h2>Deceased residents with an account</h2>
                    <p>Override a single account here without changing the policy for future marks.</p>
                </div>
                <?php if ($accounts === []): ?>
                    <div class="da-empty">
                        <i class="fas fa-user-slash" style="font-size:32px;color:#c5cdd8;display:block;margin-bottom:10px;"></i>
                        No deceased resident currently has a matching website account.
                    </div>
                <?php else: ?>
                    <div class="db-table-wrap">
                        <table class="db-table">
                            <thead>
                                <tr>
                                    <th>Resident</th>
                                    <th>Household</th>
                                    <th>Account</th>
                                    <th>Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accounts as $row):
                                    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                                    $blocked = ($row['account_status'] ?? '') === 'deceased';
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($name) ?></strong>
                                            <div style="font-size:12px;color:#6b7689;margin-top:2px;">
                                                <?= esc(ucfirst(str_replace('_', ' ', (string) ($row['relationship'] ?? '')))) ?>
                                                <?= ! empty($row['year_of_death']) ? ' · ' . esc($row['year_of_death']) : '' ?>
                                            </div>
                                        </td>
                                        <td>#<?= esc($row['household_no']) ?></td>
                                        <td>
                                            <?= esc($row['username']) ?>
                                            <div style="font-size:12px;color:#6b7689;"><?= esc($row['email']) ?></div>
                                        </td>
                                        <td>
                                            <span class="dbadge <?= $blocked ? 'dbadge--rejected' : 'dbadge--approved' ?>">
                                                <?= $blocked ? 'Blocked' : esc(ucfirst((string) $row['account_status'])) ?>
                                            </span>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <?php if ($blocked): ?>
                                                <form method="post" action="/<?= esc($role) ?>/deceased-accounts/account/<?= (int) $row['user_id'] ?>" style="display:inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="db-btn db-btn--outline db-btn--sm">Restore login</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" action="/<?= esc($role) ?>/deceased-accounts/account/<?= (int) $row['user_id'] ?>" style="display:inline;"
                                                    onsubmit="return confirm('Block this resident from signing in?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="deceased">
                                                    <button type="submit" class="db-btn db-btn--outline db-btn--sm">Block login</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.da-choice').forEach(function (label) {
            label.addEventListener('click', function () {
                document.querySelectorAll('.da-choice').forEach(function (other) {
                    other.classList.toggle('is-on', other === label);
                });
            });
        });
    </script>
</body>

</html>
