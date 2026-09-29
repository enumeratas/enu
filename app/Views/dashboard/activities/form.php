<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= empty($activity) ? 'Add Activity' : 'Edit Activity' ?> - Bacolod BIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <style>
        .sk-form-panel { background: #fff; border: 1px solid #e8ecf4; padding: 22px; max-width: 760px; }
        .sk-requirement-checks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; padding: 10px; border: 1px solid #e1e5ef; background: #fafbfe; }
        .sk-requirement-checks label { display: flex; align-items: center; gap: 7px; padding: 8px 9px; border: 1px solid #e8ecf4; background: #fff; color: #4a5068; font-size: 12px; cursor: pointer; }
        .sk-form-label { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .sk-form-label .sk-required { color: #ef4444; }
        .sk-form-input { width: 100%; padding: 10px 12px; border: 1.5px solid #e5e7eb; font-family: inherit; font-size: 13px; color: #1d2448; background: #fff; box-sizing: border-box; }
        .sk-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
        .sk-form-row--full { grid-template-columns: 1fr; }
        .sk-form-hint { margin: 6px 0 0; font-size: 12px; color: #6b7689; }
        .sk-section-divider { margin: 8px 0 16px; font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: #9aa0b4; }
        .banner-preview img { display: block; max-width: 240px; max-height: 120px; margin-top: 8px; object-fit: cover; }
        @media (max-width: 640px) { .sk-form-row, .sk-requirement-checks { grid-template-columns: 1fr; } }
    </style>
</head>

<body class="db-body">
    <?php
    $role     = $role ?? (session()->get('role') ?: 'secretary');
    $activity = (isset($activity) && is_array($activity)) ? $activity : [];
    $activityId = (int) ($activity['id'] ?? 0);
    $isEdit   = $activityId > 0;
    $active   = 'activities';
    $pageTitle = $isEdit ? 'Edit Activity' : 'Add Activity';
    include(APPPATH . 'Views/dashboard/sidebar.php');

    $categories = \App\Models\BarangayActivityModel::CATEGORIES;
    $requirements = \App\Models\BarangayActivityModel::REQUIREMENT_OPTIONS;
    $base = '/' . $role . '/activities';
    $action = $isEdit ? $base . '/update/' . $activityId : $base . '/store';

    $field = static function (string $key, $fallback = '') use ($activity) {
        $posted = old($key, null, false);
        if ($posted !== null && ! is_array($posted)) {
            return (string) $posted;
        }
        if (array_key_exists($key, $activity) && $activity[$key] !== null) {
            return (string) $activity[$key];
        }

        return (string) $fallback;
    };
    $dateValue = static function (string $key) use ($field): string {
        $value = trim($field($key));

        return $value === '' ? '' : substr($value, 0, 10);
    };
    $postedReqs = old('requirements', null, false);
    if (is_array($postedReqs)) {
        $selectedReqs = $postedReqs;
    } else {
        $raw = (string) ($activity['requirements'] ?? '');
        $selectedReqs = $raw === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
    $banner = trim((string) ($activity['banner_path'] ?? ''));
    ?>
    <div class="db-main">
        <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
        <div class="db-content">
            <div style="margin-bottom:16px;font-size:13px;">
                <a href="<?= esc($base) ?>" style="color:#16325c;font-weight:600;text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Brgy Activities
                </a>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="sk-form-panel">
                <h2 style="margin:0 0 4px;font-size:18px;color:#1c2b45;"><?= $isEdit ? 'Edit Activity' : 'Add Activity' ?></h2>
                <p style="margin:0 0 18px;font-size:12px;color:#6b7689;">Residents can join this activity from Brgy Activities. Status is set from the conducted date.</p>
                <form method="post" action="<?= esc($action) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="sk-form-row sk-form-row--full">
                        <div>
                            <label class="sk-form-label" for="activityTitle">Activity name <span class="sk-required">*</span></label>
                            <input class="sk-form-input" id="activityTitle" name="title" type="text" required maxlength="200" value="<?= esc($field('title')) ?>" placeholder="Example: Barangay cleanup drive">
                        </div>
                    </div>
                    <div class="sk-form-row">
                        <div>
                            <label class="sk-form-label" for="activityCategory">Category <span class="sk-required">*</span></label>
                            <select class="sk-form-input" id="activityCategory" name="category" required>
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
                            <label class="sk-form-label" for="activityDescription">Description</label>
                            <textarea class="sk-form-input" id="activityDescription" name="description" rows="3"><?= esc($field('description')) ?></textarea>
                        </div>
                    </div>
                    <p class="sk-section-divider">Upload requirements</p>
                    <div class="sk-requirement-checks">
                        <?php foreach ($requirements as $requirement): ?>
                            <label>
                                <input type="checkbox" name="requirements[]" value="<?= esc($requirement) ?>" <?= in_array($requirement, $selectedReqs, true) ? 'checked' : '' ?>>
                                <?= esc($requirement) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="sk-form-hint" style="margin-bottom:16px;">Select documents or photos that residents must upload when they join.</p>
                    <p class="sk-section-divider">Schedule and venue</p>
                    <div class="sk-form-row">
                        <div>
                            <label class="sk-form-label" for="activityStart">Start date of submission of requirements <span class="sk-required">*</span></label>
                            <input class="sk-form-input" id="activityStart" name="start_date" type="date" required value="<?= esc($dateValue('start_date')) ?>">
                        </div>
                        <div>
                            <label class="sk-form-label" for="activityEnd">End date of submission of requirements</label>
                            <input class="sk-form-input" id="activityEnd" name="end_date" type="date" value="<?= esc($dateValue('end_date')) ?>">
                        </div>
                    </div>
                    <div class="sk-form-row">
                        <div>
                            <label class="sk-form-label" for="activityConducted">Date to be conducted <span class="sk-required">*</span></label>
                            <input class="sk-form-input" id="activityConducted" name="conducted_date" type="date" required value="<?= esc($dateValue('conducted_date')) ?>">
                        </div>
                        <div>
                            <label class="sk-form-label">Eligible age range</label>
                            <div style="display:flex;gap:8px;">
                                <input class="sk-form-input" name="min_age" type="number" min="0" max="120" placeholder="Min age" value="<?= esc($field('min_age')) ?>">
                                <input class="sk-form-input" name="max_age" type="number" min="0" max="120" placeholder="Max age" value="<?= esc($field('max_age')) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="sk-form-row">
                        <div>
                            <label class="sk-form-label" for="activityVenue">Venue <span class="sk-required">*</span></label>
                            <input class="sk-form-input" id="activityVenue" name="venue" type="text" required maxlength="255" value="<?= esc($field('venue')) ?>" placeholder="e.g. Barangay Hall">
                        </div>
                        <div>
                            <label class="sk-form-label" for="activityTarget">Target participants</label>
                            <input class="sk-form-input" id="activityTarget" name="target_participants" type="number" min="0" value="<?= esc($field('target_participants')) ?>" placeholder="e.g. 50">
                        </div>
                    </div>
                    <div class="sk-form-row sk-form-row--full">
                        <div>
                            <label class="sk-form-label" for="activityBanner">Banner image</label>
                            <input class="sk-form-input" id="activityBanner" name="banner" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                            <p class="sk-form-hint">Optional. JPG, PNG, WEBP, or GIF up to 5 MB.</p>
                            <?php if ($banner !== ''): ?>
                                <div class="banner-preview">
                                    <img src="/uploads/<?= esc(ltrim($banner, '/')) ?>" alt="">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
                        <a href="<?= esc($base) ?>" class="db-btn db-btn--outline">Cancel</a>
                        <button type="submit" class="db-btn db-btn--primary"><?= $isEdit ? 'Save changes' : 'Create activity' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
