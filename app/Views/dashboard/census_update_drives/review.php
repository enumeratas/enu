<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Census Update - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .rev-card {
            background: #fff;
            border: 1px solid #e2e6ee;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(29,36,72,.06);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .rev-card-head {
            padding: 16px 22px;
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .rev-card-body { padding: 22px; }

        /* ── Resident info strip ── */
        .rev-who {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 24px;
            padding: 14px 16px;
            background: #f4f6fd;
            border: 1px solid #ccd5e8;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 13px;
        }
        .rev-who strong { color: #1d2448; }
        .rev-who span   { color: #6b7280; }

        /* ── Status badge ── */
        .rev-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
        }
        .rev-status--submitted { background: #fff8ee; color: #92400e; border: 1px solid #fcd34d; }
        .rev-status--approved  { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .rev-status--rejected  { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

        /* ── Diff table ── */
        .rev-diff-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        .rev-diff-table th {
            background: #f3f5fb;
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #4a5068;
            border-bottom: 2px solid #e4e9f2;
        }
        .rev-diff-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #f0f2f8;
            vertical-align: top;
        }
        .rev-diff-table tr:last-child td { border-bottom: none; }
        .rev-diff-table tr:hover td { background: #fbfcff; }
        .rev-field-name { font-weight: 600; color: #1d2448; }
        .rev-old { color: #9ca3af; text-decoration: line-through; font-size: 13px; }
        .rev-new { color: #065f46; font-weight: 600; }
        .rev-empty { color: #c4c9d4; font-style: italic; font-size: 12px; }

        /* ── No changes notice ── */
        .rev-confirm-box {
            padding: 18px 20px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            color: #166534;
            font-size: 13.5px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .rev-confirm-box i { font-size: 18px; margin-top: 1px; flex-shrink: 0; }

        /* ── Proof box ── */
        .rev-proof-box {
            padding: 16px 18px;
            background: #fafbff;
            border: 1px solid #dde4f0;
            border-radius: 10px;
        }
        .rev-proof-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #4a5068;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .rev-proof-img {
            max-width: 100%;
            max-height: 320px;
            display: block;
            border-radius: 8px;
            border: 1px solid #dde4f0;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .rev-proof-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #fff;
            border: 1.5px solid #ccd5e8;
            border-radius: 8px;
            color: #1d2448;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }
        .rev-proof-link:hover { background: #f4f6fd; }

        /* ── Action bar ── */
        .rev-action-bar {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            flex-wrap: wrap;
            padding: 20px 22px;
            background: #f8f9fc;
            border-top: 1px solid #e4e9f2;
        }
        .rev-reject-input {
            flex: 1;
            min-width: 220px;
            padding: 10px 13px;
            border: 1.5px solid #dde4f0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #1a1d2e;
        }
        .rev-reject-input:focus { border-color: #1d2448; outline: none; }

        /* ── Back link ── */
        .rev-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #4a5068;
            font-size: 13px;
            text-decoration: none;
            margin-bottom: 18px;
        }
        .rev-back:hover { color: #1d2448; }

        /* ── Already decided notice ── */
        .rev-decided {
            padding: 16px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .rev-decided--approved { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; }
        .rev-decided--rejected { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
    </style>
</head>
<body class="db-body">
<?php
$active    = 'census_updates';
$pageTitle = 'Review Census Update';
include APPPATH . 'Views/dashboard/sidebar.php';
?>
<div class="db-main">
    <?php include APPPATH . 'Views/dashboard/topbar.php'; ?>
    <div class="db-content">

        <a href="/<?= esc($role) ?>/census-updates" class="rev-back">
            <i class="fas fa-arrow-left"></i> Back to Census Update Drive
        </a>

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

        <?php
        $status       = $auth['status'] ?? 'pending';
        $alreadyDone  = in_array($status, ['approved', 'rejected'], true);
        $submittedAt  = ! empty($auth['submitted_at'])
            ? date('F j, Y \a\t g:i A', strtotime((string) $auth['submitted_at']))
            : '—';
        ?>

        <!-- ── Resident info ── -->
        <div class="rev-who">
            <div>
                <span>Resident: </span>
                <strong><?= esc($residentName ?: 'Unknown') ?></strong>
            </div>
            <div>
                <span>Household: </span>
                <strong>#<?= esc($householdNo ?: '—') ?></strong>
            </div>
            <div>
                <span>Type: </span>
                <strong><?= esc($scope === 'member' ? 'Personal information update' : 'Household information update') ?></strong>
            </div>
            <div>
                <span>Submitted: </span>
                <strong><?= esc($submittedAt) ?></strong>
            </div>
            <div>
                <span class="rev-status rev-status--<?= esc($status) ?>"><?= esc(ucfirst($status)) ?></span>
            </div>
        </div>

        <?php if ($alreadyDone): ?>
            <div class="rev-decided rev-decided--<?= esc($status) ?>">
                <i class="fas fa-<?= $status === 'approved' ? 'check-circle' : 'times-circle' ?>" style="font-size:18px;flex-shrink:0;margin-top:1px;"></i>
                <div>
                    This update has already been <strong><?= esc($status) ?></strong>.
                    <?php if (! empty($auth['reviewed_at'])): ?>
                        Reviewed on <?= esc(date('F j, Y', strtotime((string) $auth['reviewed_at']))) ?>.
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Changes ── -->
        <div class="rev-card">
            <div class="rev-card-head">
                <i class="fas fa-exchange-alt"></i>
                Proposed Changes
            </div>
            <div class="rev-card-body">
                <?php if ($confirmOnly): ?>
                    <div class="rev-confirm-box">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>No changes proposed.</strong><br>
                            The resident confirmed that their information is correct and up to date.
                        </div>
                    </div>
                <?php elseif (empty($diff)): ?>
                    <div class="rev-confirm-box" style="background:#fff8ee;border-color:#fcd34d;color:#92400e;">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            No field-level differences were detected between the submitted data and the current database record.
                            The submitted values may already match what is on file.
                        </div>
                    </div>
                <?php else: ?>
                    <table class="rev-diff-table">
                        <thead>
                            <tr>
                                <th style="width:200px;">Field</th>
                                <th>Current Value</th>
                                <th>Proposed Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diff as $row): ?>
                                <tr>
                                    <td class="rev-field-name"><?= esc($row['label']) ?></td>
                                    <td>
                                        <?php if ($row['old'] !== '' && $row['old'] !== null): ?>
                                            <span class="rev-old"><?= esc($row['old']) ?></span>
                                        <?php else: ?>
                                            <span class="rev-empty">(none)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="rev-new"><?= esc($row['new']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Proof document ── -->
        <?php if ($proofUrl): ?>
            <div class="rev-card">
                <div class="rev-card-head">
                    <i class="fas fa-paperclip"></i>
                    Supporting Document / Proof of Identity
                </div>
                <div class="rev-card-body">
                    <div class="rev-proof-box">
                        <div class="rev-proof-label">
                            <i class="fas fa-id-card"></i>
                            Uploaded by resident
                        </div>
                        <?php if ($proofIsImage): ?>
                            <img src="<?= esc($proofUrl) ?>" class="rev-proof-img" alt="Proof document"
                                onerror="this.style.display='none'; var n=document.getElementById('revProofMissing'); if(n) n.hidden=false;">
                            <div id="revProofMissing" hidden style="margin:10px 0;color:#92400e;font-size:13px;">
                                The preview could not be loaded. Use the button below to open the file.
                            </div>
                        <?php endif; ?>
                        <a href="<?= esc($proofUrl) ?>" target="_blank" rel="noopener" class="rev-proof-link" style="margin-top:12px;">
                            <i class="fas fa-external-link-alt"></i>
                            Open supporting document
                        </a>
                    </div>
                </div>
            </div>
        <?php elseif (! $confirmOnly && ! empty($diff)): ?>
            <div class="rev-card">
                <div class="rev-card-head" style="background:linear-gradient(135deg,#92400e,#b45309);">
                    <i class="fas fa-exclamation-triangle"></i>
                    No Proof Document Uploaded
                </div>
                <div class="rev-card-body">
                    <div class="rev-confirm-box" style="background:#fff8ee;border-color:#fcd34d;color:#92400e;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            The resident did not upload any supporting document for this update.
                            If name or birth date changes are involved, consider asking them to re-submit with proper ID.
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Action bar ── -->
        <?php if (! $alreadyDone): ?>
            <div class="rev-card">
                <div class="rev-action-bar">
                    <input type="text" id="rejectReasonInput" class="rev-reject-input"
                        placeholder="Rejection reason (required when rejecting)">
                    <form method="post" action="/<?= esc($role) ?>/resident/reject-census-update/<?= (int) $auth['id'] ?>"
                        onsubmit="return validateReject(this);" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="remarks" id="rejectRemarksHidden">
                        <button type="submit" class="db-btn db-btn--outline">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </form>
                    <form method="post" action="/<?= esc($role) ?>/resident/approve-census-update/<?= (int) $auth['id'] ?>"
                        onsubmit="return confirm('Apply this census update to the database now?');" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="db-btn db-btn--primary">
                            <i class="fas fa-check"></i> Approve &amp; Apply Changes
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div><!-- /.db-content -->
</div><!-- /.db-main -->

<script>
function validateReject(form) {
    var reason = document.getElementById('rejectReasonInput').value.trim();
    document.getElementById('rejectRemarksHidden').value = reason;
    return true;
}
</script>
</body>
</html>
