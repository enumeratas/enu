<?php
$selectedGroups = is_array($selectedGroups ?? null) ? $selectedGroups : [];
$eligibilityOther = (string) ($eligibilityOther ?? '');
$options = \App\Models\BarangayActivityModel::EDUCATION_ELIGIBILITY;
$otherOn = in_array('other', $selectedGroups, true);
?>
<p class="sk-section-divider">Eligibility</p>
<div class="sk-form-row">
    <div>
        <label class="sk-form-label">Age range <span style="font-weight:400;color:#9aa0b4;">(optional)</span></label>
        <div style="display:flex;gap:8px;">
            <input class="sk-form-input" name="min_age" type="number" min="0" max="120" placeholder="Min age" value="<?= esc($minAge ?? '') ?>">
            <input class="sk-form-input" name="max_age" type="number" min="0" max="120" placeholder="Max age" value="<?= esc($maxAge ?? '') ?>">
        </div>
        <p class="sk-form-hint">Leave blank if age is not a requirement.</p>
    </div>
    <div>
        <label class="sk-form-label">Year level / educational status <span style="font-weight:400;color:#9aa0b4;">(optional)</span></label>
        <div class="sk-elig-grid">
            <?php foreach ($options as $value => $label): ?>
                <label class="sk-elig-option">
                    <input type="checkbox" name="eligibility_groups[]" value="<?= esc($value) ?>" <?= in_array($value, $selectedGroups, true) ? 'checked' : '' ?> <?= $value === 'other' ? 'data-eligibility-other-toggle' : '' ?>>
                    <?= esc($label) ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div id="eligibilityOtherWrap" style="margin-top:8px;" <?= $otherOn ? '' : 'hidden' ?>>
            <input class="sk-form-input" id="eligibilityOtherInput" name="eligibility_other" type="text" maxlength="200" placeholder="Specify the other eligible group" value="<?= esc($eligibilityOther) ?>" <?= $otherOn ? '' : 'disabled' ?>>
        </div>
        <p class="sk-form-hint">Residents are matched from their census or SK youth education record.</p>
    </div>
</div>
