<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Barangay Management</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#16325c">
    <meta name="mobile-web-app-capable" content="yes">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #1a1d2e;
        }

        .site-republic {
            background: #16325c;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .02em;
            border-bottom: 3px solid #e0b32a;
        }

        .site-republic-inner,
        .site-nav-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding-left: 24px;
            padding-right: 24px;
        }

        .site-republic-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-top: 7px;
            padding-bottom: 7px;
        }

        .site-nav {
            background: #fff;
            border-bottom: 1px solid #e2e6ee;
        }

        .site-nav-inner {
            min-height: 68px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .site-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #16325c;
            margin-right: auto;
        }

        .site-brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .site-brand strong {
            display: block;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .site-brand small {
            display: block;
            margin-top: 1px;
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
        }

        .site-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .site-links a {
            color: #16325c;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .03em;
            text-transform: uppercase;
            padding: 8px 12px;
        }

        .site-links a:hover {
            background: #f4f6f9;
        }

        .site-signup {
            margin-left: 8px;
            background: #16325c;
            color: #fff !important;
            border-radius: 6px;
            padding: 8px 14px !important;
        }

        .site-signup:hover {
            background: #e0b32a !important;
            color: #16325c !important;
        }

        .site-menu {
            display: none;
            width: 42px;
            height: 42px;
            border: 1px solid #e2e6ee;
            background: #fff;
            color: #16325c;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .site-mobile {
            display: none;
            background: #fff;
            border-bottom: 1px solid #e2e6ee;
            padding: 8px 24px 16px;
        }

        .site-mobile.open {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .site-mobile a {
            color: #16325c;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 4px;
            border-bottom: 1px solid #f0f2f6;
        }

        .site-mobile .site-signup {
            margin: 8px 0 0;
            text-align: center;
            border-bottom: 0;
        }

        .login-page {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px 48px;
        }

        .login-shell {
            width: min(980px, 100%);
            min-height: 580px;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr);
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 48px rgba(22, 50, 92, 0.14);
        }

        .login-intro {
            position: relative;
            display: flex;
            align-items: flex-start;
            min-height: 580px;
            padding: 40px 32px 36px;
            color: #fff;
            overflow: hidden;
        }

        .login-intro-photo {
            position: absolute;
            inset: -20px;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            filter: blur(3px);
            transform: scale(1.03);
        }

        .login-intro-shade {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(11, 28, 54, .78) 0%, rgba(11, 28, 54, .55) 42%, rgba(11, 28, 54, .72) 100%);
        }

        .login-intro-body {
            position: relative;
            z-index: 1;
        }

        .login-intro-kicker {
            display: inline-block;
            margin-bottom: 12px;
            color: #ffd76b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .login-intro h2 {
            margin: 0 0 10px;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(2rem, 2.8vw, 2.6rem);
            font-weight: 700;
            line-height: 1.15;
        }

        .login-intro-place {
            margin: 0 0 18px;
            color: #ffd76b;
            font-size: 16px;
            font-weight: 600;
        }

        .login-intro p {
            margin: 0 0 28px;
            max-width: none;
            color: rgba(255, 255, 255, .94);
            font-family: "Source Serif 4", Georgia, "Times New Roman", serif;
            font-size: 26px;
            font-weight: 400;
            line-height: 1.45;
        }

        .login-intro ul {
            list-style: none;
            display: grid;
            gap: 16px;
        }

        .login-intro li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 17px;
            color: rgba(255, 255, 255, .92);
        }

        .login-intro li i {
            width: 16px;
            color: #ffd76b;
            text-align: center;
        }

        .login-card {
            background: #fff;
            border-radius: 0;
            padding: 40px 36px;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 28px;
        }

        .login-logo h1 {
            font-size: 18px;
            font-weight: 700;
            color: #1d2448;
        }

        .login-logo p {
            font-size: 13px;
            color: #9aa0b4;
            margin-top: 2px;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .alert--error {
            background: #fff0f1;
            color: #c0392b;
            border: 1px solid #fad4d4;
        }

        .alert--success {
            background: #f0faf6;
            color: #1a7a55;
            border: 1px solid #c3e8d8;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #4a5068;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e2e5ef;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1a1d2e;
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input::placeholder {
            color: #b0b6cc;
        }

        .form-group input:focus {
            border-color: #1d2448;
            box-shadow: 0 0 0 3px rgba(29, 36, 72, 0.08);
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            padding-right: 44px;
        }

        .eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #b0b6cc;
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
            transition: color 0.2s;
        }

        .eye-btn:hover {
            color: #1d2448;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #1d2448;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.15s;
        }

        .submit-btn:hover {
            background: #2e3a6e;
            transform: translateY(-1px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn.is-busy,
        .submit-btn.is-busy:hover,
        .submit-btn:disabled {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #1d2448;
            cursor: not-allowed;
            pointer-events: none;
            opacity: .85;
            transform: none;
        }

        .signin-spin {
            width: 15px;
            height: 15px;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: signin-spin .7s linear infinite;
        }

        @keyframes signin-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .remember-row {
            display: flex;
            align-items: center;
            margin: 2px 0 12px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #4a5068;
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: #1d2448;
            cursor: pointer;
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #9aa0b4;
        }

        .login-footer a {
            color: #1d2448;
            font-weight: 500;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .divider {
            height: 1px;
            background: #eef0f6;
            margin: 16px 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #9aa0b4;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #1d2448;
        }

        @media (max-width: 768px) {
            .site-republic-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }

            .site-links {
                display: none;
            }

            .site-menu {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        }

        @media (max-width: 860px) {
            .login-page {
                align-items: stretch;
                padding: 16px 12px 28px;
            }

            .login-shell {
                grid-template-columns: 1fr;
                min-height: 0;
            }

            .login-intro {
                align-items: flex-start;
                min-height: 0;
                padding: 18px 18px 16px;
            }

            .login-intro h2 {
                font-size: 1.45rem;
                margin-bottom: 0;
            }

            .login-intro-kicker {
                margin-bottom: 6px;
            }

            .login-intro ul {
                display: none;
            }

            .login-intro p {
                display: block;
                margin: 8px 0 0;
                font-size: 14px;
                line-height: 1.45;
            }

            .login-intro .login-intro-place {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 20px;
            }

            .login-intro {
                min-height: 0;
                padding: 14px 16px 12px;
            }

            .login-intro h2 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>

<body>
    <header class="site-top">
        <div class="site-republic">
            <div class="site-republic-inner">
                <span>Republic of the Philippines</span>
                <span>Province of Camarines Sur</span>
            </div>
        </div>
        <div class="site-nav">
            <div class="site-nav-inner">
                <a class="site-brand" href="/">
                    <img src="/bacolod.png" alt="Seal of Barangay Bacolod">
                    <span>
                        <strong>Barangay Bacolod</strong>
                        <small>Bato, Camarines Sur</small>
                    </span>
                </a>
                <nav class="site-links" aria-label="Site">
                    <a href="/">Home</a>
                    <a href="/events">Events</a>
                    <a href="/faqs">FAQs</a>
                    <a class="site-signup" href="/signup">Sign Up</a>
                </nav>
                <button type="button" class="site-menu" id="siteMenuBtn" aria-label="Open menu" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <div class="site-mobile" id="siteMobile">
            <a href="/">Home</a>
            <a href="/events">Events</a>
            <a href="/faqs">FAQs</a>
            <a class="site-signup" href="/signup">Sign Up</a>
        </div>
    </header>

    <div class="login-page">
    <div class="login-shell">
        <aside class="login-intro" aria-label="About the system">
            <span class="login-intro-photo" style="background-image:url('/image-hero/hero.png');"></span>
            <span class="login-intro-shade" aria-hidden="true"></span>
            <div class="login-intro-body">
                <span class="login-intro-kicker">Barangay Information System</span>
                <h2>Services for every household in Bacolod</h2>
                <p class="login-intro-place"><i class="fas fa-map-marker-alt"></i> Bato, Camarines Sur</p>
                <p>Sign in to request documents, follow updates, and use the services available to your account. Public events and office information stay open without a login.</p>
                <ul>
                    <li><i class="fas fa-certificate"></i> Request clearances and certificates</li>
                    <li><i class="fas fa-calendar-alt"></i> View barangay events and appointments</li>
                    <li><i class="fas fa-comments"></i> Send a concern to the barangay office</li>
                </ul>
            </div>
        </aside>
        <div class="login-card">

            <div class="login-logo">
                <h1>Barangay Management</h1>
                <p>Sign in to your account</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert--error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert--success">
                    <i class="fas fa-check-circle"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="eye-btn" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="remember" for="remember_me">
                        <input type="checkbox" id="remember_me" name="remember_me" value="1">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="submit-btn" id="signInBtn">Sign In</button>
            </form>

            <div class="login-footer" style="margin-top:14px;">
                <a href="/forgot-password">Forgot password?</a>
            </div>

            <div class="divider"></div>

            <div class="login-footer">
                Don't have an account? <a href="/signup">Create account</a>
            </div>

            <div style="text-align:center; margin-top:14px;">
                <a href="/" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to home
                </a>
            </div>

        </div>
    </div>
    </div>

    <script>
        document.getElementById('siteMenuBtn').addEventListener('click', function () {
            const menu = document.getElementById('siteMobile');
            const open = menu.classList.toggle('open');
            this.setAttribute('aria-expanded', open ? 'true' : 'false');
            this.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            this.innerHTML = open ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js?v=10', { updateViaCache: 'none' }).catch(function () {});
        }

        document.getElementById('signInBtn').closest('form').addEventListener('submit', function (event) {
            const form = event.currentTarget;
            const button = document.getElementById('signInBtn');
            if (button.dataset.busy === '1') {
                event.preventDefault();
                return;
            }
            event.preventDefault();
            button.dataset.busy = '1';
            button.classList.add('is-busy');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.innerHTML = '<span class="signin-spin" aria-hidden="true"></span><span>Signing in</span>';
            window.setTimeout(function () {
                form.submit();
            }, 450);
        });

        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
    <script src="/js/pwa-install.js?v=1"></script>
</body>

</html>