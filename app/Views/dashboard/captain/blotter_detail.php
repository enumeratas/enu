<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blotter Report - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="db-body">
    <?php
    $role      = $role ?? 'captain';
    $active    = 'blotter';
    $pageTitle = 'Blotter Report';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $r = $report ?? [];
    $caseNo = str_pad($r['id'] ?? 0, 2, '0', STR_PAD_LEFT) . '-' . date('Y', strtotime($r['created_at'] ?? 'now'));

    $statusMap = [
        'pending'            => ['db-badge--pending',  'Pending'],
        'under_investigation' => ['db-badge--info',     'Under Investigation'],
        'file_to_action'      => ['db-badge--rejected', 'File to Action'],
        'resolved'           => ['db-badge--approved', 'Resolved'],
        'dismissed'          => ['db-badge--rejected', 'Dismissed'],
    ];
    [$badgeClass, $statusLabel] = $statusMap[$r['status'] ?? 'pending'] ?? $statusMap['pending'];
    ?>

    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">

            <!-- Breadcrumb -->
            <div class="hh-breadcrumb" style="margin-bottom:20px;">
                <a href="/<?= $role ?>/blotter" class="hh-back"><i class="fas fa-arrow-left"></i> Back to Blotter Reports</a>
                <span class="hh-bc-sep">/</span>
                <span>Case #<?= $caseNo ?></span>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <?php /* flash handled by SweetAlert below */ ?>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <?php /* flash handled by SweetAlert below */ ?>
            <?php endif; ?>

            <!-- Header card -->
            <div class="cld-header-card" style="margin-bottom:20px;">

                <div class="cld-header-info">
                    <h2><?= esc($r['complainant_full_name'] ?? '—') ?>
                        <span class="hh-head-badge">Complainant</span>
                    </h2>
                    <div class="cld-header-meta">
                        <span><i class="fas fa-envelope"></i> <?= esc($r['complainant_email_addr'] ?? '—') ?></span>
                        <span><i class="fas fa-exclamation-triangle"></i> <?= esc($r['incident_type']) ?></span>
                        <?php if ($r['incident_date']): ?>
                            <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($r['incident_date'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="cld-header-right">
                    <span class="db-badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                </div>
            </div>

            <div class="bl-grid">

                <!-- LEFT: Report details + status update -->
                <div>
                    <!-- Report details -->
                    <div class="bl-card">
                        <h4 class="bl-card-title"><i class="fas fa-file-alt"></i> Report Details</h4>
                        <div class="bl-detail-list">
                            <div class="bl-detail-item"><span>Case #</span><strong><?= $caseNo ?></strong></div>
                            <div class="bl-detail-item"><span>Status</span><span class="db-badge <?= $badgeClass ?>"><?= $statusLabel ?></span></div>
                            <div class="bl-detail-item"><span>Incident Type</span><strong><?= esc($r['incident_type']) ?></strong></div>
                            <div class="bl-detail-item"><span>Date Filed</span><strong><?= date('M d, Y', strtotime($r['created_at'])) ?></strong></div>
                            <div class="bl-detail-item"><span>Incident Date</span><strong><?= $r['incident_date'] ? date('M d, Y', strtotime($r['incident_date'])) : '—' ?></strong></div>
                            <div class="bl-detail-item"><span>Incident Time</span><strong><?= $r['incident_time'] ? date('h:i A', strtotime($r['incident_time'])) : '—' ?></strong></div>
                            <div class="bl-detail-item" style="grid-column:1/-1;"><span>Location</span><strong><?= esc($r['location'] ?? '—') ?></strong></div>
                            <div class="bl-detail-item" style="grid-column:1/-1;"><span>Persons Involved</span><strong><?= esc($r['persons_involved'] ?? '—') ?></strong></div>
                        </div>
                    </div>

                    <!-- Evidence Photos -->
                    <?php
                    $evidencePhotos = [];
                    if (! empty($r['evidence_photos'])) {
                        $decoded = json_decode($r['evidence_photos'], true);
                        if (is_array($decoded)) {
                            $evidencePhotos = $decoded;
                        }
                    }
                    ?>
                    <?php
                    $evidenceUrls = [];
                    foreach ($evidencePhotos as $idx => $path) {
                        $evidenceUrls[] = '/' . $role . '/blotter/evidence/' . (int) ($r['id'] ?? 0) . '/' . (int) $idx;
                    }
                    ?>
                    <?php if (! empty($evidencePhotos)): ?>
                        <div class="bl-card">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;">
                                <h4 class="bl-card-title" style="margin:0;">
                                    <i class="fas fa-camera"></i> Evidence Photos
                                </h4>
                                <span style="font-size:12px;color:#7a8aaa;">
                                    <?= count($evidencePhotos) ?> photo<?= count($evidencePhotos) !== 1 ? 's' : '' ?>
                                </span>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;">
                                <?php foreach ($evidencePhotos as $idx => $path): ?>
                                    <div style="position:relative;border-radius:10px;overflow:hidden;aspect-ratio:1;background:#f0f2f8;cursor:pointer;"
                                        onclick="openLightbox(<?= $idx ?>)">
                                        <img src="<?= esc($evidenceUrls[$idx] ?? '') ?>"
                                            alt="Evidence <?= $idx + 1 ?>"
                                            loading="lazy"
                                            style="width:100%;height:100%;object-fit:cover;transition:transform .2s;"
                                            onmouseover="this.style.transform='scale(1.04)'"
                                            onmouseout="this.style.transform='scale(1)'">
                                        <div style="position:absolute;bottom:0;left:0;right:0;padding:4px 8px;background:linear-gradient(transparent,rgba(0,0,0,.45));color:#fff;font-size:11px;">
                                            Photo <?= $idx + 1 ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Lightbox overlay -->
                        <div id="evidenceLightbox"
                            style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;align-items:center;justify-content:center;flex-direction:column;"
                            onclick="closeLightbox()">
                            <img id="lightboxImg" src="" alt=""
                                style="max-width:90vw;max-height:85vh;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.6);object-fit:contain;">
                            <div style="margin-top:14px;display:flex;align-items:center;gap:16px;">
                                <button onclick="event.stopPropagation();shiftLightbox(-1)"
                                    style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:8px;padding:8px 18px;cursor:pointer;font-size:20px;">&#8592;</button>
                                <span id="lightboxCaption" style="color:#fff;font-size:13px;"></span>
                                <button onclick="event.stopPropagation();shiftLightbox(1)"
                                    style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:8px;padding:8px 18px;cursor:pointer;font-size:20px;">&#8594;</button>
                            </div>
                            <button onclick="closeLightbox()"
                                style="position:absolute;top:18px;right:22px;background:none;border:none;color:#fff;font-size:26px;cursor:pointer;">&#x2715;</button>
                        </div>

                        <script>
                            const EVIDENCE_PHOTOS = <?= json_encode(array_values($evidenceUrls)) ?>;
                            let lightboxIdx = 0;

                            function openLightbox(idx) {
                                lightboxIdx = idx;
                                updateLightbox();
                                const lb = document.getElementById('evidenceLightbox');
                                lb.style.display = 'flex';
                                document.body.style.overflow = 'hidden';
                            }

                            function closeLightbox() {
                                document.getElementById('evidenceLightbox').style.display = 'none';
                                document.body.style.overflow = '';
                            }

                            function shiftLightbox(dir) {
                                lightboxIdx = (lightboxIdx + dir + EVIDENCE_PHOTOS.length) % EVIDENCE_PHOTOS.length;
                                updateLightbox();
                            }

                            function updateLightbox() {
                                const path = EVIDENCE_PHOTOS[lightboxIdx];
                                document.getElementById('lightboxImg').src = '/' + path.replace(/^\//, '');
                                document.getElementById('lightboxCaption').textContent =
                                    'Photo ' + (lightboxIdx + 1) + ' of ' + EVIDENCE_PHOTOS.length;
                            }

                            document.addEventListener('keydown', function(e) {
                                const lb = document.getElementById('evidenceLightbox');
                                if (lb.style.display === 'none') return;
                                if (e.key === 'ArrowRight') shiftLightbox(1);
                                if (e.key === 'ArrowLeft') shiftLightbox(-1);
                                if (e.key === 'Escape') closeLightbox();
                            });
                        </script>
                    <?php endif; ?>

                    <!-- Narrative -->
                    <div class="bl-card">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                            <h4 class="bl-card-title" style="margin:0;"><i class="fas fa-align-left"></i> Narratives during Filing</h4>
                            <button type="button" class="bl-btn bl-btn--outline" style="padding:7px 12px;font-size:11px;" onclick="toggleNarratives('narrativeContent', this)">
                                <i class="fas fa-eye"></i> <span id="narrativeToggleLabel">Hide</span>
                            </button>
                        </div>
                        <div id="narrativeContent" style="margin-top:14px;">
                            <?php if ($role === 'secretary'): ?>
                                <form action="/secretary/blotter/narrative/<?= $r['id'] ?>" method="post">
                                    <?= csrf_field() ?>
                                    <div class="bl-form-group">
                                        <label>Complainant's Narrative</label>
                                        <textarea name="complainant_narrative" class="bl-textarea" rows="6" required><?= esc($r['complainant_narrative'] ?? $r['narrative'] ?? '') ?></textarea>
                                    </div>
                                    <div class="bl-form-group">
                                        <label>Respondent's Narrative</label>
                                        <textarea name="respondent_narrative" class="bl-textarea" rows="6" required><?= esc($r['respondent_narrative'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" class="bl-btn bl-btn--primary"><i class="fas fa-save"></i> Save Narratives</button>
                                </form>
                            <?php elseif (! empty($r['complainant_narrative']) || ! empty($r['respondent_narrative'])): ?>
                                <strong>Complainant's Narrative</strong>
                                <p class="bl-narrative"><?= nl2br(esc($r['complainant_narrative'] ?? '')) ?></p>
                                <strong>Respondent's Narrative</strong>
                                <p class="bl-narrative"><?= nl2br(esc($r['respondent_narrative'] ?? '')) ?></p>
                            <?php else: ?>
                                <p class="bl-narrative"><?= nl2br(esc($r['narrative'] ?? '')) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bl-card">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                            <h4 class="bl-card-title" style="margin:0;"><i class="fas fa-align-left"></i> Narratives during Hearing</h4>
                            <button type="button" class="bl-btn bl-btn--outline" style="padding:7px 12px;font-size:11px;" onclick="toggleNarratives('hearingNarrativeContent', this)">
                                <i class="fas fa-eye"></i> <span id="narrativeToggleLabel">Hide</span>
                            </button>
                        </div>
                        <div id="hearingNarrativeContent" style="margin-top:14px;">
                            <?php if ($role === 'secretary'): ?>
                                <form action="/secretary/blotter/hearing-narrative/<?= $r['id'] ?>" method="post">
                                    <?= csrf_field() ?>
                                    <div class="bl-form-group">
                                        <label>Complainant's Hearing Narrative</label>
                                        <textarea name="hearing_complainant_narrative" class="bl-textarea" rows="6" placeholder="Record what the complainant stated during the hearing..."><?= esc($r['hearing_complainant_narrative'] ?? '') ?></textarea>
                                    </div>
                                    <div class="bl-form-group">
                                        <label>Respondent's Hearing Narrative</label>
                                        <textarea name="hearing_respondent_narrative" class="bl-textarea" rows="6" placeholder="Record what the respondent stated during the hearing..."><?= esc($r['hearing_respondent_narrative'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" class="bl-btn bl-btn--primary"><i class="fas fa-save"></i> Save Hearing Narratives</button>
                                </form>
                            <?php elseif (! empty($r['hearing_complainant_narrative']) || ! empty($r['hearing_respondent_narrative'])): ?>
                                <?php if (! empty($r['hearing_complainant_narrative'])): ?>
                                    <strong>Complainant's Hearing Narrative</strong>
                                    <p class="bl-narrative"><?= nl2br(esc($r['hearing_complainant_narrative'])) ?></p>
                                <?php endif; ?>
                                <?php if (! empty($r['hearing_respondent_narrative'])): ?>
                                    <strong>Respondent's Hearing Narrative</strong>
                                    <p class="bl-narrative"><?= nl2br(esc($r['hearing_respondent_narrative'])) ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="bl-narrative">No hearing narratives recorded yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Update status -->
                    <div class="bl-card">
                        <h4 class="bl-card-title"><i class="fas fa-tasks"></i> Update Status</h4>
                        <form action="/<?= $role ?>/blotter/status/<?= $r['id'] ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="bl-form-group">
                                <label>Status</label>
                                <select name="status" class="bl-select">
                                    <option value="pending" <?= ($r['status'] ?? '') === 'pending'            ? 'selected' : '' ?>>Pending</option>
                                    <option value="under_investigation" <?= ($r['status'] ?? '') === 'under_investigation' ? 'selected' : '' ?>>Under Investigation</option>
                                    <option value="file_to_action" <?= ($r['status'] ?? '') === 'file_to_action' ? 'selected' : '' ?>>File to Action</option>
                                    <option value="resolved" <?= ($r['status'] ?? '') === 'resolved'           ? 'selected' : '' ?>>Resolved</option>
                                    <option value="dismissed" <?= ($r['status'] ?? '') === 'dismissed'          ? 'selected' : '' ?>>Dismissed</option>
                                </select>
                            </div>
                            <div class="bl-form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="bl-textarea" placeholder="Add notes or remarks..."><?= esc($r['remarks'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="bl-btn bl-btn--primary bl-btn--full">
                                <i class="fas fa-save"></i> Save Status
                            </button>
                        </form>
                    </div>
                </div>

                <!-- RIGHT: Summons + Schedule Management -->
                <div>
                    <!-- ── Schedule Management Card ── -->
                    <div class="bl-card">
                        <h4 class="bl-card-title"><i class="fas fa-calendar-alt"></i> Hearing Schedule</h4>

                        <?php if (! empty($r['hearing_date'])): ?>
                            <!-- Current schedule display -->
                            <div style="background:linear-gradient(135deg,#1d2448,#2e3a6e);border-radius:10px;padding:16px 18px;margin-bottom:16px;color:#fff;">
                                <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                                    <i class="fas fa-calendar-check"></i> Scheduled Hearing
                                </div>
                                <div style="font-size:18px;font-weight:700;margin-bottom:4px;">
                                    <?= date('F d, Y', strtotime($r['hearing_date'])) ?>
                                </div>
                                <div style="font-size:14px;color:rgba(255,255,255,.8);margin-bottom:8px;">
                                    <i class="fas fa-clock" style="margin-right:5px;"></i>
                                    <?= date('h:i A', strtotime($r['hearing_time'])) ?>
                                    &nbsp;&middot;&nbsp;
                                    <i class="fas fa-map-marker-alt" style="margin-right:5px;"></i>
                                    Barangay Hall
                                </div>
                                <?php if (! empty($r['hearing_notes'])): ?>
                                    <div style="font-size:12px;color:rgba(255,255,255,.65);border-top:1px solid rgba(255,255,255,.15);padding-top:8px;margin-top:4px;">
                                        <i class="fas fa-sticky-note" style="margin-right:4px;"></i>
                                        <?= esc($r['hearing_notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Letter button -->
                            <a href="/<?= $role ?>/blotter/letter/<?= $r['id'] ?>"
                                class="bl-btn bl-btn--primary bl-btn--full" style="margin-bottom:12px;text-decoration:none;">
                                <i class="fas fa-file-alt"></i> View / Print Summons Letter
                                <?php if (! empty($r['letter_issued_at'])): ?>
                                    <span style="font-size:10px;font-weight:400;opacity:.75;margin-left:4px;">
                                        (last issued <?= date('M d', strtotime($r['letter_issued_at'])) ?>)
                                    </span>
                                <?php endif; ?>
                            </a>

                            <!-- Reschedule form (hidden by default) -->
                            <div id="rescheduleForm" style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid #f0f2f8;">
                                <form action="/<?= $role ?>/blotter/reschedule/<?= $r['id'] ?>" method="post">
                                    <?= csrf_field() ?>
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                                        <div class="bl-form-group" style="margin:0;">
                                            <label>New Date <span style="color:#c0392b;">*</span></label>
                                            <input type="date" name="hearing_date" class="bl-input"
                                                value="<?= esc($r['hearing_date']) ?>"
                                                min="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="bl-form-group" style="margin:0;">
                                            <label>New Time <span style="color:#c0392b;">*</span></label>
                                            <input type="time" name="hearing_time" class="bl-input"
                                                value="<?= esc($r['hearing_time']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="bl-form-group">
                                        <label>Notes / Reason for Rescheduling</label>
                                        <textarea name="hearing_notes" class="bl-textarea"
                                            placeholder="e.g. Rescheduled due to unavailability of parties..."><?= esc($r['hearing_notes'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" class="bl-btn bl-btn--primary bl-btn--full">
                                        <i class="fas fa-save"></i> Save New Schedule
                                    </button>
                                </form>
                            </div>

                        <?php else: ?>
                            <!-- No schedule yet -->
                            <div style="text-align:center;padding:20px 0;color:#9aa0b4;">
                                <i class="fas fa-calendar-times" style="font-size:28px;display:block;margin-bottom:8px;color:#d0d5e8;"></i>
                                <p style="font-size:13px;">No hearing scheduled yet.</p>
                                <p style="font-size:12px;margin-top:4px;">Set a schedule in the Summons section below.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── Summons Card ── -->
                    <div class="bl-card">
                        <h4 class="bl-card-title"><i class="fas fa-envelope"></i> Send Summons</h4>

                        <?php if ($r['summons_sent_at']): ?>
                            <div class="bl-summons-sent" style="margin-bottom:16px;">
                                <i class="fas fa-check-circle"></i>
                                <span>Last Save <strong><?= date('M d, Y h:i A', strtotime($r['summons_sent_at'])) ?></strong></span>
                            </div>
                        <?php endif; ?>

                        <form action="/<?= $role ?>/blotter/summons/<?= $r['id'] ?>" method="post">
                            <?= csrf_field() ?>

                            <!-- Complainant (auto-filled) -->
                            <div style="background:#f8f9fc;border:1px solid #e8ecf4;border-radius:10px;padding:14px 16px;margin-bottom:12px;">
                                <div style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                                    <i class="fas fa-user-tie"></i> Complainant (auto-filled)
                                </div>
                                <div style="font-size:13px;font-weight:600;color:#1a1d2e;"><?= esc($r['complainant_full_name'] ?? '—') ?></div>
                                <div style="font-size:12px;color:#9aa0b4;margin-top:3px;">
                                    <i class="fas fa-envelope" style="width:12px;"></i>
                                    <?= esc($r['complainant_email_addr'] ?? '—') ?>
                                </div>
                            </div>

                            <!-- Respondent fields -->
                            <div style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.5px;margin:14px 0 10px;">
                                <i class="fas fa-user-slash"></i> Respondent
                            </div>
                            <div class="bl-form-group">
                                <label>Respondent Name</label>
                                <input type="text" name="respondent_name" class="bl-input"
                                    value="<?= esc($r['respondent_name'] ?? $r['persons_involved'] ?? '') ?>"
                                    placeholder="Full name of the respondent">
                            </div>
                            <div class="bl-form-group">
                                <label>Respondent Email <span style="font-size:11px;color:#9aa0b4;font-weight:400;">(required to send summons)</span></label>
                                <input type="email" name="respondent_email" class="bl-input"
                                    value="<?= esc($r['respondent_email'] ?? '') ?>"
                                    placeholder="respondent@email.com">
                            </div>
                            <div class="bl-form-group">
                                <label>Respondent Address <span style="font-size:11px;color:#9aa0b4;font-weight:400;">(optional)</span></label>
                                <input type="text" name="respondent_address" class="bl-input"
                                    value="<?= esc($r['respondent_address'] ?? ($suggestedRespondentAddr ?? '')) ?>"
                                    placeholder="e.g. Zone 3, Barangay Bacolod">
                            </div>

                            <!-- Hearing schedule -->
                            <div style="font-size:11px;font-weight:700;color:#9aa0b4;text-transform:uppercase;letter-spacing:.5px;margin:14px 0 10px;">
                                <i class="fas fa-calendar-alt"></i> Hearing Schedule
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:10px;">
                                <div class="bl-form-group" style="margin:0;">
                                    <label>Hearing Date <span style="color:#c0392b;">*</span></label>
                                    <input type="date" name="hearing_date" class="bl-input"
                                        value="<?= esc($r['hearing_date'] ?? '') ?>"
                                        min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="bl-form-group" style="margin:0;">
                                    <label>Hearing Time <span style="color:#c0392b;">*</span></label>
                                    <input type="time" name="hearing_time" class="bl-input"
                                        value="<?= esc($r['hearing_time'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="bl-form-group">
                                <label>Hearing Notes <span style="font-size:11px;color:#9aa0b4;font-weight:400;">(optional)</span></label>
                                <input type="text" name="hearing_notes" class="bl-input"
                                    value="<?= esc($r['hearing_notes'] ?? '') ?>"
                                    placeholder="e.g. Bring supporting documents">
                            </div>

                            <!-- Email notice -->
                            <div style="background:#f0f4ff;border:1px solid #d0d8f5;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:#4a5068;display:flex;gap:8px;align-items:flex-start;">
                                <i class="fas fa-info-circle" style="color:#5b6fd6;margin-top:1px;flex-shrink:0;"></i>
                                <span>
                                    A summons email will be sent to the complainant's registered Gmail
                                    <?= ! empty($r['respondent_email']) ? 'and the respondent\'s email' : '<strong>only</strong>. Enter the respondent\'s email above to also notify them' ?>.
                                </span>
                            </div>

                            <button type="submit" class="bl-btn bl-btn--primary bl-btn--full">
                                <i class="fas fa-paper-plane"></i>
                                <?= $r['summons_sent_at'] ? 'Save &amp; Resend Summons' : 'Save &amp; Send Summons' ?>
                            </button>
                        </form>
                    </div>

                    <!-- ── Certificate to File Action Card ── -->
                    <?php
                    $isUnresolved = ! in_array($r['status'] ?? 'pending', ['resolved', 'dismissed'], true);
                    ?>
                    <div class="bl-card" style="<?= $isUnresolved ? 'border:1.5px solid #fde8c8;' : '' ?>">
                        <h4 class="bl-card-title">
                            <i class="fas fa-file-certificate" style="color:#e67e22;"></i>
                            Certificate to File Action
                        </h4>

                        <?php if (! $isUnresolved): ?>
                            <!-- Case is resolved/dismissed — no referral needed -->
                            <div style="background:#e6f9f1;border:1px solid #b2e8d2;border-radius:9px;padding:12px 16px;font-size:13px;color:#0e9464;display:flex;align-items:center;gap:9px;">
                                <i class="fas fa-check-circle" style="flex-shrink:0;"></i>
                                <span>This case is <strong><?= ucfirst($r['status']) ?></strong>. No referral to the police station is required.</span>
                            </div>
                        <?php else: ?>
                            <!-- Informational notice -->
                            <div style="background:#fff8f0;border:1px solid #fde8c8;border-radius:9px;padding:14px 16px;font-size:13px;color:#7a4200;line-height:1.7;margin-bottom:16px;">
                                <div style="display:flex;gap:10px;align-items:flex-start;">
                                    <i class="fas fa-exclamation-triangle" style="color:#e67e22;margin-top:2px;flex-shrink:0;"></i>
                                    <span>
                                        If this case is <strong>not resolved</strong> at the barangay level, the parties may be referred to the
                                        <strong>police station</strong> or the appropriate court.
                                        The Secretary may issue a <strong>Certificate to File Action</strong> (also called a
                                        <em>Certification to File Action</em>) to certify that the barangay has exhausted
                                        conciliation proceedings under <strong>R.A. 7160</strong> (Katarungang Pambarangay Law).
                                    </span>
                                </div>
                            </div>

                            <!-- Issue button -->
                            <a href="/<?= esc($role) ?>/blotter/certificate/<?= (int)$r['id'] ?>"
                                style="display:flex;align-items:center;justify-content:center;gap:9px;width:100%;padding:12px 16px;background:linear-gradient(135deg,#e67e22,#ca6f1e);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;text-decoration:none;transition:opacity .18s;"
                                onmouseover="this.style.opacity='.88'"
                                onmouseout="this.style.opacity='1'">
                                <i class="fas fa-file-alt"></i>
                                View / Print Certificate to File Action
                            </a>

                            <p style="font-size:11.5px;color:#b0b6cc;margin:10px 0 0;text-align:center;">
                                <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                                The certificate will open in a new tab ready for printing or saving as PDF.
                            </p>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        function toggleNarratives(contentId, button) {
            const content = document.getElementById(contentId);
            const label = button.querySelector('[id$="ToggleLabel"]');
            const hidden = content.style.display === 'none';
            content.style.display = hidden ? '' : 'none';
            label.textContent = hidden ? 'Hide' : 'View';
        }

        document.querySelectorAll('.db-nav-item').forEach(i =>
            i.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'))
        );

        // ── SweetAlert flash notifications ────────────────────────────────
        <?php $flashSuccess = session()->getFlashdata('success'); ?>
        <?php $flashError   = session()->getFlashdata('error');   ?>

        <?php if ($flashSuccess): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: <?= json_encode($flashSuccess) ?>,
                confirmButtonColor: '#1d2448',
                confirmButtonText: 'OK',
                timer: 4000,
                timerProgressBar: true
            });
        <?php endif; ?>

        <?php if ($flashError): ?>
            Swal.fire({
                icon: 'error',
                title: 'Something went wrong',
                text: <?= json_encode($flashError) ?>,
                confirmButtonColor: '#c0392b',
                confirmButtonText: 'Close'
            });
        <?php endif; ?>
    </script>

    <!-- ── Hearing schedule conflict checker ── -->
    <script>
        (function() {

            // Cache: date → [{start, end, label}]
            const slotCache = {};

            function toMin(t) {
                if (!t) return null;
                const parts = t.split(':');
                return parseInt(parts[0]) * 60 + parseInt(parts[1]);
            }

            function fmt12(t) {
                if (!t) return '';
                const [h, m] = t.split(':');
                const hr = parseInt(h);
                const ampm = hr >= 12 ? 'PM' : 'AM';
                return (hr % 12 || 12) + ':' + m + ' ' + ampm;
            }

            // Returns true when a 1-hour block starting at aStart overlaps [bStart, bEnd)
            function overlaps(aStart, bStart, bEnd) {
                const a0 = toMin(aStart);
                if (a0 === null) return false;
                const a1 = a0 + 60;
                const b0 = toMin(bStart);
                if (b0 === null) return false;
                const b1 = bEnd ? toMin(bEnd) : b0 + 60;
                return a0 < b1 && a1 > b0;
            }

            async function fetchSlots(date) {
                if (slotCache[date]) return slotCache[date];
                try {
                    const res = await fetch('/public/blotter/busy-slots?date=' + encodeURIComponent(date));
                    const data = await res.json();
                    slotCache[date] = data.slots || [];
                } catch (e) {
                    slotCache[date] = [];
                }
                return slotCache[date];
            }

            // Wire up a pair of date + time inputs with a conflict feedback element
            function wireHearingPair(dateInput, timeInput, feedbackEl, submitBtn) {
                if (!dateInput || !timeInput) return;

                async function check() {
                    const date = dateInput.value;
                    const time = timeInput.value;

                    // Remove existing feedback
                    if (feedbackEl) {
                        feedbackEl.style.display = 'none';
                        feedbackEl.innerHTML = '';
                    }
                    if (submitBtn) submitBtn.disabled = false;

                    if (!date || !time) return;

                    const slots = await fetchSlots(date);
                    const conflicting = slots.filter(s => overlaps(time, s.start, s.end));

                    if (conflicting.length === 0) return;

                    const names = conflicting.map(s => {
                        const endStr = s.end ? ' – ' + fmt12(s.end) : ' (1 hr)';
                        return '<strong>' + s.label + '</strong> (' + fmt12(s.start) + endStr + ')';
                    }).join(', ');

                    if (feedbackEl) {
                        feedbackEl.innerHTML =
                            '<i class="fas fa-exclamation-circle" style="margin-right:5px;color:#c0392b;"></i>' +
                            '<span>This time conflicts with: ' + names + '. Please pick a different time.</span>';
                        feedbackEl.style.display = 'flex';
                    }
                    if (submitBtn) submitBtn.disabled = true;
                }

                dateInput.addEventListener('change', check);
                timeInput.addEventListener('change', check);
                timeInput.addEventListener('input', check);
            }

            function disableFullyBookedDates(dateInput) {
                if (!dateInput || typeof flatpickr !== 'function') return;

                fetch('/public/blotter/busy-dates')
                    .then(response => response.json())
                    .then(data => {
                        const unavailableDates = (data.dates || [])
                            .filter(item => item.busy)
                            .map(item => item.date);
                        dateInput.dataset.unavailableDates = unavailableDates.join(',');
                        flatpickr(dateInput, {
                            dateFormat: 'Y-m-d',
                            minDate: dateInput.min || 'today',
                            disable: unavailableDates,
                            onChange: () => dateInput.dispatchEvent(new Event('change'))
                        });
                    })
                    .catch(() => {
                        dateInput.dataset.unavailableDates = '';
                    });
            }

            // ── Summons form ──────────────────────────────────────────────────────
            const summonsForm = document.querySelector('form[action*="/summons/"]');
            if (summonsForm) {
                const dateIn = summonsForm.querySelector('input[name="hearing_date"]');
                const timeIn = summonsForm.querySelector('input[name="hearing_time"]');
                const submitBtn = summonsForm.querySelector('button[type="submit"]');

                // Create feedback element and insert after the date/time row
                const feedback = document.createElement('div');
                feedback.style.cssText =
                    'display:none;align-items:flex-start;gap:8px;background:#fff0f1;' +
                    'border:1px solid #fad4d4;border-radius:8px;padding:10px 14px;' +
                    'font-size:12.5px;color:#c0392b;margin-bottom:12px;';

                if (dateIn && dateIn.closest('.bl-form-group')) {
                    const row = dateIn.closest('div[style*="grid"]') || dateIn.closest('.bl-form-group');
                    if (row && row.parentNode) {
                        row.parentNode.insertBefore(feedback, row.nextSibling);
                    }
                }

                wireHearingPair(dateIn, timeIn, feedback, submitBtn);
                disableFullyBookedDates(dateIn);
            }

            // ── Reschedule form ───────────────────────────────────────────────────
            const rescheduleForm = document.querySelector('form[action*="/reschedule/"]');
            if (rescheduleForm) {
                const dateIn = rescheduleForm.querySelector('input[name="hearing_date"]');
                const timeIn = rescheduleForm.querySelector('input[name="hearing_time"]');
                const submitBtn = rescheduleForm.querySelector('button[type="submit"]');

                const feedback = document.createElement('div');
                feedback.style.cssText =
                    'display:none;align-items:flex-start;gap:8px;background:#fff0f1;' +
                    'border:1px solid #fad4d4;border-radius:8px;padding:10px 14px;' +
                    'font-size:12.5px;color:#c0392b;margin-bottom:10px;';

                if (dateIn) {
                    const row = dateIn.closest('div[style*="grid"]') || dateIn.closest('.bl-form-group');
                    if (row && row.parentNode) {
                        row.parentNode.insertBefore(feedback, row.nextSibling);
                    }
                }

                wireHearingPair(dateIn, timeIn, feedback, submitBtn);
                disableFullyBookedDates(dateIn);
            }

        })();
    </script>
</body>

</html>