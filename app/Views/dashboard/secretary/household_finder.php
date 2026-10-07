<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Household Finder - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body class="db-body">
<?php
$role = $role ?? 'secretary';
$active = 'household_finder';
$pageTitle = 'Household Finder';
$hideGlobalSearch = true;
$finderQuery = trim((string) ($query ?? ''));
$finderMessage = trim((string) ($message ?? ''));
$families = (isset($families) && is_array($families)) ? $families : [];
include(APPPATH . 'Views/dashboard/sidebar.php');
?>
<div class="db-main">
    <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
    <div class="db-content">
        <form method="get" action="/<?= esc($role) ?>/household-finder" style="max-width:520px;margin:12px auto 28px;">
            <label for="householdFinderQuery" style="display:block;font-size:13px;font-weight:700;color:#1a1d2e;margin-bottom:8px;">Household number</label>
            <div style="display:flex;gap:8px;">
                <input id="householdFinderQuery" class="pf-input" type="text" name="q" value="<?= esc($finderQuery) ?>" inputmode="numeric" autocomplete="off" placeholder="Search household number" style="flex:1;margin:0;">
                <button type="submit" class="db-btn db-btn--primary"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>

        <?php if ($finderMessage !== ''): ?>
            <div style="max-width:720px;margin:0 auto 18px;padding:12px 14px;background:#fef3f2;border:1px solid #fecdca;border-radius:10px;color:#b42318;font-size:13px;font-weight:600;">
                <i class="fas fa-exclamation-circle"></i> <?= esc($finderMessage) ?>
            </div>
        <?php endif; ?>

        <div style="max-width:860px;margin:0 auto;display:flex;flex-direction:column;gap:16px;">
            <?php foreach ($families as $family): ?>
                <?php $head = is_array($family['head'] ?? null) ? $family['head'] : []; ?>
                <section style="background:#fff;border:1px solid #dde2f5;border-radius:12px;padding:16px 18px;">
                    <div style="font-size:13px;font-weight:700;color:#3b5bdb;margin-bottom:10px;">
                        <i class="fas fa-user"></i> <?= esc((string) ($family['label'] ?? 'Head')) ?>
                    </div>
                    <?php
                    $headRows = [
                        'Name' => $head['name'] ?? '',
                        'Household No.' => $head['household_no'] ?? '',
                        'Zone / Purok' => $head['zone'] ?? '',
                        'Address' => $head['address'] ?? '',
                        'Date of Birth' => $head['date_of_birth'] ?? '',
                        'Gender' => $head['gender'] ?? '',
                        'Civil Status' => $head['civil_status'] ?? '',
                        'Contact' => $head['contact_number'] ?? '',
                        'House Ownership' => $head['house_ownership'] ?? '',
                    ];
                    ?>
                    <?php foreach ($headRows as $label => $value): ?>
                        <?php if (trim((string) $value) === '') continue; ?>
                        <div style="display:flex;gap:12px;font-size:13px;padding:3px 0;">
                            <span style="min-width:150px;color:#6b7289;"><?= esc($label) ?></span>
                            <span style="color:#1a1d2e;font-weight:600;"><?= esc((string) $value) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div style="font-size:13px;font-weight:700;color:#1a1d2e;margin:14px 0 8px;padding-top:10px;border-top:1px solid #e6e9f2;">
                        <i class="fas fa-users" style="color:#3b5bdb;"></i> Family members
                    </div>
                    <?php $members = is_array($family['members'] ?? null) ? $family['members'] : []; ?>
                    <?php if ($members === []): ?>
                        <div style="font-size:13px;color:#6b7289;">No other family members recorded.</div>
                    <?php else: ?>
                        <?php foreach ($members as $member): ?>
                            <?php
                            $bits = array_filter([
                                $member['name'] ?? '',
                                $member['gender'] ?? '',
                                $member['date_of_birth'] ?? '',
                            ], static fn ($bit) => trim((string) $bit) !== '');
                            $detail = implode(' · ', $bits);
                            if (! empty($member['is_deceased'])) {
                                $detail .= ($detail !== '' ? ' · ' : '') . 'Deceased';
                            }
                            ?>
                            <div style="display:flex;gap:12px;font-size:13px;padding:4px 0;">
                                <span style="min-width:150px;color:#6b7289;"><?= esc((string) ($member['relationship'] ?? 'Member')) ?></span>
                                <span style="color:#1a1d2e;font-weight:600;"><?= esc($detail) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script>
    (function () {
        const input = document.getElementById('householdFinderQuery');
        if (!input) return;

        function digitsOnly(value) {
            return String(value || '').replace(/\D/g, '').slice(0, 5);
        }

        function clearStoredSearch() {
            const url = new URL(window.location.href);
            if (!url.searchParams.has('q')) return;
            url.searchParams.delete('q');
            const next = url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : '');
            history.replaceState(null, '', next);
        }

        input.addEventListener('paste', function (e) {
            const clipboard = e.clipboardData || window.clipboardData;
            if (!clipboard) return;
            e.preventDefault();
            input.value = digitsOnly(clipboard.getData('text'));
        });

        input.addEventListener('input', function () {
            const digits = digitsOnly(input.value);
            if (input.value !== digits) input.value = digits;
            if (digits === '') clearStoredSearch();
        });

        window.addEventListener('pageshow', function () {
            const url = new URL(window.location.href);
            if (!url.searchParams.get('q')) input.value = '';
        });
    })();
</script>
</body>
</html>
