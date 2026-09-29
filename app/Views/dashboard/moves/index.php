<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Household Moves - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .mv-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .mv-tabs {
            display: inline-flex;
            border: 1px solid #e2e6ee;
            background: #fff;
        }

        .mv-tabs a {
            padding: 8px 14px;
            font-size: 12.5px;
            font-weight: 600;
            color: #4b556a;
            text-decoration: none;
            border-right: 1px solid #e2e6ee;
        }

        .mv-tabs a:last-child {
            border-right: none;
        }

        .mv-tabs a.is-active {
            background: #16325c;
            color: #fff;
        }

        .mv-tabs a .count {
            display: inline-block;
            margin-left: 6px;
            padding: 1px 8px;
            border-radius: 999px;
            font-size: 11px;
            background: rgba(0, 0, 0, .06);
            color: inherit;
        }

        .mv-search {
            display: flex;
            gap: 8px;
        }

        .mv-search input {
            padding: 8px 12px;
            border: 1px solid #d7dce6;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            min-width: 240px;
        }

        .mv-empty {
            text-align: center;
            padding: 40px 20px;
            background: #fff;
            border: 1px solid #e2e6ee;
            color: #6b7689;
        }

        .mv-summary {
            font-size: 12.5px;
            color: #3d4658;
        }

        .mv-actions form {
            display: inline;
        }

        .reject-modal-body {
            padding: 18px 22px;
        }

        .reject-modal-body textarea {
            width: 100%;
            min-height: 110px;
            padding: 10px 12px;
            border: 1px solid #d7dce6;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            box-sizing: border-box;
        }
    </style>
</head>

