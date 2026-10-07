<?php
$reqRows = $reqRows ?? [['type' => 'document', 'label' => '']];
$suggestions = \App\Models\BarangayActivityModel::REQUIREMENT_LABEL_SUGGESTIONS;
$blockId = $requirementBlockId ?? 'activityRequirementsBlock';
$cleanupSelected = ! empty($cleanupSelected);
?>
<div id="<?= esc($blockId, 'attr') ?>" <?= $cleanupSelected ? 'hidden' : '' ?>>
    <p class="sk-section-divider">Upload requirements</p>
    <datalist id="reqLabelSuggestions">
        <?php foreach ($suggestions as $suggestion): ?>
            <option value="<?= esc($suggestion) ?>"></option>
        <?php endforeach; ?>
    </datalist>
    <div data-req-list class="sk-req-list">
        <?php foreach ($reqRows as $row): ?>
            <div class="sk-req-row">
                <select class="sk-form-input" name="req_type[]" aria-label="File type" <?= $cleanupSelected ? 'disabled' : '' ?>>
                    <option value="document" <?= ($row['type'] ?? '') !== 'image' ? 'selected' : '' ?>>Document</option>
                    <option value="image" <?= ($row['type'] ?? '') === 'image' ? 'selected' : '' ?>>Image</option>
                </select>
                <input class="sk-form-input" name="req_label[]" type="text" maxlength="120" list="reqLabelSuggestions" placeholder="e.g. Birth Certificate, Valid ID" value="<?= esc($row['label'] ?? '') ?>" <?= $cleanupSelected ? 'disabled' : '' ?>>
                <button type="button" class="db-btn db-btn--outline db-btn--sm" data-req-remove aria-label="Remove requirement" <?= $cleanupSelected ? 'disabled' : '' ?>>&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="db-btn db-btn--outline db-btn--sm sk-req-add" data-req-add <?= $cleanupSelected ? 'disabled' : '' ?>>
        <i class="fas fa-plus"></i> Add requirement
    </button>
    <p class="sk-form-hint" style="margin-bottom:16px;">Choose Document or Image, then type the specific file residents must submit.</p>
</div>
