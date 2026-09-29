<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQs - Barangay Bacolod BIS</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    :root {
      --navy: #1d2448;
      --navy-mid: #2e3a6e;
      --navy-dark: #0f1117;
      --accent: #5b6fd6;
      --accent-light: #7b8fe8;
      --white: #ffffff;
      --gray-light: #f4f6fb;
      --gray-mid: #e8ecf4;
      --text-dark: #1a1d2e;
      --text-muted: #6b7280;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text-dark);
      overflow-x: hidden;
    }

    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      background: #fff;
      transition: box-shadow .3s;
    }

    .navbar.scrolled {
      box-shadow: 0 2px 20px rgba(0, 0, 0, .10);
    }

    .nav-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
      height: 68px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .nav-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }

    .nav-brand img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: contain;
    }

    .nav-brand span {
      font-size: 16px;
      font-weight: 700;
      color: var(--navy);
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .nav-links a {
      font-size: 14px;
      font-weight: 500;
      color: var(--text-dark);
      text-decoration: none;
      padding: 8px 14px;
      border-radius: 8px;
      transition: background .2s, color .2s;
    }

    .nav-links a:hover,
    .nav-links a.active {
      background: var(--gray-light);
      color: var(--navy);
    }

    .nav-divider {
      width: 1px;
      height: 24px;
      background: var(--gray-mid);
      margin: 0 8px;
    }

    .btn-login {
      background: var(--navy);
      color: #fff !important;
      padding: 9px 20px !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
    }

    .btn-login:hover {
      background: var(--navy-mid) !important;
    }

    .btn-signup {
      border: 2px solid var(--navy);
      color: var(--navy) !important;
      padding: 7px 18px !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
    }

    .btn-signup:hover {
      background: var(--navy);
      color: #fff !important;
    }

    .hamburger {
      display: none;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      padding: 8px;
      border: none;
      background: none;
    }

    .hamburger span {
      display: block;
      width: 24px;
      height: 2px;
      background: var(--navy);
      border-radius: 2px;
    }

    .mobile-menu {
      display: none;
      position: fixed;
      top: 68px;
      left: 0;
      right: 0;
      background: #fff;
      border-top: 1px solid var(--gray-mid);
      padding: 16px 24px;
      z-index: 999;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
    }

    .mobile-menu.open {
      display: block;
    }

    .mobile-menu a {
      display: block;
      padding: 12px 0;
      font-size: 15px;
      font-weight: 500;
      color: var(--text-dark);
      text-decoration: none;
      border-bottom: 1px solid var(--gray-mid);
    }

    .mobile-menu a:last-child {
      border-bottom: none;
    }

    .mobile-menu .btn-login,
    .mobile-menu .btn-signup {
      display: block;
      text-align: center;
      margin-top: 8px;
      padding: 12px !important;
      border-radius: 8px !important;
    }

    .page-hero {
      background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 100%);
      padding: 100px 24px 60px;
      text-align: center;
      margin-top: 68px;
    }

    .page-hero h1 {
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 800;
      color: #fff;
      margin-bottom: 12px;
    }

    .page-hero p {
      font-size: 16px;
      color: rgba(255, 255, 255, .75);
      max-width: 520px;
      margin: 0 auto;
    }

    .faq-main {
      max-width: 860px;
      margin: 0 auto;
      padding: 60px 24px;
    }

    .search-wrap {
      position: relative;
      margin-bottom: 48px;
    }

    .search-wrap input {
      width: 100%;
      padding: 14px 20px 14px 50px;
      border: 2px solid var(--gray-mid);
      border-radius: 12px;
      font-size: 15px;
      font-family: 'Poppins', sans-serif;
      color: var(--text-dark);
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .search-wrap input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(91, 111, 214, .1);
    }

    .search-wrap i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 16px;
    }

    .faq-category {
      margin-bottom: 48px;
    }

    .faq-category-title {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 18px;
      font-weight: 700;
      color: var(--navy);
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid var(--gray-mid);
    }

    .faq-category-title i {
      width: 36px;
      height: 36px;
      background: var(--navy);
      color: #fff;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .accordion-item {
      border: 1px solid var(--gray-mid);
      border-radius: 12px;
      margin-bottom: 10px;
      overflow: hidden;
      transition: box-shadow .2s;
    }

    .accordion-item:hover {
      box-shadow: 0 4px 16px rgba(29, 36, 72, .07);
    }

    .accordion-item.hidden {
      display: none;
    }

    .accordion-header {
      padding: 18px 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      background: #fff;
      user-select: none;
    }

    .accordion-header:hover {
      background: var(--gray-light);
    }

    .accordion-question {
      font-size: 15px;
      font-weight: 600;
      color: var(--navy);
      flex: 1;
    }

    .accordion-icon {
      width: 28px;
      height: 28px;
      border-radius: 6px;
      background: var(--gray-light);
      color: var(--navy);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      flex-shrink: 0;
      transition: background .2s, transform .3s;
    }

    .accordion-item.open .accordion-icon {
      background: var(--navy);
      color: #fff;
      transform: rotate(180deg);
    }

    .accordion-body {
      max-height: 0;
      overflow: hidden;
      transition: max-height .35s ease, padding .35s ease;
    }

    .accordion-item.open .accordion-body {
      max-height: 400px;
    }

    .accordion-body-inner {
      padding: 0 20px 20px;
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.8;
      border-top: 1px solid var(--gray-mid);
    }

    .no-results {
      text-align: center;
      padding: 48px 24px;
      color: var(--text-muted);
    }

    .no-results i {
      font-size: 40px;
      margin-bottom: 12px;
      color: var(--gray-mid);
    }

    footer {
      background: var(--navy-dark);
      padding: 60px 24px 0;
    }

    .footer-inner {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1.5fr 1fr 1.2fr;
      gap: 48px;
      padding-bottom: 48px;
    }

    .footer-brand img {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      margin-bottom: 14px;
    }

    .footer-brand h3 {
      font-size: 17px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 8px;
    }

    .footer-brand p {
      font-size: 13px;
      color: rgba(255, 255, 255, .5);
      line-height: 1.7;
      margin-bottom: 20px;
    }

    .social-links {
      display: flex;
      gap: 10px;
    }

    .social-links a {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: rgba(255, 255, 255, .08);
      color: rgba(255, 255, 255, .6);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      text-decoration: none;
      transition: background .2s, color .2s;
    }

    .social-links a:hover {
      background: var(--accent);
      color: #fff;
    }

    .footer-col h4 {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 18px;
      text-transform: uppercase;
      letter-spacing: .5px;
    }

    .footer-col ul {
      list-style: none;
    }

    .footer-col ul li {
      margin-bottom: 10px;
    }

    .footer-col ul li a {
      font-size: 13px;
      color: rgba(255, 255, 255, .5);
      text-decoration: none;
      transition: color .2s;
    }

    .footer-col ul li a:hover {
      color: #fff;
    }

    .footer-contact-item {
      display: flex;
      gap: 12px;
      margin-bottom: 14px;
    }

    .footer-contact-item i {
      color: var(--accent);
      font-size: 14px;
      margin-top: 2px;
      flex-shrink: 0;
    }

    .footer-contact-item span {
      font-size: 13px;
      color: rgba(255, 255, 255, .5);
      line-height: 1.6;
    }

    .footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, .08);
      padding: 20px 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .footer-bottom p {
      font-size: 12px;
      color: rgba(255, 255, 255, .35);
    }

    .footer-bottom-links {
      display: flex;
      gap: 20px;
    }

    .footer-bottom-links a {
      font-size: 12px;
      color: rgba(255, 255, 255, .35);
      text-decoration: none;
      transition: color .2s;
    }

    .footer-bottom-links a:hover {
      color: rgba(255, 255, 255, .7);
    }

    @media(max-width:768px) {
      .nav-links {
        display: none;
      }

      .hamburger {
        display: flex;
      }

      .footer-inner {
        grid-template-columns: 1fr;
        gap: 32px;
      }

      .footer-bottom {
        flex-direction: column;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <?php $isLoggedIn = (bool) session()->get('user_id'); ?>
  <nav class="navbar" id="navbar">
    <div class="nav-inner">
      <a href="/" class="nav-brand"><img src="/bacolod.png" alt="Bacolod Logo"><span>Bacolod BIS</span></a>
      <div class="nav-links">
        <a href="/">Home</a><a href="/#services">Services</a><a href="/#about">About</a><a href="/events">Events</a><a href="/faqs" class="active">FAQs</a>
        <?php if (! $isLoggedIn): ?>
          <div class="nav-divider"></div>
          <a href="/login" class="btn-login">Login</a><a href="/signup" class="btn-signup">Sign Up</a>
        <?php endif; ?>
      </div>
      <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
    </div>
  </nav>
  <div class="mobile-menu" id="mobileMenu">
    <a href="/">Home</a><a href="/#services">Services</a><a href="/#about">About</a><a href="/events">Events</a><a href="/faqs">FAQs</a>
    <?php if (! $isLoggedIn): ?>
      <a href="/login" class="btn-login">Login</a><a href="/signup" class="btn-signup">Sign Up</a>
    <?php endif; ?>
  </div>

  <div class="page-hero">
    <h1><i class="fas fa-question-circle" style="margin-right:12px;opacity:.8;"></i>Frequently Asked Questions</h1>
    <p>Find answers to common questions about the Barangay Bacolod Information System and our services.</p>
  </div>

  <main class="faq-main">
    <div class="search-wrap">
      <i class="fas fa-search"></i>
      <input type="text" id="faqSearch" placeholder="Search questions..." oninput="filterFAQs(this.value)">
    </div>

    <div id="noResults" class="no-results" style="display:none;">
      <i class="fas fa-search"></i>
      <p>No questions found matching your search.</p>
    </div>

    <!-- General -->
    <div class="faq-category" data-category="general">
      <div class="faq-category-title"><i class="fas fa-info"></i> General</div>
      <div class="accordion-item" data-question="what is barangay information system bis">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">What is the Barangay Information System (BIS)?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">The Barangay Information System (BIS) is the official digital platform of Barangay Bacolod, Bato, Camarines Sur. It allows residents to request barangay documents, file blotter reports, and access community information online — reducing the need for in-person visits to the barangay hall. Barangay officials use the system to manage the census, process requests, handle blotter cases, publish community schedules, and generate demographic reports.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="who can use the bis portal residents roles captain secretary sk ">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Who can use the BIS portal?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">
            The portal serves six user roles:
            <ul style="margin-top:8px;padding-left:18px;">
              <li><strong>Residents</strong> — Request documents, view notifications, use eligible SK profiling features, and manage their profile.</li>
              <li><strong>SK (Sangguniang Kabataan)</strong> — Manage youth profiles, SK programs, registrations, and related reports.</li>
              <li><strong>Barangay Council</strong> — Review census information, access community programs and SK profiling, and request eligible documents.</li>
              <li><strong>Captain</strong> — Approve/reject clearances and accounts, manage the census, oversee blotter cases, publish schedules, and view demographic reports.</li>
              <li><strong>Secretary</strong> — Full administrative access including census management, clearance processing, blotter management, account management, and report generation.</li>
            </ul>
            Public blotter filing is currently handled by the barangay secretary through the administrative portal.
          </div>
        </div>
      </div>
      <div class="accordion-item" data-question="is the bis portal free to use cost">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Is the BIS portal free to use?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Yes, creating an account and using the BIS portal is completely free. However, some barangay documents may have standard processing fees as mandated by local ordinance. These fees are paid at the barangay hall when you pick up your document.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="what services are available online barangay features census calendar schedule reports sk">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">What services are available through the portal?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">
            The BIS portal currently provides the following services:
            <ul style="margin-top:8px;padding-left:18px;">
              <li><strong>Clearance and document requests</strong> — Residents and eligible council users can request and track available documents.</li>
              <li><strong>Blotter management</strong> — The Secretary can create, update, schedule, summon, and generate documents for blotter cases; authorized officials can review case status.</li>
              <li><strong>Census management</strong> — Authorized officials can create, update, review, approve, separate, and export household records.</li>
              <li><strong>Community calendar</strong> — Officials can publish and manage events, appointments, and hearings.</li>
              <li><strong>SK youth profiling and programs</strong> — Eligible residents, council users, and SK officials can access the profiling and program workflows available to their role.</li>
              <li><strong>Notifications, chatbot, and reports</strong> — Users can receive activity updates and use role-specific support, dashboard, and report features.</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="accordion-item" data-question="what are the office hours barangay hall schedule">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">What are the barangay hall office hours?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">The Barangay Hall of Bacolod, Bato, Camarines Sur is open Monday to Friday, 8:00 AM to 5:00 PM. The online portal is available 24/7 for submitting requests, but processing is done during office hours only.</div>
        </div>
      </div>
    </div>

    <!-- Offline access -->
    <div class="faq-category" data-category="offline">
      <div class="faq-category-title"><i class="fas fa-cloud-download-alt"></i> Offline Use</div>
      <div class="accordion-item" data-question="offline mode offline data sync internet connection clearance blotter transaction">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Can I continue a transaction if my internet connection is interrupted?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Yes, after you have successfully logged in while connected to the internet. If the connection is interrupted while you are completing an eligible Clearance or Blotter transaction, the information entered can be saved on the device and synchronized automatically when the connection returns. Offline support is limited to these transactions. You cannot log in or begin using the BIS for the first time without an internet connection.</div>
        </div>
      </div>
    </div>

    <!-- Account & Registration -->
    <div class="faq-category" data-category="account">
      <div class="faq-category-title"><i class="fas fa-user"></i> Account &amp; Registration</div>
      <div class="accordion-item" data-question="how to create account register sign up resident sk">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">How can I create an account?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">
            Residents and SK (Sangguniang Kabataan) members can self-register through the Sign Up page (<strong>/signup</strong>). Here's how the process works:
            <br><br>
            <strong>Step 1 — Fill out the form.</strong> Provide your full name, email address, a unique username, your desired password, and — for residents — your Household Number as recorded in the barangay census. Your full name must exactly match the name on record under that household.
            <br><br>
            <strong>Step 2 — Verify your email.</strong> A 6-digit One-Time Password (OTP) is sent to your email address. Enter it on the verification page within 15 minutes. You can request a new code if it expires.
            <br><br>
            <strong>Step 3 — Wait for approval.</strong> Once your email is verified, your account status becomes <em>Pending</em>. The Barangay Captain or Secretary will review and approve it, typically within 1–3 business days. You will receive an email once your account is approved and active.
            <br><br>
            <strong>Captain, Secretary, Council,and SK accounts</strong> are not self-registered. They are created directly by the System Administrator.
          </div>
        </div>
      </div>
      <div class="accordion-item" data-question="who can self register sign up account roles">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Who can register through the Sign Up page?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Only <strong>Residents</strong> members can create their own accounts via the public Sign Up page. Accounts for the Barangay Captain, Secretary, Council, and SK are created internally by authorized officials and are not available for public self-registration.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="household number census verification resident registration">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Where can I find my Household Number?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Your Household Number is assigned during the barangay census. You can find it by visiting the barangay hall and asking the Secretary to look it up. The number must match an existing entry in the census for your registration to proceed.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="forgot password reset account login otp">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">What should I do if I forgot my password?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">On the login page, click <strong>"Forgot password?"</strong> and enter your registered email address. A 6-digit OTP will be sent to your inbox. Enter the code to verify your identity, then set a new password. The code is valid for 15 minutes — you can request a new one if needed. If you no longer have access to your registered email, please visit the barangay hall in person for assistance.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="how long account approval pending waiting">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">How long does account approval take?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Account approval typically takes 1–3 business days. The Barangay Captain or Secretary reviews each registration to confirm residency and census details. You will receive an email notification once your account is approved. If your account has not been approved after 3 business days, please contact the barangay hall directly.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="can i update my profile information personal details avatar password">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Can I update my profile and change my password?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Yes. Once logged in, go to your Settings page to update your profile details, upload a profile photo (avatar), and change your password. Password changes require OTP verification sent to your registered email for security. For changes to your name or address in the official census record, please visit the barangay hall with a valid ID.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="is my personal data safe secure privacy">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Is my personal data safe on the portal?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Yes. The BIS portal complies with the Data Privacy Act of 2012 (RA 10173). Passwords are stored using secure hashing and are never saved in plain text. Your personal information is only accessible to authorized barangay officials for legitimate purposes. We do not share your data with third parties without your consent. Please review our <a href="/privacy-policy" style="color:var(--accent);">Privacy Policy</a> for full details.</div>
        </div>
      </div>
    </div>

    <!-- Documents & Clearances -->
    <div class="faq-category" data-category="documents">
      <div class="faq-category-title"><i class="fas fa-file-alt"></i> Documents &amp; Clearances</div>
      <div class="accordion-item" data-question="how to request barangay clearance certificate">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">How do I request a Barangay Clearance?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Log in to your account and open the Clearance section. Select the document type, identify the household member the document is for, enter the purpose and any notes, then submit the request. Available options include Barangay Clearance, Certificates of Residency, Indigency and Good Moral, First Time Job Seekers, Solo Parent Certificate, Business Permit Clearance, Medical Assistance, PhilHealth / SSS / GSIS, Scholarship Application, Employment / Job Application, and Other Document. Track the request and its approval or release status from your dashboard.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="what documents requirements needed clearance">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">What are the requirements for requesting documents?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">You need an approved, email-verified BIS account and a household record linked to your profile. First Time Job Seekers requests are available to all users in the Clearance request form; the system does not exclude a requester based on the occupation recorded in the census. Other documents may have specific requirements, such as a validated Solo Parent record or household information for eligibility checks. Any supporting documents or payment arrangements are handled according to barangay instructions.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="how long processing time document clearance">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">How long does document processing take?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Processing time depends on the document type and barangay review. Officials can approve, reject, or release a request, and may include remarks when rejecting it. You can monitor the current status and view available updates from your dashboard; clearance status updates may also be sent by email.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="fees cost barangay clearance certificate payment">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Are there fees for barangay documents?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Fees and payment arrangements depend on the document and current barangay policy. The portal may display configured document fees, but it does not guarantee a single fee for every request. Contact the barangay office for the current amount and payment instructions.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="can i cancel request clearance document">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Can I cancel a document request?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Yes. You can cancel a pending request from the Clearance page. Cancellation is not available after the request has moved beyond the pending state, so contact the barangay office if you need assistance with an approved, rejected, or released request.</div>
        </div>
      </div>
    </div>

    <!-- Blotter Reports -->
    <div class="faq-category" data-category="blotter">
      <div class="faq-category-title"><i class="fas fa-file-signature"></i> Blotter Reports</div>
      <div class="accordion-item" data-question="how to file blotter report incident complaint">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">How do I file a blotter report?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Blotter creation is currently handled by the barangay secretary through the administrative Blotter page. The form records complainant details, respondent name, incident type, incident date, contact information, and the narrative. Authorized officials can then review the case, update its status, manage hearings, send summons, and generate case documents.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="what happens after blotter report filed process status summons">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">What happens after I file a blotter report?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">After creation, authorized officials review the report and may update its status, add narratives, schedule or reschedule a hearing, issue a summons, and generate a letter or certificate. The available status values are managed by the barangay workflow. Hearing dates and other case activity are reflected in the administrative calendar and case records.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="is blotter report confidential anonymous privacy official record">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">Is my blotter report kept confidential?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">Blotter reports are official barangay records and are treated with confidentiality. Access is restricted to authorized barangay officials only. However, as official records, they may be disclosed in legal proceedings or upon lawful order. We recommend providing accurate information, as filing false reports may have legal consequences.</div>
        </div>
      </div>
    </div>

    <!-- SK Youth -->
    <div class="faq-category" data-category="sk">
      <div class="faq-category-title"><i class="fas fa-users"></i> SK &amp; Youth</div>
      <div class="accordion-item" data-question="sk sangguniang kabataan account register youth">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">How do SK members create an account?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">SK members can register through the same public Sign Up page (<strong>/signup</strong>) as residents. The process is identical: fill in your details, verify your email via OTP, and wait for approval by the Barangay Captain or Secretary. No household number is required for SK registration.</div>
        </div>
      </div>
      <div class="accordion-item" data-question="sk youth profiling what is it census records">
        <div class="accordion-header" onclick="toggleAccordion(this)">
          <span class="accordion-question">What is the SK Youth Profiling module?</span>
          <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="accordion-body">
          <div class="accordion-body-inner">The SK Youth Profiling module uses barangay census records to support youth profiles for ages 15 to 30. Eligible residents and council users can submit or update their own profile, or a parent can submit a profile for an eligible minor child in the household. SK officials can manage youth records, registrations, programs, and related reports according to their role.</div>
        </div>
      </div>
    </div>
  </main>

  <footer>
    <div class="footer-inner">
      <div class="footer-brand">
        <img src="/bacolod.png" alt="Bacolod Logo">
        <h3>Barangay Bacolod BIS</h3>
        <p>Official Barangay Information System of Barangay Bacolod, Bato, Camarines Sur.</p>
        <?php $footerPart = 'social'; include APPPATH . 'Views/partials/public_footer_contact.php'; ?>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="/">Home</a></li>
          <li><a href="/#services">Services</a></li>
          <li><a href="/faqs">FAQs</a></li>
          <li><a href="/privacy-policy">Privacy Policy</a></li>
          <li><a href="/terms">Terms of Use</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact Us</h4>
        <?php $footerPart = 'contact'; include APPPATH . 'Views/partials/public_footer_contact.php'; ?>
      </div>
    </div>
    <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Barangay Bacolod, Bato, Camarines Sur. All rights reserved.</p>
        <div class="footer-bottom-links">
          <a href="/privacy-policy">Privacy Policy</a>
          <a href="/terms">Terms of Use</a>
          <a href="/faqs">FAQs</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    window.addEventListener('scroll', function() {
      var n = document.getElementById('navbar');
      n.classList.toggle('scrolled', window.scrollY > 10);
    });
    document.getElementById('hamburger').addEventListener('click', function() {
      document.getElementById('mobileMenu').classList.toggle('open');
    });

    function toggleAccordion(header) {
      var item = header.closest('.accordion-item');
      var isOpen = item.classList.contains('open');
      document.querySelectorAll('.accordion-item.open').forEach(function(i) {
        i.classList.remove('open');
      });
      if (!isOpen) item.classList.add('open');
    }

    function filterFAQs(q) {
      var query = q.toLowerCase().trim();
      var items = document.querySelectorAll('.accordion-item');
      var anyVisible = false;
      items.forEach(function(item) {
        var question = item.getAttribute('data-question') || '';
        var bodyText = item.querySelector('.accordion-body-inner').textContent.toLowerCase();
        var match = !query || question.includes(query) || bodyText.includes(query);
        item.classList.toggle('hidden', !match);
        if (match) anyVisible = true;
      });
      document.querySelectorAll('.faq-category').forEach(function(cat) {
        var visible = cat.querySelectorAll('.accordion-item:not(.hidden)').length > 0;
        cat.style.display = visible ? '' : 'none';
      });
      document.getElementById('noResults').style.display = anyVisible ? 'none' : 'block';
    }
  </script>
</body>

</html>