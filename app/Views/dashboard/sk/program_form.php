<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= empty($program) ? 'Add Program' : 'Edit Program' ?> - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .sk-form-panel { background: #fff; border: 1px solid #e8ecf4; padding: 22px; max-width: 760px; }
        .sk-form-label { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .sk-form-label .sk-required { color: #ef4444; }
        .sk-form-input { width: 100%; padding: 10px 12px; border: 1.5px solid #e5e7eb; font-family: inherit; font-size: 13px; color: #1d2448; background: #fff; box-sizing: border-box; }
        .sk-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
        .sk-form-row--full { grid-template-columns: 1fr; }
        .sk-form-row.is-disabled input { background: #f3f4f6; color: #9aa0b4; cursor: not-allowed; }
        .sk-form-hint { margin: 6px 0 0; font-size: 12px; color: #6b7689; }
        .sk-section-divider { margin: 8px 0 16px; font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: #9aa0b4; }
        .sk-req-list { display: flex; flex-direction: column; gap: 8px; }
        .sk-req-row { display: grid; grid-template-columns: 140px 1fr auto; gap: 8px; align-items: center; }
        .sk-req-add { margin-top: 8px; }
        .sk-elig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .sk-elig-option { display: flex; align-items: center; gap: 7px; padding: 8px 9px; border: 1px solid #e8ecf4; background: #fff; color: #4a5068; font-size: 12px; cursor: pointer; }
        @media (max-width: 640px) { .sk-form-row, .sk-req-row, .sk-elig-grid { grid-template-columns: 1fr; } }
    </style>
</head>

<body class="db-body">
    <?php
    $sessionRole = strtolower((string) session()->get('role'));
    $role        = $sessionRole === 'admin' ? 'admin' : 'sk';
    $active      = 'programs';
    $program     = (isset($program) && is_array($program)) ? $program : [];
    $programId   = (int) ($program['id'] ?? 0);
    $isEdit      = $programId > 0;
    $pageTitle   = $isEdit ? 'Edit Program' : 'Add Program';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $categories = ['Sports', 'Livelihood', 'Health', 'Education', 'Environment', 'Clean-up Drive', 'Cultural', 'Other'];
    $base = '/' . $role . '/programs';
    $action = $isEdit ? $base . '/update/' . $programId : $base . '/store';

    $field = static function (string $key, $fallback = '') use ($program) {
        $posted = old($key, null, false);
        if ($posted !== null && ! is_array($posted)) {
            return (string) $posted;
        }
        if (array_key_exists($key, $program) && $program[$key] !== null) {
            return (string) $program[$key];
        }

        return (string) $fallback;
    };
    $dateValue = static function (string $key) use ($field): string {
        $value = trim($field($key));

        return $value === '' ? '' : substr($value, 0, 10);
    };
    $postedTypes = old('req_type', null, false);
    $postedLabels = old('req_label', null, false);
    if (is_array($postedTypes) && is_array($postedLabels)) {
        $reqRows = [];
        foreach ($postedTypes as $index => $type) {
            $reqRows[] = ['type' => (string) $type, 'label' => (string) ($postedLabels[$index] ?? '')];
        }
    } else {
        $reqRows = \App\Models\BarangayActivityModel::decodeRequirements($program['requirements'] ?? null);
    }
    if ($reqRows === []) {
        $reqRows = [['type' => 'document', 'label' => '']];
    }
    $postedGroups = old('eligibility_groups', null, false);
    $selectedGroups = is_array($postedGroups)
        ? $postedGroups
        : \App\Models\BarangayActivityModel::parseEligibilityGroups($program['eligibility_groups'] ?? null);
    $eligibilityOther = (string) old('eligibility_other', $program['eligibility_other'] ?? '', false);
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div style="margin-bottom:16px;font-size:13px;">
                <a href="<?= esc($base) ?>" style="color:#16325c;font-weight:600;text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Programs &amp; Events
                </a>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="sk-form-panel">
                <h2 style="margin:0 0 4px;font-size:18px;color:#1c2b45;"><?= $isEdit ? 'Edit Program' : 'Add Program / Event' ?></h2>
                <p style="margin:0 0 18px;font-size:12px;color:#6b7689;">Status is set from the date the activity will be conducted.</p>
                <form method="post" action="<?= esc($action) ?>">
                    <?= csrf_field() ?>
                    <div class="sk-form-row sk-form-row--full">
                        <div>
                            <label class="sk-form-label" for="programName">Program name <span class="sk-required">*</span></label>
                            <input class="sk-form-input" id="programName" name="name" type="text" required maxlength="200" value="<?= esc($field('name')) ?>" placeholder="e.g. Youth Leadership Summit">
                        </div>
                    </div>
                    <div class="sk-form-row">
                        <div>
                            <label class="sk-form-label" for="programCategory">Category <span class="sk-required">*</span></label>
                            <select class="sk-form-input" id="programCategory" name="category" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= esc($category) ?>" <?= $field('category') === $category ? 'selected' : '' ?>><?= esc($category) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <p class="sk-form-hint" style="margin-top:28px;">Status updates automatically from the conducted date.</p>
                        </div>
                    </div>
                    <div class="sk-form-row sk-form-row--full">
                        <div>
                            <label class="sk-form-label" for="programDescription">Description</label>
                            <textarea class="sk-form-input" id="programDescription" name="description" rows="3"><?= esc($field('description')) ?></textarea>
                        </div>
                    </div>
                    <?php $cleanupSelected = \App\Models\BarangayActivityModel::isCleanupDrive($field('category'), $field('name')); ?>
                    <?php
                    $requirementBlockId = 'programRequirementsBlock';
                    include APPPATH . 'Views/dashboard/partials/activity_requirement_fields.php';
                    ?>
                    <p class="sk-section-divider">Schedule and venue</p>
                    <div class="sk-form-row<?= $cleanupSelected ? ' is-disabled' : '' ?>" id="programSubmissionDates">
                        <div>
                            <label class="sk-form-label" for="programStart">Start date of submission of requirements <span class="sk-required">*</span></label>
                            <input class="sk-form-input" id="programStart" name="start_date" type="date" <?= $cleanupSelected ? 'disabled' : 'required' ?> value="<?= esc($dateValue('start_date')) ?>">
                        </div>
                        <div>
                            <label class="sk-form-label" for="programEnd">End date of submission of requirements</label>
                            <input class="sk-form-input" id="programEnd" name="end_date" type="date" <?= $cleanupSelected ? 'disabled' : '' ?> value="<?= esc($dateValue('end_date')) ?>">
                        </div>
                    </div>
                    <div class="sk-form-row">
                        <div>
                            <label class="sk-form-label" for="programConducted">Date to be conducted <span class="sk-required">*</span></label>
                            <input class="sk-form-input" id="programConducted" name="conducted_date" type="date" required value="<?= esc($dateValue('conducted_date')) ?>">
                        </div>
                        <div>
                            <label class="sk-form-label" for="programVenue">Venue <span class="sk-required">*</span></label>
                            <input class="sk-form-input" id="programVenue" name="venue" type="text" required maxlength="255" value="<?= esc($field('venue')) ?>" placeholder="e.g. Barangay Hall">
                        </div>
                    </div>
                    <div class="sk-form-row">
                        <div>
                            <label class="sk-form-label" for="programTarget">Target participants</label>
                            <input class="sk-form-input" id="programTarget" name="target_participants" type="number" min="0" value="<?= esc($field('target_participants')) ?>" placeholder="e.g. 50">
                        </div>
                    </div>
                    <?php
                    $minAge = $field('min_age');
                    $maxAge = $field('max_age');
                    include APPPATH . 'Views/dashboard/partials/activity_eligibility_fields.php';
                    ?>
                    <p id="programCleanupNote" class="sk-form-hint" style="margin-bottom:16px;<?= $cleanupSelected ? '' : 'display:none;' ?>">A clean-up drive does not use supporting files or a requirements submission period, so those dates stay disabled. The conducted date is added to the calendar.</p>
                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
                        <a href="<?= esc($base) ?>" class="db-btn db-btn--outline">Cancel</a>
                        <button type="submit" class="db-btn db-btn--primary"><?= $isEdit ? 'Save changes' : 'Save program' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="/js/activity-form.js"></script>
    <script>
        ActivityForm.bindRequirements('programRequirementsBlock');
        ActivityForm.bindCleanup({
            categoryId: 'programCategory',
            titleId: 'programName',
            requirementsId: 'programRequirementsBlock',
            datesId: 'programSubmissionDates',
            noteId: 'programCleanupNote',
            startId: 'programStart',
            endId: 'programEnd'
        });
        ActivityForm.bindEligibility();
    </script>
</body>

</html>
