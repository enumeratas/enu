(function () {
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
        return;
    }

    var dismissedUntil = Number(localStorage.getItem('bisPwaInstallDismissed') || '0');
    if (dismissedUntil > Date.now()) {
        return;
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js?v=9', { updateViaCache: 'none' }).catch(function () {});
        });
    }

    var style = document.createElement('style');
    style.textContent = ''
        + '#bisPwaInstall{position:fixed;left:16px;right:16px;bottom:16px;z-index:4000;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;max-width:520px;margin:0 auto;padding:14px 16px;background:#16325c;color:#fff;border-top:3px solid #e0b32a;box-shadow:0 16px 40px rgba(11,28,54,.28);font-family:Inter,Poppins,sans-serif;}'
        + '#bisPwaInstall .bis-pwa-copy{display:flex;flex-direction:column;gap:2px;min-width:0;}'
        + '#bisPwaInstall strong{font-size:14px;}'
        + '#bisPwaInstall span{font-size:12px;line-height:1.4;color:rgba(255,255,255,.82);}'
        + '#bisPwaInstall .bis-pwa-actions{display:flex;gap:8px;}'
        + '#bisPwaInstall button{border:0;padding:8px 12px;font-size:13px;font-weight:700;cursor:pointer;}'
        + '#bisPwaInstall .bis-pwa-install{background:#e0b32a;color:#16325c;}'
        + '#bisPwaInstall .bis-pwa-later{background:transparent;color:#fff;}';
    document.head.appendChild(style);

    var promptEvent = null;

    function hideBanner() {
        var banner = document.getElementById('bisPwaInstall');
        if (banner) {
            banner.remove();
        }
    }

    function dismiss() {
        localStorage.setItem('bisPwaInstallDismissed', String(Date.now() + 7 * 24 * 60 * 60 * 1000));
        hideBanner();
    }

    function showBanner(message, installLabel, onInstall) {
        if (document.getElementById('bisPwaInstall')) {
            return;
        }
        var banner = document.createElement('div');
        banner.id = 'bisPwaInstall';
        banner.setAttribute('role', 'dialog');
        banner.setAttribute('aria-label', 'Install Bacolod BIS');
        banner.innerHTML = ''
            + '<div class="bis-pwa-copy"><strong>Install Bacolod BIS</strong><span>' + message + '</span></div>'
            + '<div class="bis-pwa-actions"><button type="button" class="bis-pwa-install">' + installLabel + '</button><button type="button" class="bis-pwa-later">Not now</button></div>';
        document.body.appendChild(banner);
        banner.querySelector('.bis-pwa-later').addEventListener('click', dismiss);
        banner.querySelector('.bis-pwa-install').addEventListener('click', onInstall);
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        promptEvent = event;
        showBanner('Your browser can install this app on this device.', 'Install', function () {
            if (!promptEvent) {
                return;
            }
            promptEvent.prompt();
            promptEvent.userChoice.then(function (choice) {
                promptEvent = null;
                if (choice.outcome === 'dismissed') {
                    dismiss();
                    return;
                }
                hideBanner();
            }).catch(function () {
                hideBanner();
            });
        });
    });

    window.addEventListener('appinstalled', function () {
        promptEvent = null;
        hideBanner();
    });

    var ios = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    if (ios) {
        window.addEventListener('load', function () {
            showBanner('Open the Share menu, then choose Add to Home Screen.', 'Got it', function () {
                dismiss();
            });
        });
    }
})();
