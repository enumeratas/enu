<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body class="db-body">
    <?php
    $role = in_array($role ?? '', ['admin', 'secretary'], true) ? $role : 'secretary';
    $active = 'support_tickets';
    $pageTitle = 'Support Tickets';
    $tickets = is_array($tickets ?? null) ? $tickets : [];
    include APPPATH . 'Views/dashboard/sidebar.php';
    ?>
    <div class="db-main">
        <?php include APPPATH . 'Views/dashboard/topbar.php'; ?>
        <div class="db-content">
            <p style="margin:0 0 16px;color:#667085;font-size:13px;">
                Approve a ticket to open a live conversation. Continue that conversation in Customer Service.
            </p>
            <form method="get" data-live-results="liveResults" style="margin-bottom:16px;">
                <div class="db-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" data-live-query autocomplete="off" placeholder="Search resident, title, or date..." value="<?= esc($search ?? '') ?>">
                </div>
            </form>
            <div id="liveResults">
            <div class="db-table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>Title</th>
                            <th>Concern</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tickets === []): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;color:#9aa0b4;padding:28px;"><?= ($search ?? '') !== '' ? 'No tickets match your search.' : 'No support tickets yet.' ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <?php
                                $name = trim((string) (($ticket['first_name'] ?? '') . ' ' . ($ticket['last_name'] ?? '')));
                                if ($name === '') {
                                    $name = (string) ($ticket['username'] ?? 'Resident');
                                }
                                $status = (string) ($ticket['status'] ?? 'pending');
                                ?>
                                <tr>
                                    <td><?= pii($name, 'name') ?></td>
                                    <td><?= esc($ticket['title'] ?? '') ?></td>
                                    <td style="max-width:320px;white-space:pre-wrap;"><?= esc($ticket['concern'] ?? '') ?></td>
                                    <td><?= esc(ucfirst($status)) ?></td>
                                    <td><?= ! empty($ticket['created_at']) ? esc(date('M j, Y g:i A', strtotime($ticket['created_at']))) : '—' ?></td>
                                    <td>
                                        <?php if ($status === 'pending'): ?>
                                            <div class="db-action-group">
                                                <form method="post" action="/<?= esc($role) ?>/support-tickets/approve/<?= (int) $ticket['id'] ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="db-btn db-btn--primary db-btn--sm">Approve</button>
                                                </form>
                                                <form method="post" action="/<?= esc($role) ?>/support-tickets/decline/<?= (int) $ticket['id'] ?>" onsubmit="return confirm('Decline this ticket?')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="db-btn db-btn--outline db-btn--sm">Decline</button>
                                                </form>
                                            </div>
                                        <?php elseif ($status === 'approved'): ?>
                                            <a href="/<?= esc($role) ?>/customer-service" class="db-btn db-btn--outline db-btn--sm">Open chat</a>
                                        <?php else: ?>
                                            <span style="color:#9aa0b4;">Closed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</body>

</html>
