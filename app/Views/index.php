<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Bacolod - Official Portal | Bato, Camarines Sur</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="/landing-gov.css?v=20260929k">
</head>

<body class="gov-site">

    <!-- ── TOAST FLASH MESSAGES ── -->
    <?php if (session()->getFlashdata('blotter_success')): ?>
        <div class="toast-container" id="toastContainer">
            <div class="toast success" id="toastMsg">
                <i class="fas fa-check-circle toast-icon"></i>
                <div class="toast-body">
                    <div class="toast-title">Blotter Submitted</div>
                    <div class="toast-msg"><?= esc(session()->getFlashdata('blotter_success')) ?></div>
                </div>
                <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fas fa-times"></i></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('blotter_error')): ?>
        <div class="toast-container" id="toastContainer">
            <div class="toast error" id="toastMsg">
                <i class="fas fa-exclamation-circle toast-icon"></i>
                <div class="toast-body">
                    <div class="toast-title">Submission Failed</div>
                    <div class="toast-msg"><?= esc(session()->getFlashdata('blotter_error')) ?></div>
                </div>
                <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fas fa-times"></i></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('concern_success')): ?>
        <div class="toast-container">
            <div class="toast success">
                <i class="fas fa-check-circle toast-icon"></i>
                <div class="toast-body">
                    <div class="toast-title">Concern Submitted</div>
                    <div class="toast-msg"><?= esc(session()->getFlashdata('concern_success')) ?></div>
                </div>
                <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fas fa-times"></i></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('concern_error')): ?>
        <div class="toast-container">
            <div class="toast error">
                <i class="fas fa-exclamation-circle toast-icon"></i>
                <div class="toast-body">
                    <div class="toast-title">Submission Failed</div>
                    <div class="toast-msg"><?= esc(session()->getFlashdata('concern_error')) ?></div>
                </div>
                <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fas fa-times"></i></button>
            </div>
        </div>
    <?php endif; ?>

    <?php $isLoggedIn = (bool) session()->get('user_id'); ?>

    <!-- ── OFFICIAL HEADER ── -->
    <div class="gov-republic">
        <div class="gov-republic-inner">
            <span>Republic of the Philippines</span>
            <span>Province of Camarines Sur</span>
        </div>
    </div>
    <nav class="navbar" id="navbar">
        <div class="nav-inner">
            <a href="/" class="nav-logo" aria-label="Barangay Bacolod home">
                <img src="/bacolod.png" alt="Seal of Barangay Bacolod">
            </a>
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="#services">Services</a>
                <a href="#about">About</a>
                <a href="/events">Events</a>
                <a href="/faqs">FAQs</a>
                <?php if (! $isLoggedIn): ?>
                    <div class="nav-auth">
                        <a href="/login" class="btn-login">Login</a>
                        <a href="/signup" class="btn-signup">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
            <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobileMenu">
                <span></span><span></span><span></span>
            </button>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <a href="/">Home</a>
            <a href="#services">Services</a>
            <a href="#about">About</a>
            <a href="/events">Events</a>
            <a href="/faqs">FAQs</a>
            <?php if (! $isLoggedIn): ?>
                <a href="/login" class="btn-login">Login</a>
                <a href="/signup" class="btn-signup">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- ── WELCOME ── -->
    <section class="hero hero--photo">
        <span class="hero-photo" aria-hidden="true" style="background-image:url('/image-hero/hero.png');"></span>
        <span class="hero-vignette" aria-hidden="true"></span>
        <div class="hero-inner">
            <div class="hero-content">
                <div class="hero-badge"><i class="fas fa-landmark"></i> Office of the Barangay</div>
                <h1>Barangay Bacolod</h1>
                <p class="hero-place"><i class="fas fa-map-marker-alt"></i> Bato, Camarines Sur</p>
                <p class="hero-sub">The official portal for barangay services. Request documents, view the public calendar, send a concern, and sign in to your account.</p>
                <div class="hero-btns">
                    <a href="/login" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="#services" class="btn-outline-white"><i class="fas fa-th-large"></i> View Services</a>
                </div>
                <ul class="hero-metrics" aria-label="Barangay quick facts">
                    <li>
                        <strong>Mon – Fri</strong>
                        <span>8:00 AM – 5:00 PM</span>
                    </li>
                    <li>
                        <strong>Public Portal</strong>
                        <span>Documents, events, concerns</span>
                    </li>
                    <li>
                        <strong>Serving</strong>
                        <span>Every household in Bacolod</span>
                    </li>
                </ul>
            </div>
            <div class="hero-seal" aria-hidden="true">
                <img src="/hero-section.png" alt="Barangay Bacolod">
            </div>
        </div>
    </section>

    <!-- ── SERVICES ── -->
    <section class="services" id="services">
        <div class="services-inner">
            <div class="services-header fade-in">
                <span class="section-tag">What We Offer</span>
                <h2 class="section-title">Barangay Services</h2>
                <p class="section-sub">Explore public information, start an account, request eligible documents, and contact barangay officials online.</p>
            </div>
            <div class="services-grid">
                <div class="service-card fade-in">
                    <div class="service-icon blue"><i class="fas fa-certificate"></i></div>
                    <h3>Documents &amp; Clearances</h3>
                    <p>After logging in, request available barangay documents, including clearances and certificates, then monitor approval and release updates from your dashboard.</p>
                    <a href="/login" class="service-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon green"><i class="fas fa-file-signature"></i></div>
                    <h3>Blotter Case Management</h3>
                    <p>Authorized barangay officials create and manage blotter cases, hearings, summons, narratives, letters, and certificates through the administrative portal.</p>
                    <a href="/login" class="service-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon orange"><i class="fas fa-database"></i></div>
                    <h3>Census Management</h3>
                    <p>Authorized officials maintain household and member records, process census updates, manage household changes, and export census reports.</p>
                    <a href="/login" class="service-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon orange"><i class="fas fa-calendar-check"></i></div>
                    <h3>Concerns &amp; Appointments</h3>
                    <p>Send a concern or inquiry and optionally request an appointment with barangay officials for follow-up assistance.</p>
                    <a href="#concern-modal" class="service-link" onclick="openConcernModal(event)">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card fade-in">
                    <div class="service-icon blue"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Events Calendar</h3>
                    <p>See upcoming barangay activities on the public calendar. No account is required.</p>
                    <a href="/events" class="service-link">View Events <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- ── PUBLIC EVENTS ── -->
    <section class="services" id="events" style="background:#f7f8fc;">
        <div class="services-inner">
            <div class="services-header fade-in">
                <span class="section-tag">Open to everyone</span>
                <h2 class="section-title">Events Calendar</h2>
                <p class="section-sub">Upcoming barangay activities from the system calendar. You can view them here without logging in.</p>
            </div>
            <?php $publicEvents = $publicEvents ?? []; ?>
            <?php if ($publicEvents === []): ?>
                <p style="text-align:center;color:#6b7280;margin-bottom:18px;">There are no upcoming events on the calendar right now.</p>
            <?php else: ?>
                <div class="services-grid">
                    <?php foreach ($publicEvents as $event):
                        $when = date('F j, Y', strtotime($event['event_date']));
                        $timeLabel = '';
                        if (! empty($event['start_time']) && $event['start_time'] !== '00:00:00') {
                            $timeLabel = date('g:i A', strtotime($event['start_time']));
                        }
                    ?>
                        <article class="service-card fade-in" style="text-align:left;align-items:flex-start;">
                            <h3><?= esc($event['title']) ?></h3>
                            <p>
                                <?= esc(ucfirst((string) ($event['event_type'] ?? 'event'))) ?>
                                · <?= esc($when) ?>
                                <?php if ($timeLabel !== ''): ?> · <?= esc($timeLabel) ?><?php endif; ?>
                                <?php if (! empty($event['location'])): ?><br><?= esc($event['location']) ?><?php endif; ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p style="text-align:center;margin-top:22px;">
                <a href="/events" style="display:inline-flex;align-items:center;gap:8px;background:#1d2448;color:#fff;text-decoration:none;padding:12px 22px;border-radius:10px;font-weight:600;font-size:14px;"><i class="fas fa-calendar-alt"></i> Open the public calendar</a>
            </p>
        </div>
    </section>

    <!-- ── ABOUT ── -->
    <section class="about" id="about">
        <div class="about-inner">
            <div class="about-text fade-in">
                <span class="section-tag">About Us</span>
                <h2 class="section-title">Barangay Bacolod</h2>
                <p>Barangay Bacolod is a vibrant community in the municipality of Bato, Camarines Sur. Our Barangay Information System (BIS) is designed to modernize and streamline the delivery of public services to our residents.</p>
                <p>We are committed to transparent, role-based digital services that help residents contact the barangay, request documents, monitor updates, and keep household information accurate.</p>
                <div class="info-row">
                    <div class="info-row-icon"><i class="fas fa-bullseye"></i></div>
                    <div class="info-row-text">
                        <h4>Our Mission</h4>
                        <p>To provide efficient, transparent, and accessible barangay services that empower residents and foster community development in Bacolod, Bato, Camarines Sur.</p>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-row-icon"><i class="fas fa-eye"></i></div>
                    <div class="info-row-text">
                        <h4>Our Vision</h4>
                        <p>A progressive, digitally-enabled barangay where every resident has equal access to government services and participates actively in community governance.</p>
                    </div>
                </div>
            </div>
            <div class="features-grid fade-in">
                <div class="feature-card">
                    <i class="fas fa-bolt"></i>
                    <h4>Fast Service</h4>
                    <p>Clear online forms help route requests and concerns to the appropriate barangay workflow.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-lock"></i>
                    <h4>Secure Data</h4>
                    <p>Your personal information is protected with industry-standard security measures.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-eye"></i>
                    <h4>Transparent</h4>
                    <p>Residents can review document request updates and officials can monitor active barangay work.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-universal-access"></i>
                    <h4>Accessible</h4>
                    <p>Public information is available online, while account-based services require login and internet access to begin.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── HOW IT WORKS ── -->
    <section class="how">
        <div class="how-inner">
            <div class="how-header fade-in">
                <span class="section-tag">Simple Process</span>
                <h2 class="section-title">How It Works</h2>
                <p class="section-sub" style="margin:0 auto;">Use the public pages to learn about services, then sign in when a service requires an account.</p>
            </div>
            <div class="steps">
                <div class="step fade-in">
                    <div class="step-num">1</div>
                    <h3>Create and Verify</h3>
                    <p>Register as a resident or SK member, verify your email with the OTP, and wait for account approval.</p>
                </div>
                <div class="step fade-in">
                    <div class="step-num">2</div>
                    <h3>Use Your Role</h3>
                    <p>After approval, sign in and open the features available to your role, such as document requests, profiling, or official management tools.</p>
                </div>
                <div class="step fade-in">
                    <div class="step-num">3</div>
                    <h3>Track Updates</h3>
                    <p>Monitor request statuses and notifications in the portal. Processing and release follow barangay instructions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── CTA BANNER ── -->
    <section class="cta-banner">
        <div class="fade-in">
            <h2>Ready to access barangay services?</h2>
            <p>Create an approved account to access resident services, or send a concern and appointment request from the public page.</p>
            <a href="/signup" class="btn-primary" style="display:inline-flex;"><i class="fas fa-user-plus"></i> Get Started</a>
        </div>
    </section>

    <!-- ── FOOTER ── -->
    <footer>
        <div class="footer-inner">
            <div class="footer-brand">
                <img src="/bacolod.png" alt="Bacolod Logo">
                <h3>Barangay Bacolod</h3>
                <p>Official Barangay Information System of Barangay Bacolod, Bato, Camarines Sur. Serving our community with transparency and efficiency.</p>
                <?php $footerPart = 'social'; include APPPATH . 'Views/partials/public_footer_contact.php'; ?>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="/events">Events</a></li>
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

    <!-- ── BLOTTER INFORMATION MODAL ── -->
    <div class="modal-overlay" id="blotterModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fas fa-file-signature" style="color:var(--accent);margin-right:8px;"></i>Blotter Case Management</h3>
                <button class="modal-close" onclick="closeBlotterModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Blotter reports are created and managed by authorized barangay officials through the administrative portal. Contact the barangay office or send a concern below for assistance.</p>
                <form action="/public/blotter/store" method="post" id="blotterForm" style="display:none;">
                    <?= csrf_field() ?>

                    <!-- ── Complainant Name ── -->
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9aa0b4;margin-bottom:8px;">Complainant Information</p>
                    <div class="form-row" style="grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Last Name <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="complainant_last_name" placeholder="Last name" required>
                        </div>
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>First Name <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="complainant_first_name" placeholder="First name" required>
                        </div>
                    </div>
                    <div class="form-row" style="grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Middle Name <span style="font-size:11px;color:#b0b6cc;font-weight:400;">(optional)</span></label>
                            <input type="text" name="complainant_middle_name" placeholder="Middle name">
                        </div>
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Contact Number <span style="color:#e74c3c;">*</span></label>
                            <input type="tel" name="contact_number" class="js-contact-number" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:10px;">
                        <label>Email Address <span style="color:#e74c3c;">*</span></label>
                        <input type="email" name="complainant_email" placeholder="your@email.com" required>
                    </div>

                    <!-- ── Incident Details ── -->
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9aa0b4;margin:14px 0 8px;">Incident Details</p>
                    <div class="form-group" style="margin-bottom:10px;">
                        <label>Respondent Name <span style="color:#e74c3c;">*</span></label>
                        <input type="text" name="respondent_name" placeholder="Name of the person being reported" required>
                    </div>
                    <div class="form-row" style="grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Incident Type <span style="color:#e74c3c;">*</span></label>
                            <select name="incident_type" required>
                                <option value="" disabled selected>Select type</option>
                                <option value="Dispute">Dispute</option>
                                <option value="Physical Assault">Physical Assault</option>
                                <option value="Verbal Abuse">Verbal Abuse</option>
                                <option value="Theft">Theft</option>
                                <option value="Trespassing">Trespassing</option>
                                <option value="Noise Complaint">Noise Complaint</option>
                                <option value="Domestic Violence">Domestic Violence</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Incident Date</label>
                            <input type="date" name="incident_date">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:10px;">
                        <label>Incident Description <span style="color:#e74c3c;">*</span></label>
                        <textarea name="narrative" rows="3" placeholder="Describe the incident in detail..." required style="resize:vertical;"></textarea>
                    </div>

                    <!-- ── Appointment Scheduling ── -->
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9aa0b4;margin:14px 0 8px;">Appointment Scheduling</p>
                    <div style="background:#f5f7ff;border:1px solid #dde2f5;border-radius:10px;padding:12px 14px;margin-bottom:10px;">
                        <p style="font-size:12px;color:#4a5068;margin-bottom:10px;"><i class="fas fa-calendar-check" style="color:#5b6fd6;margin-right:6px;"></i>Optionally request an appointment with the Barangay Captain. Dates with existing events are marked and unavailable.</p>
                        <div class="form-row" style="grid-template-columns:1fr 1fr;gap:10px;">
                            <div class="form-group" style="margin-bottom:0;">
                                <label>Preferred Date</label>
                                <input type="date" name="appointment_date" id="appointmentDate"
                                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label>Preferred Time</label>
                                <input type="time" name="appointment_time" id="appointmentTime"
                                    min="08:00" max="17:00">
                            </div>
                        </div>
                        <div id="apptDateHint" style="margin-top:8px;font-size:12px;display:none;"></div>
                        <!-- Occupied slots list (shown after date is picked) -->
                        <div id="apptSlotList" style="display:none;margin-top:10px;"></div>
                        <!-- Time conflict warning -->
                        <div id="apptTimeConflict" style="display:none;margin-top:8px;font-size:12px;"></div>
                    </div>

                    <button type="submit" class="btn-submit" id="blotterSubmitBtn"><i class="fas fa-paper-plane" style="margin-right:8px;"></i>Submit Blotter Report</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── PUBLIC APPOINTMENT REQUEST MODAL ── -->
    <div class="modal-overlay" id="concernModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-check" style="color:#e67e22;margin-right:8px;"></i>Request an Appointment</h3>
                <button class="modal-close" onclick="closeConcernModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
                    Fill out the form below to request an appointment with barangay officials for a concern, inquiry, or follow-up.
                    Our team will get back to you at the provided email address.
                </p>
                <form action="/public/concern/store" method="post" id="concernForm">
                    <?= csrf_field() ?>

                    <!-- ── Personal Information ── -->
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9aa0b4;margin-bottom:8px;">Your Information</p>
                    <div class="form-row" style="grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Full Name <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="full_name" placeholder="e.g. Juan Dela Cruz" required>
                        </div>
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Email Address <span style="color:#e74c3c;">*</span></label>
                            <input type="email" name="email" placeholder="your@email.com" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:10px;">
                        <label>Contact Number <span style="font-size:11px;color:#b0b6cc;font-weight:400;">(optional)</span></label>
                        <input type="tel" name="contact_number" class="js-contact-number" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" title="Enter exactly 11 digits" oninput="this.value=this.value.replace(/\D/g,'').slice(0,11)">
                    </div>

                    <!-- ── Appointment Details ── -->
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9aa0b4;margin:14px 0 8px;">Appointment Details</p>
                    <div class="form-row" style="grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Category</label>
                            <select name="category">
                                <option value="">— Select Category —</option>
                                <option>Infrastructure / Roads</option>
                                <option>Garbage / Sanitation</option>
                                <option>Street Lighting</option>
                                <option>Water Supply</option>
                                <option>Peace and Order</option>
                                <option>Barangay Services</option>
                                <option>Health and Sanitation</option>
                                <option>Business Permit Inquiry</option>
                                <option>Suggestion / Feedback</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:10px;">
                            <label>Subject <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="subject" placeholder="Brief subject of your concern" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:10px;">
                        <label>Message <span style="color:#e74c3c;">*</span></label>
                        <textarea name="message" rows="4" placeholder="Describe your concern or inquiry in detail..." required style="resize:vertical;"></textarea>
                    </div>

                    <!-- ── Appointment Scheduling ── -->
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9aa0b4;margin:14px 0 8px;">Appointment Scheduling</p>
                    <div style="background:#f5f7ff;border:1px solid #dde2f5;border-radius:10px;padding:12px 14px;margin-bottom:16px;">
                        <p style="font-size:12px;color:#4a5068;margin-bottom:10px;">
                            <i class="fas fa-calendar-check" style="color:#5b6fd6;margin-right:6px;"></i>
                            Optionally request an appointment at the Barangay Hall (Mon–Fri, 8:00 AM–5:00 PM).
                        </p>
                        <div class="form-row" style="grid-template-columns:1fr 1fr;gap:10px;">
                            <div class="form-group" style="margin-bottom:0;">
                                <label>Preferred Date</label>
                                <input type="date" name="appointment_date" id="concernApptDate"
                                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label>Preferred Time</label>
                                <input type="time" name="appointment_time" id="concernApptTime"
                                    min="08:00" max="17:00">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="concernSubmitBtn"
                        onclick="concernRequestOtp()"
                        class="btn-submit" style="background:linear-gradient(135deg,#e67e22,#ca6f1e);">
                        <i class="fas fa-paper-plane" style="margin-right:8px;"></i>Submit Appointment Request
                    </button>

                    <!-- ── OTP verification step (hidden until email is sent) ── -->
                    <div id="concernOtpStep" style="display:none;margin-top:14px;">

                        <div id="concernOtpBanner"
                            style="background:#fff8f0;border:1.5px solid #fde8c8;border-radius:11px;padding:14px 16px;margin-bottom:14px;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                <i class="fas fa-envelope-open-text" style="color:#e67e22;font-size:15px;"></i>
                                <span style="font-size:13px;font-weight:700;color:#1a1d2e;">Verify Your Email</span>
                            </div>
                            <p style="font-size:12.5px;color:#7a4200;margin:0 0 10px;line-height:1.6;">
                                A 6-digit code was sent to <strong id="concernOtpEmailDisplay"></strong>.
                                Enter it below to complete your submission.
                            </p>

                            <div style="display:flex;gap:8px;align-items:flex-end;">
                                <div style="flex:1;">
                                    <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#9aa0b4;display:block;margin-bottom:5px;">
                                        Verification Code
                                    </label>
                                    <input
                                        type="text"
                                        id="concernOtpInput"
                                        maxlength="6"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        placeholder="• • • • • •"
                                        style="
                                            width:100%;box-sizing:border-box;
                                            padding:11px 14px;
                                            border:1.5px solid #fde8c8;
                                            border-radius:9px;
                                            font-size:22px;font-weight:700;
                                            letter-spacing:8px;text-align:center;
                                            font-family:'Poppins',sans-serif;
                                            outline:none;
                                            transition:border-color .2s,box-shadow .2s;
                                        "
                                        onfocus="this.style.borderColor='#e67e22';this.style.boxShadow='0 0 0 3px rgba(230,126,34,.15)'"
                                        onblur="this.style.borderColor='#fde8c8';this.style.boxShadow='none'"
                                        oninput="this.value=this.value.replace(/\D/g,'').slice(0,6);concernOtpAutoVerify(this.value)">
                                </div>
                                <button type="button"
                                    id="concernVerifyBtn"
                                    onclick="concernVerifyOtp()"
                                    style="
                                        padding:11px 18px;
                                        background:linear-gradient(135deg,#e67e22,#ca6f1e);
                                        color:#fff;border:none;border-radius:9px;
                                        font-size:13px;font-weight:600;
                                        font-family:'Poppins',sans-serif;
                                        cursor:pointer;white-space:nowrap;
                                        transition:opacity .18s;
                                        flex-shrink:0;
                                    "
                                    onmouseover="this.style.opacity='.85'"
                                    onmouseout="this.style.opacity='1'">
                                    <i class="fas fa-check"></i> Verify
                                </button>
                            </div>

                            <!-- Error / success feedback -->
                            <div id="concernOtpError"
                                style="display:none;margin-top:10px;padding:8px 12px;background:#fff0f1;border:1px solid #fad4d4;border-radius:8px;font-size:12.5px;color:#c0392b;display:flex;align-items:center;gap:7px;">
                                <i class="fas fa-exclamation-circle"></i>
                                <span id="concernOtpErrorText"></span>
                            </div>
                            <div id="concernOtpSuccess"
                                style="display:none;margin-top:10px;padding:8px 12px;background:#e6f9f1;border:1px solid #b2e8d2;border-radius:8px;font-size:12.5px;color:#0e7a55;display:flex;align-items:center;gap:7px;">
                                <i class="fas fa-check-circle"></i>
                                <span>Email verified! Submitting your concern…</span>
                            </div>

                            <!-- Resend -->
                            <div style="margin-top:10px;font-size:12px;color:#9aa0b4;text-align:center;">
                                Didn't receive it?
                                <button type="button"
                                    id="concernResendBtn"
                                    onclick="concernRequestOtp(true)"
                                    style="background:none;border:none;color:#e67e22;font-weight:600;font-size:12px;cursor:pointer;padding:0;font-family:inherit;">
                                    Resend Code
                                </button>
                            </div>
                        </div>

                    </div><!-- /#concernOtpStep -->
                </form>
            </div>
        </div>
    </div>

    <script>
        // Navbar scroll shadow
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 10) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        });

        // Hamburger menu
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('open');
        });

        // Close mobile menu on link click
        document.querySelectorAll('#mobileMenu a').forEach(function(a) {
            a.addEventListener('click', function() {
                document.getElementById('mobileMenu').classList.remove('open');
            });
        });

        // Blotter modal
        function openBlotterModal(e) {
            if (e) e.preventDefault();
            document.getElementById('blotterModal').classList.add('open');
            document.body.style.overflow = 'hidden';
            loadBusyDates();
        }

        function closeBlotterModal() {
            document.getElementById('blotterModal').classList.remove('open');
            document.body.style.overflow = '';
        }
        document.getElementById('blotterModal').addEventListener('click', function(e) {
            if (e.target === this) closeBlotterModal();
        });

        // Concern modal
        function openConcernModal(e) {
            if (e) e.preventDefault();
            document.getElementById('concernModal').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeConcernModal() {
            document.getElementById('concernModal').classList.remove('open');
            document.body.style.overflow = '';
            // Reset OTP step when modal is closed
            concernResetOtp();
        }
        document.getElementById('concernModal').addEventListener('click', function(e) {
            if (e.target === this) closeConcernModal();
        });
        if (window.location.hash === '#concernModal') {
            openConcernModal();
        }

        // ── Concern OTP flow ──────────────────────────────────────────────────

        let _concernOtpVerified = false;

        function concernResetOtp() {
            _concernOtpVerified = false;
            document.getElementById('concernOtpStep').style.display = 'none';
            document.getElementById('concernOtpInput').value = '';
            document.getElementById('concernOtpError').style.display = 'none';
            document.getElementById('concernOtpSuccess').style.display = 'none';
            const btn = document.getElementById('concernSubmitBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:8px;"></i>Submit Concern';
        }

        function concernSetBusy(busy) {
            const btn = document.getElementById('concernSubmitBtn');
            btn.disabled = busy;
            btn.innerHTML = busy ?
                '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>Sending code…' :
                '<i class="fas fa-paper-plane" style="margin-right:8px;"></i>Submit Concern';
        }

        async function concernRequestOtp(isResend = false) {
            // If already verified, just submit the real form
            if (_concernOtpVerified) {
                document.getElementById('concernForm').requestSubmit();
                return;
            }

            const form = document.getElementById('concernForm');
            const email = form.querySelector('[name="email"]').value.trim();
            const fullName = form.querySelector('[name="full_name"]').value.trim();

            if (!fullName) {
                form.querySelector('[name="full_name"]').focus();
                return;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                form.querySelector('[name="email"]').focus();
                return;
            }

            concernSetBusy(true);
            document.getElementById('concernOtpError').style.display = 'none';
            document.getElementById('concernOtpSuccess').style.display = 'none';

            try {
                // Grab fresh CSRF token from the form
                const csrfInput = form.querySelector('input[name^="csrf_"]');
                const fd = new FormData();
                fd.append('email', email);
                fd.append('full_name', fullName);
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);

                const res = await fetch('/public/concern/send-otp', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });
                const data = await res.json();

                if (!data.success) {
                    concernSetBusy(false);
                    // Show inline error above OTP step
                    const errEl = document.getElementById('concernOtpError');
                    document.getElementById('concernOtpErrorText').textContent = data.message;
                    errEl.style.display = 'flex';
                    document.getElementById('concernOtpStep').style.display = '';
                    return;
                }

                // Show OTP step
                document.getElementById('concernOtpEmailDisplay').textContent = email;
                document.getElementById('concernOtpStep').style.display = '';
                document.getElementById('concernOtpInput').value = '';
                document.getElementById('concernOtpInput').focus();
                if (isResend) {
                    const errEl = document.getElementById('concernOtpError');
                    document.getElementById('concernOtpErrorText').textContent = '';
                    errEl.style.display = 'none';
                }

            } catch (e) {
                const errEl = document.getElementById('concernOtpError');
                document.getElementById('concernOtpErrorText').textContent = 'Network error. Please try again.';
                errEl.style.display = 'flex';
                document.getElementById('concernOtpStep').style.display = '';
            }

            concernSetBusy(false);
        }

        function concernOtpAutoVerify(val) {
            if (val.length === 6) concernVerifyOtp();
        }

        async function concernVerifyOtp() {
            const form = document.getElementById('concernForm');
            const email = form.querySelector('[name="email"]').value.trim();
            const otp = document.getElementById('concernOtpInput').value.trim();

            const errEl = document.getElementById('concernOtpError');
            const sucEl = document.getElementById('concernOtpSuccess');
            const vBtn = document.getElementById('concernVerifyBtn');

            errEl.style.display = 'none';
            sucEl.style.display = 'none';

            if (otp.length !== 6) {
                document.getElementById('concernOtpErrorText').textContent = 'Please enter the 6-digit code.';
                errEl.style.display = 'flex';
                return;
            }

            vBtn.disabled = true;
            vBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const csrfInput = form.querySelector('input[name^="csrf_"]');
                const fd = new FormData();
                fd.append('otp', otp);
                fd.append('email', email);
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);

                const res = await fetch('/public/concern/verify-otp', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });
                const data = await res.json();

                if (!data.success) {
                    document.getElementById('concernOtpErrorText').textContent = data.message;
                    errEl.style.display = 'flex';
                    vBtn.disabled = false;
                    vBtn.innerHTML = '<i class="fas fa-check"></i> Verify';
                    return;
                }

                // Verified — show success briefly then submit
                _concernOtpVerified = true;
                form.dataset.offlineVerified = '1';
                sucEl.style.display = 'flex';
                vBtn.disabled = true;
                document.getElementById('concernOtpInput').disabled = true;

                setTimeout(() => {
                    if (navigator.onLine) form.submit();
                    else document.getElementById('concernSubmitBtn').click();
                }, 900);

            } catch (e) {
                document.getElementById('concernOtpErrorText').textContent = 'Network error. Please try again.';
                errEl.style.display = 'flex';
                vBtn.disabled = false;
                vBtn.innerHTML = '<i class="fas fa-check"></i> Verify';
            }
        }

        // ── Appointment date/time availability ────────────────────────────
        let busyDateMap = {}; // date → { count, busy }
        let dateSlotsMap = {}; // date → [{ start, end, label }]

        function loadBusyDates() {
            if (Object.keys(busyDateMap).length > 0) return;
            fetch('/public/blotter/busy-dates')
                .then(r => r.json())
                .then(data => {
                    busyDateMap = {};
                    (data.dates || []).forEach(d => {
                        busyDateMap[d.date] = d;
                    });
                })
                .catch(() => {});
        }

        function fmt12(hhmm) {
            if (!hhmm) return '';
            const [h, m] = hhmm.split(':').map(Number);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return h12 + ':' + String(m).padStart(2, '0') + ' ' + ampm;
        }

        // Convert HH:MM to total minutes
        function toMin(t) {
            if (!t) return null;
            const [h, m] = t.split(':').map(Number);
            return h * 60 + m;
        }

        // Returns true if [aStart, aEnd) overlaps [bStart, bEnd)
        // Uses 1-hour default duration when end is unknown
        function overlaps(aStart, bStart, bEnd) {
            const a0 = toMin(aStart);
            const a1 = a0 + 60; // 1-hour appointment slot
            const b0 = toMin(bStart);
            const b1 = bEnd ? toMin(bEnd) : b0 + 60;
            if (a0 === null || b0 === null) return false;
            return a0 < b1 && a1 > b0;
        }

        function checkTimeConflict() {
            const timeVal = document.getElementById('appointmentTime').value;
            const dateVal = document.getElementById('appointmentDate').value;
            const conflictEl = document.getElementById('apptTimeConflict');
            const submitBtn = document.getElementById('blotterSubmitBtn');
            conflictEl.style.display = 'none';
            conflictEl.innerHTML = '';
            if (submitBtn) submitBtn.disabled = false;

            if (!timeVal || !dateVal) return;

            const slots = dateSlotsMap[dateVal] || [];
            const conflicting = slots.filter(s => overlaps(timeVal, s.start, s.end));

            if (conflicting.length > 0) {
                const names = conflicting.map(s => {
                    const endStr = s.end ? ' – ' + fmt12(s.end) : ' (1 hr)';
                    return '<strong>' + s.label + '</strong> (' + fmt12(s.start) + endStr + ')';
                }).join(', ');
                conflictEl.innerHTML = '<span style="color:#c0392b;"><i class="fas fa-exclamation-circle" style="margin-right:5px;"></i>This time conflicts with: ' + names + '. Please pick a different time.</span>';
                conflictEl.style.display = 'block';
                if (submitBtn) submitBtn.disabled = true;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const apptDate = document.getElementById('appointmentDate');
            const apptTime = document.getElementById('appointmentTime');
            const hint = document.getElementById('apptDateHint');
            const slotList = document.getElementById('apptSlotList');
            if (!apptDate) return;

            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            apptDate.min = tomorrow.toISOString().split('T')[0];

            apptDate.addEventListener('change', function() {
                const val = this.value;
                hint.style.display = 'none';
                hint.innerHTML = '';
                slotList.style.display = 'none';
                slotList.innerHTML = '';
                document.getElementById('apptTimeConflict').style.display = 'none';
                apptTime.disabled = false;
                const submitBtn = document.getElementById('blotterSubmitBtn');
                if (submitBtn) submitBtn.disabled = false;

                if (!val) return;

                // Block Sundays
                const picked = new Date(val + 'T00:00:00');
                if (picked.getDay() === 0) {
                    hint.innerHTML = '<span style="color:#c0392b;"><i class="fas fa-times-circle"></i> Sundays are unavailable. Please pick a weekday.</span>';
                    hint.style.display = 'block';
                    this.value = '';
                    return;
                }

                const info = busyDateMap[val];
                if (info && info.busy) {
                    hint.innerHTML = '<span style="color:#c0392b;"><i class="fas fa-ban"></i> This date is fully booked (' + info.count + ' appointments). Please choose another date.</span>';
                    hint.style.display = 'block';
                    apptTime.disabled = true;
                    this.value = '';
                    return;
                } else if (info && info.count >= 2) {
                    hint.innerHTML = '<span style="color:#e67e22;"><i class="fas fa-exclamation-triangle"></i> This date is getting busy (' + info.count + '/3 slots). Accepted at barangay\'s discretion.</span>';
                    hint.style.display = 'block';
                } else {
                    hint.innerHTML = '<span style="color:#16a085;"><i class="fas fa-check-circle"></i> This date looks available.</span>';
                    hint.style.display = 'block';
                }

                // Fetch occupied time slots for this date
                fetch('/public/blotter/busy-slots?date=' + val)
                    .then(r => r.json())
                    .then(data => {
                        const slots = data.slots || [];
                        dateSlotsMap[val] = slots;

                        if (slots.length > 0) {
                            let html = '<div style="font-size:11.5px;font-weight:700;color:#4a5068;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;">Occupied Time Slots on ' + new Date(val + 'T00:00:00').toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric'
                            }) + ':</div>';
                            html += '<div style="display:flex;flex-direction:column;gap:5px;">';
                            slots.forEach(s => {
                                const endStr = s.end ? ' – ' + fmt12(s.end) : ' (1 hr)';
                                html += '<div style="display:flex;align-items:center;gap:8px;background:#fff0f0;border:1px solid #fad4d4;border-radius:7px;padding:6px 10px;">' +
                                    '<i class="fas fa-clock" style="color:#c0392b;font-size:11px;"></i>' +
                                    '<span style="color:#7a1a1a;font-size:12px;"><strong>' + fmt12(s.start) + endStr + '</strong> — ' + s.label + '</span></div>';
                            });
                            html += '</div>';
                            slotList.innerHTML = html;
                            slotList.style.display = 'block';
                        }

                        // Re-check time conflict if time was already selected
                        if (apptTime.value) checkTimeConflict();
                    })
                    .catch(() => {});
            });

            apptTime.addEventListener('change', checkTimeConflict);
            apptTime.addEventListener('input', checkTimeConflict);
        });

        // Fade-in on scroll
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12
        });
        document.querySelectorAll('.fade-in').forEach(function(el) {
            observer.observe(el);
        });

        // Auto-dismiss toast after 5s
        setTimeout(function() {
            var t = document.getElementById('toastMsg');
            if (t) t.style.animation = 'slideIn .3s ease reverse forwards', setTimeout(function() {
                t.remove();
            }, 300);
        }, 5000);
    </script>

    <!-- ══ PUBLIC CHATBOT WIDGET ══ -->
    <style>
        /* ── Chat widget ── */
        .cw-wrap {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .cw-toggle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            border: none;
            color: #fff;
            text-decoration: none;
            font-size: 22px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(29, 36, 72, .35);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform .2s, box-shadow .2s;
            position: relative;
        }

        .cw-toggle:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 28px rgba(29, 36, 72, .45);
        }

        .cw-unread {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #c0392b;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }

        .cw-panel {
            display: none;
            flex-direction: column;
            width: 340px;
            max-height: 520px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .18);
            overflow: hidden;
            margin-bottom: 12px;
            animation: cwSlide .2s ease;
        }

        .cw-panel.cw-open {
            display: flex;
        }

        @keyframes cwSlide {
            from {
                opacity: 0;
                transform: translateY(12px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .cw-header {
            background: linear-gradient(135deg, #1d2448, #2e3a6e);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .cw-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cw-header-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            position: relative;
        }

        .cw-header-dot {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 9px;
            height: 9px;
            background: #16c79a;
            border-radius: 50%;
            border: 2px solid #1d2448;
        }

        .cw-header-name {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
        }

        .cw-header-sub {
            color: rgba(255, 255, 255, .65);
            font-size: 11px;
            font-family: 'Poppins', sans-serif;
        }

        .cw-header-actions {
            display: flex;
            gap: 6px;
        }

        .cw-hbtn {
            background: rgba(255, 255, 255, .15);
            border: none;
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }

        .cw-hbtn:hover {
            background: rgba(255, 255, 255, .28);
        }

        .cw-date-divider {
            text-align: center;
            padding: 8px 0;
            font-size: 11px;
            color: #b0b6cc;
            font-family: 'Poppins', sans-serif;
        }

        .cw-date-divider span {
            background: #f5f6fa;
            padding: 2px 10px;
            border-radius: 100px;
        }

        .cw-messages {
            flex: 1;
            overflow-y: auto;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #f5f6fa;
        }

        .cw-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .cw-row--user {
            flex-direction: row-reverse;
        }

        .cw-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #1d2448;
            color: #fff;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cw-body {
            display: flex;
            flex-direction: column;
            max-width: 80%;
        }

        .cw-row--user .cw-body {
            align-items: flex-end;
        }

        .cw-bubble {
            background: #fff;
            border-radius: 14px 14px 14px 4px;
            padding: 10px 13px;
            font-size: 13px;
            color: #1a1d2e;
            line-height: 1.55;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            font-family: 'Poppins', sans-serif;
        }

        .cw-row--user .cw-bubble {
            background: #1d2448;
            color: #fff;
            border-radius: 14px 14px 4px 14px;
        }

        .cw-ts {
            font-size: 10px;
            color: #b0b6cc;
            margin-top: 3px;
            font-family: 'Poppins', sans-serif;
        }

        .cw-typing span {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #9aa0b4;
            border-radius: 50%;
            margin: 0 2px;
            animation: cwDot 1.2s infinite;
        }

        .cw-typing span:nth-child(2) {
            animation-delay: .2s
        }

        .cw-typing span:nth-child(3) {
            animation-delay: .4s
        }

        @keyframes cwDot {

            0%,
            80%,
            100% {
                transform: scale(.8);
                opacity: .5
            }

            40% {
                transform: scale(1.1);
                opacity: 1
            }
        }

        .cw-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 4px 0 2px;
        }

        .cw-chip {
            background: #fff;
            border: 1.5px solid #e2e5ef;
            border-radius: 100px;
            padding: 5px 12px;
            font-size: 11.5px;
            font-weight: 600;
            color: #1d2448;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cw-chip:hover {
            background: #1d2448;
            color: #fff;
            border-color: #1d2448;
        }

        .cw-footer {
            padding: 10px 12px;
            background: #fff;
            border-top: 1px solid #f0f2f8;
            flex-shrink: 0;
        }

        .cw-input-wrap {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .cw-input {
            flex: 1;
            padding: 9px 14px;
            border: 1.5px solid #e2e5ef;
            border-radius: 100px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: #1a1d2e;
            outline: none;
            transition: border-color .2s;
        }

        .cw-input:focus {
            border-color: #1d2448;
        }

        .cw-send {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #1d2448;
            border: none;
            color: #fff;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
            flex-shrink: 0;
        }

        .cw-send:hover {
            background: #2e3a6e;
        }

        .cw-powered {
            font-size: 10px;
            color: #b0b6cc;
            text-align: center;
            margin-top: 6px;
            font-family: 'Poppins', sans-serif;
        }

        @media(max-width:400px) {
            .cw-panel {
                width: calc(100vw - 32px);
            }
        }
    </style>

    <div class="cw-wrap" id="cwWrap">
        <div class="cw-panel" id="cwPanel">
            <div class="cw-header">
                <div class="cw-header-left">
                    <div class="cw-header-avatar">
                        <i class="fas fa-robot"></i>
                        <span class="cw-header-dot"></span>
                    </div>
                    <div>
                        <div class="cw-header-name">BIS Assistant</div>
                        <div class="cw-header-sub">Bacolod Barangay · Online</div>
                    </div>
                </div>
                <div class="cw-header-actions">
                    <button class="cw-hbtn" onclick="cwClose()" title="Close"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="cw-date-divider"><span>Today</span></div>
            <div class="cw-messages" id="cwMessages">
                <div class="cw-row cw-row--bot">
                    <div class="cw-avatar"><i class="fas fa-robot"></i></div>
                    <div class="cw-body">
                        <div class="cw-bubble">Hello! I'm the <strong>BIS Assistant</strong> 👋<br>I can answer questions about barangay services, documents, and how the system works.</div>
                        <span class="cw-ts">Just now</span>
                    </div>
                </div>
                <div class="cw-chips" id="cwChips">
                    <button class="cw-chip" onclick="cwQuick('How do I request a barangay clearance?')"><i class="fas fa-file-alt"></i> Request clearance</button>
                    <button class="cw-chip" onclick="cwQuick('How do I create an account?')"><i class="fas fa-user-plus"></i> Create account</button>
                    <button class="cw-chip" onclick="cwQuick('How are blotter reports handled?')"><i class="fas fa-book"></i> Blotter process</button>
                    <button class="cw-chip" onclick="cwQuick('What documents can I request?')"><i class="fas fa-file-contract"></i> Documents</button>
                    <button class="cw-chip" onclick="cwQuick('What are the office hours?')"><i class="fas fa-clock"></i> Office hours</button>
                    <button class="cw-chip" onclick="cwQuick('What events are on the calendar?')"><i class="fas fa-calendar-alt"></i> Events calendar</button>
                    <button class="cw-chip" onclick="cwQuick('What is the fee for barangay clearance?')"><i class="fas fa-coins"></i> Fees</button>
                    <button class="cw-chip" onclick="cwQuick('Speak to Admin?')"><i class="fas fa-coins"></i> Speak to Admin</button>
                </div>
            </div>
            <div class="cw-footer">
                <div class="cw-input-wrap">
                    <input type="text" id="cwInput" class="cw-input" placeholder="Ask a question…" onkeydown="if(event.key==='Enter')cwSend()">
                    <button class="cw-send" onclick="cwSend()"><i class="fas fa-paper-plane"></i></button>
                </div>
                <p class="cw-powered">Powered by <strong>Bacolod BIS</strong></p>
            </div>
        </div>
        <a class="cw-toggle" id="cwToggle" href="/assistant" aria-label="Open chat">
            <i class="fas fa-comment-dots" id="cwIcon"></i>
            <span class="cw-unread" id="cwUnread">1</span>
        </a>
    </div>

    <script>
        (function() {
            // Knowledge base replaced with Gemini API - using real AI responses
            // All hardcoded KB logic removed - now using dynamic AI responses via API

            function now() {
                const d = new Date();
                return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
            }

            function addMsg(text, isUser) {
                const wrap = document.getElementById('cwMessages');
                const chips = document.getElementById('cwChips');
                if (chips) chips.remove();
                const row = document.createElement('div');
                row.className = 'cw-row ' + (isUser ? 'cw-row--user' : 'cw-row--bot');
                row.innerHTML = isUser ?
                    `<div class="cw-body"><div class="cw-bubble">${text}</div><span class="cw-ts">${now()}</span></div>` :
                    `<div class="cw-avatar"><i class="fas fa-robot"></i></div><div class="cw-body"><div class="cw-bubble">${text}</div><span class="cw-ts">${now()}</span></div>`;
                wrap.appendChild(row);
                wrap.scrollTop = wrap.scrollHeight;
            }

            function typing() {
                const wrap = document.getElementById('cwMessages');
                const t = document.createElement('div');
                t.id = 'cwTyping';
                t.className = 'cw-row cw-row--bot';
                t.innerHTML = `<div class="cw-avatar"><i class="fas fa-robot"></i></div><div class="cw-body"><div class="cw-bubble cw-typing"><span></span><span></span><span></span></div></div>`;
                wrap.appendChild(t);
                wrap.scrollTop = wrap.scrollHeight;
            }

            window.cwSend = async function() {
                const inp = document.getElementById('cwInput');
                const msg = inp.value.trim();
                if (!msg) return;
                addMsg(msg, true);
                inp.value = '';
                document.getElementById('cwUnread').style.display = 'none';
                typing();

                try {
                    const response = await fetch('/api/chatbot/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `message=${encodeURIComponent(msg)}&source=landing`
                    });

                    const data = await response.json();
                    const t = document.getElementById('cwTyping');
                    if (t) t.remove();

                    if (data.success) {
                        addMsg(data.response, false);
                    } else {
                        addMsg('Sorry, I encountered an error. Please try again.', false);
                    }
                } catch (error) {
                    const t = document.getElementById('cwTyping');
                    if (t) t.remove();
                    addMsg('Sorry, I encountered an error. Please try again.', false);
                }
            };

            window.cwQuick = async function(msg) {
                const chips = document.getElementById('cwChips');
                if (chips) chips.remove();
                addMsg(msg, true);
                document.getElementById('cwUnread').style.display = 'none';
                typing();

                try {
                    const response = await fetch('/api/chatbot/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `message=${encodeURIComponent(msg)}&source=landing`
                    });

                    const data = await response.json();
                    const t = document.getElementById('cwTyping');
                    if (t) t.remove();

                    if (data.success) {
                        addMsg(data.response, false);
                    } else {
                        addMsg('Sorry, I encountered an error. Please try again.', false);
                    }
                } catch (error) {
                    const t = document.getElementById('cwTyping');
                    if (t) t.remove();
                    addMsg('Sorry, I encountered an error. Please try again.', false);
                }
            };

            window.cwTogglePanel = function() {
                const panel = document.getElementById('cwPanel');
                const unread = document.getElementById('cwUnread');
                const icon = document.getElementById('cwIcon');
                panel.classList.toggle('cw-open');
                if (panel.classList.contains('cw-open')) {
                    unread.style.display = 'none';
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-comment-dots';
                }
            };

            window.cwClose = function() {
                document.getElementById('cwPanel').classList.remove('cw-open');
                document.getElementById('cwIcon').className = 'fas fa-comment-dots';
            };
        })();
    </script>
</body>

</html>`