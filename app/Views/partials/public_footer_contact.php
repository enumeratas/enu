<?php

$contactSettings = (new \App\Models\BarangaySettingsModel())->getAll();
$publicAddress = trim((string) ($contactSettings['public_address'] ?? ''));
$publicPhone = trim((string) ($contactSettings['public_phone'] ?? ''));
$publicEmail = trim((string) ($contactSettings['public_email'] ?? ''));
$publicHours = trim((string) ($contactSettings['public_hours'] ?? ''));
$publicFacebook = trim((string) ($contactSettings['public_facebook'] ?? ''));
$publicTwitter = trim((string) ($contactSettings['public_twitter'] ?? ''));
$publicEmailLink = trim((string) ($contactSettings['public_email_link'] ?? ''));

$publicLink = static function (string $value): string {
    $value = trim($value);
    if ($value === '' || $value === '#') {
        return '';
    }
    if (preg_match('/^\s*javascript:/i', $value) === 1) {
        return '';
    }
    if (preg_match('/^(https?:|mailto:)/i', $value) === 1) {
        return $value;
    }
    if (str_contains($value, '@') && ! str_contains($value, ' ')) {
        return 'mailto:' . $value;
    }

    return 'https://' . ltrim($value, '/');
};

$footerPart = $footerPart ?? 'contact';
?>
<?php if ($footerPart === 'social'): ?>
    <div class="social-links">
        <?php $facebookUrl = $publicLink($publicFacebook); ?>
        <?php if ($facebookUrl !== ''): ?>
            <a href="<?= esc($facebookUrl) ?>" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
        <?php endif; ?>
        <?php $twitterUrl = $publicLink($publicTwitter); ?>
        <?php if ($twitterUrl !== ''): ?>
            <a href="<?= esc($twitterUrl) ?>" aria-label="Twitter" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
        <?php endif; ?>
        <?php $emailUrl = $publicLink($publicEmailLink !== '' ? $publicEmailLink : $publicEmail); ?>
        <?php if ($emailUrl !== ''): ?>
            <a href="<?= esc($emailUrl) ?>" aria-label="Email"><i class="fas fa-envelope"></i></a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <?php if ($publicAddress !== ''): ?>
        <div class="footer-contact-item">
            <i class="fas fa-map-marker-alt"></i>
            <span><?= esc($publicAddress) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($publicPhone !== ''): ?>
        <div class="footer-contact-item">
            <i class="fas fa-phone"></i>
            <span><?= esc($publicPhone) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($publicEmail !== ''): ?>
        <div class="footer-contact-item">
            <i class="fas fa-envelope"></i>
            <span><?= esc($publicEmail) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($publicHours !== ''): ?>
        <div class="footer-contact-item">
            <i class="fas fa-clock"></i>
            <span><?= esc($publicHours) ?></span>
        </div>
    <?php endif; ?>
<?php endif; ?>
