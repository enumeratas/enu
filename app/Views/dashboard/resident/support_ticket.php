<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body class="db-body">
    <?php
    $role = 'resident';
    $active = 'chatbot';
    $pageTitle = 'Support Ticket';
    $target = ($target ?? '') === 'admin' ? 'admin' : 'secretary';
    $desk = $target === 'admin' ? 'Barangay Admin' : 'Barangay Secretary';
    $pending = is_array($pending ?? null) ? $pending : null;
    include APPPATH . 'Views/dashboard/sidebar.php';
    ?>
    <div class="db-main">
        <?php include APPPATH . 'Views/dashboard/topbar.php'; ?>
        <div class="db-content">
            <div class="db-card" style="max-width:680px;background:#fff;border:1px solid #e2e6ee;padding:24px;">
                <h2 style="margin:0 0 6px;font-size:20px;color:#16325c;">Submit a ticket to the <?= esc($desk) ?></h2>
                <p style="margin:0 0 18px;color:#667085;font-size:13px;line-height:1.6;">
                    Add a title and describe your concern. After the <?= esc($desk) ?> approves this ticket, the chatbot opens a live conversation with that office.
                </p>

                <?php if ($pending): ?>
                    <div class="db-alert db-alert--warning" style="margin-bottom:16px;">
                        <i class="fas fa-clock"></i>
                        Your ticket “<?= esc($pending['title'] ?? '') ?>” is waiting for the <?= esc($desk) ?>.
                    </div>
                    <a href="/resident/chatbot" class="db-btn db-btn--outline">Back to chatbot</a>
                <?php else: ?>
                    <form method="post" action="/resident/support-ticket">
                        <?= csrf_field() ?>
                        <input type="hidden" name="target_role" value="<?= esc($target) ?>">
                        <div class="db-form-group" style="margin-bottom:14px;">
                            <label for="ticketTitle">Title</label>
                            <input id="ticketTitle" name="title" type="text" maxlength="160" required placeholder="Short title of your concern">
                        </div>
                        <div class="db-form-group" style="margin-bottom:18px;">
                            <label for="ticketConcern">Concern</label>
                            <textarea id="ticketConcern" name="concern" rows="6" maxlength="2000" required placeholder="Describe the concern you want to discuss"></textarea>
                        </div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <button type="submit" class="db-btn db-btn--primary"><i class="fas fa-paper-plane"></i> Submit ticket</button>
                            <a href="/resident/chatbot" class="db-btn db-btn--outline">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