<body class="db-body">
    <?php
    $role      = $role ?? (session()->get('role') ?: 'secretary');
    $active    = 'moves';
    $pageTitle = 'Household Moves';
    $moves     = $moves  ?? [];
    $counts    = $counts ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    $status    = $status ?? '';
    $search    = $search ?? '';
    $canApprove = in_array($role, ['captain', 'admin'], true);
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

            <div class="mv-toolbar">
                <div class="mv-tabs">
                    <a class="<?= $status === '' ? 'is-active' : '' ?>" href="/<?= esc($role) ?>/moves">All</a>
                    <a class="<?= $status === 'pending' ? 'is-active' : '' ?>" href="/<?= esc($role) ?>/moves?status=pending">Pending <span class="count"><?= (int) $counts['pending'] ?></span></a>
                    <a class="<?= $status === 'approved' ? 'is-active' : '' ?>" href="/<?= esc($role) ?>/moves?status=approved">Approved <span class="count"><?= (int) $counts['approved'] ?></span></a>
                    <a class="<?= $status === 'rejected' ? 'is-active' : '' ?>" href="/<?= esc($role) ?>/moves?status=rejected">Rejected <span class="count"><?= (int) $counts['rejected'] ?></span></a>
                </div>
                <div style="display:flex;gap:10px;align-items:center;">
                    <form class="mv-search" method="get">
                        <?php if ($status !== ''): ?>
                            <input type="hidden" name="status" value="<?= esc($status) ?>">
                        <?php endif; ?>
                        <input type="text" name="q" placeholder="Search household # or head name" value="<?= esc($search) ?>">
                        <button type="submit" class="db-btn db-btn--outline db-btn--sm"><i class="fas fa-search"></i> Search</button>
                    </form>
                    <a class="db-btn db-btn--primary" href="/<?= esc($role) ?>/moves/new">
                        <i class="fas fa-exchange-alt"></i> File a Move
                    </a>
                </div>
            </div>

            <?php if ($moves === []): ?>
                <div class="mv-empty">
                    <i class="fas fa-exchange-alt" style="font-size:36px;color:#c5cdd8;display:block;margin-bottom:12px;"></i>
                    <p style="margin:0;font-weight:600;color:#1c2b45;">No household moves yet</p>
                    <p style="margin:6px 0 0;font-size:13px;">Use <strong>File a Move</strong> when a family or member moves to another house.</p>
                </div>
            <?php else: ?>
                <div class="db-table-wrap">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Move</th>
                                <th>Reason</th>
                                <th>Requested by</th>
                                <th>Filed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($moves as $move): ?>
                                <?php
                                $srcHead = trim(($move['src_first_name'] ?? '') . ' ' . ($move['src_last_name'] ?? '')) ?: '—';
                                $dstHead = trim(($move['dst_first_name'] ?? '') . ' ' . ($move['dst_last_name'] ?? ''));
                                $reqBy   = trim(($move['req_first'] ?? '') . ' ' . ($move['req_last'] ?? '')) ?: '—';
                                $procBy  = trim(($move['proc_first'] ?? '') . ' ' . ($move['proc_last'] ?? ''));
                                $mids    = json_decode((string) ($move['member_ids'] ?? '[]'), true);
                                $mCount  = is_array($mids) ? count($mids) : 0;
                                $badge   = match ($move['status']) {
                                    'approved' => 'dbadge--approved',
                                    'rejected' => 'dbadge--rejected',
                                    default    => 'dbadge--pending',
                                };
                                ?>
                                <tr>
                                    <td>
                                        <strong>#<?= esc($move['source_household_no']) ?> <?= esc($srcHead) ?></strong>
                                        <div class="mv-summary" style="margin-top:2px;color:#6b7689;">
                                            → <?= $move['destination_type'] === 'new' ? 'New household' : ('Household #' . esc($move['destination_household_no'] ?? '—') . ($dstHead !== '' ? ' (' . esc($dstHead) . ')' : '')) ?>
                                        </div>
                                        <div class="mv-summary" style="margin-top:2px;">
                                            <?= (int) $move['includes_head'] === 1 ? '<span class="dbadge dbadge--approved" style="margin-right:4px;">Head moves</span>' : '' ?>
                                            <?= $mCount > 0 ? ($mCount . ' member' . ($mCount === 1 ? '' : 's')) : ((int) $move['includes_head'] === 1 ? '' : 'No members selected') ?>
                                        </div>
                                        <?php if (! empty($move['summary'])): ?>
                                            <div class="mv-summary" style="margin-top:4px;color:#485062;"><?= esc($move['summary']) ?></div>
                                        <?php endif; ?>
                                        <?php if (! empty($move['rejection_reason'])): ?>
                                            <div class="mv-summary" style="margin-top:4px;color:#b5321a;">Rejected: <?= esc($move['rejection_reason']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= esc($move['move_reason'] ?: '—') ?>
                                        <?php if (! empty($move['notes'])): ?>
                                            <div class="mv-summary" style="color:#6b7689;margin-top:2px;"><?= esc($move['notes']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($reqBy) ?></td>
                                    <td style="white-space:nowrap;font-size:12px;color:#6b7689;">
                                        <?= ! empty($move['created_at']) ? esc(date('M j, Y g:i A', strtotime($move['created_at']))) : '—' ?>
                                        <?php if (! empty($move['processed_at'])): ?>
                                            <div style="margin-top:4px;">Processed <?= esc(date('M j, Y g:i A', strtotime($move['processed_at']))) ?><?= $procBy !== '' ? ' by ' . esc($procBy) : '' ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="dbadge <?= $badge ?>"><?= esc(ucfirst($move['status'])) ?></span></td>
                                    <td class="mv-actions" style="white-space:nowrap;">
                                        <?php if ($move['status'] === 'pending' && $canApprove): ?>
                                            <form method="post" action="/captain/moves/approve/<?= (int) $move['id'] ?>" onsubmit="return confirm('Apply this move now?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="db-btn db-btn--primary db-btn--sm"><i class="fas fa-check"></i> Approve</button>
                                            </form>
                                            <button type="button" class="db-btn db-btn--outline db-btn--sm" onclick="openReject(<?= (int) $move['id'] ?>)"><i class="fas fa-times"></i> Reject</button>
                                        <?php elseif ($move['status'] === 'pending'): ?>
                                            <span style="color:#6b7689;font-size:12px;">Awaiting captain</span>
                                        <?php else: ?>
                                            <span style="color:#6b7689;font-size:12px;">—</span>
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

    <div class="db-modal-overlay" id="rejectModal" style="display:none;">
        <div class="db-modal" style="max-width:480px;">
            <div class="db-modal-header">
                <h3><i class="fas fa-times-circle"></i> Reject Move</h3>
                <button class="db-modal-close" type="button" onclick="closeReject()"><i class="fas fa-times"></i></button>
            </div>
            <form method="post" id="rejectForm">
                <?= csrf_field() ?>
                <div class="reject-modal-body">
                    <label for="rejectReason" style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Reason (optional but helpful)</label>
                    <textarea name="rejection_reason" id="rejectReason" placeholder="Tell the requester what to fix before filing again"></textarea>
                </div>
                <div style="padding:14px 22px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e6ee;">
                    <button type="button" class="db-btn db-btn--outline" onclick="closeReject()">Cancel</button>
                    <button type="submit" class="db-btn db-btn--primary"><i class="fas fa-times"></i> Reject Move</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReject(id) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = '/captain/moves/reject/' + id;
            modal.style.display = 'flex';
        }

        function closeReject() {
            document.getElementById('rejectModal').style.display = 'none';
        }
    </script>
</body>

</html>
