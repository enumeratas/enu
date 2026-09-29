 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title><?= esc(ucfirst(session()->get('role') ?? 'resident')) ?> Dashboard - Bacolod BIS</title>
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <link rel="stylesheet" href="/style.css">
     <style>
         .svc-grid {
             display: grid;
             grid-template-columns: repeat(2, minmax(0, 1fr));
             gap: 16px;
             margin-bottom: 24px;
         }

         .svc-card {
             background: #fff;
             border: 1px solid #e8ecf4;
             border-radius: 14px;
             padding: 20px 20px 18px;
             display: grid;
             grid-template-columns: 48px minmax(0, 1fr);
             column-gap: 16px;
             align-items: center;
             transition: border-color .2s, box-shadow .2s, transform .2s;
         }

         .svc-card:hover {
             border-color: #cbd3ee;
             box-shadow: 0 4px 18px rgba(29, 36, 72, .09);
             transform: translateY(-2px);
         }

         .svc-card.active-card {
             box-shadow: 0 2px 12px rgba(29, 36, 72, .07);
         }

         .svc-icon-wrap {
             width: 48px;
             height: 48px;
             border-radius: 12px;
             background: #1d2448;
             color: #fff;
             display: flex;
             align-items: center;
             justify-content: center;
             font-size: 20px;
             grid-row: 1 / span 3;
         }

         .svc-card h4 {
             margin: 0 0 6px;
             font-size: 14px;
             font-weight: 700;
             color: #1a1d2e;
         }

         .svc-card p {
             margin: 0 0 14px;
             font-size: 12.5px;
             line-height: 1.55;
             color: #6b7280;
         }

         .svc-btn {
             display: inline-flex;
             align-items: center;
             gap: 6px;
             padding: 9px 16px;
             border-radius: 8px;
             border: 1.5px solid #1d2448;
             background: #fff;
             color: #1d2448;
             text-decoration: none;
             font-size: 13px;
             font-weight: 600;
             transition: background .2s, color .2s;
         }

         .svc-btn:hover {
             background: #1d2448;
             color: #fff;
         }

         .db-section-title {
             margin: 0 0 14px;
             font-size: 14px;
             font-weight: 700;
             color: #1a1d2e;
         }

         @media (max-width: 900px) {
             .svc-grid {
                 grid-template-columns: 1fr;
             }
         }

         @media (max-width: 700px) {
             .resident-home .dash-welcome {
                 align-items: flex-start;
                 padding: 16px;
                 gap: 10px;
             }

             .resident-home .dash-welcome h2 {
                 font-size: 18px;
                 line-height: 1.35;
                 overflow-wrap: anywhere;
             }

             .resident-home .dash-welcome p,
             .resident-home .dash-welcome-date {
                 line-height: 1.45;
                 overflow-wrap: anywhere;
             }

             .resident-home .dash-welcome-icon {
                 display: none;
             }

             .resident-home .dash-grid {
                 grid-template-columns: 1fr;
                 gap: 10px;
             }

             .resident-home .dash-stat {
                 padding: 14px 16px;
             }

             .resident-home .dash-card-head {
                 flex-wrap: wrap;
                 align-items: flex-start;
                 gap: 6px 12px;
             }

             .resident-home .dash-card-head h4 {
                 min-width: 0;
                 line-height: 1.4;
             }

             .resident-home .dash-card-head a {
                 white-space: nowrap;
             }

             .resident-home .db-table-wrap {
                 overflow-x: auto;
                 -webkit-overflow-scrolling: touch;
             }

             .resident-home .dash-mini-table {
                 min-width: 520px;
             }

             .resident-home .db-modal {
                 max-width: none;
                 width: calc(100vw - 24px);
             }
         }
     </style>
 </head>

 <body class="db-body resident-home">
     <?php
        $role      = $role ?? 'resident';
        $active    = 'dashboard';
        $pageTitle = ucfirst($role) . ' Dashboard';
        include(APPPATH . 'Views/dashboard/sidebar.php');
        ?>
     <div class="db-main">
         <?php include(APPPATH . 'Views/dashboard/topbar.php'); ?>
         <div class="db-content">

             <!-- Welcome -->
             <div class="dash-welcome">
                 <?php if (session()->getFlashdata('success')): ?>
                     <div class="db-alert db-alert--success" style="margin-bottom:16px;">
                         <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                     </div>
                 <?php endif; ?>
                 <?php if (session()->getFlashdata('error')): ?>
                     <div class="db-alert db-alert--error" style="margin-bottom:16px;">
                         <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                     </div>
                 <?php endif; ?>
                 <div>
                     <?php
                        $firstName   = session()->get('first_name') ?? '';
                        $lastName    = session()->get('last_name')  ?? '';
                        $displayName = trim("$firstName $lastName") ?: (session()->get('username') ?? 'Resident');
                        $today = date('l, F d, Y');
                        ?>
                     <h2>Good <?= date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') ?>, <?= esc($displayName) ?> 👋</h2>
                     <p>Barangay Bacolod, Bato, Camarines Sur — Barangay Information System</p>
                     <div class="dash-welcome-date"><i class="fas fa-calendar" style="margin-right:5px;"></i><?= $today ?></div>
                 </div>
                 <div class="dash-welcome-icon"><i class="fas fa-user"></i></div>
             </div>

             <?php
                $censusUpdateDrive = $censusUpdateDrive ?? null;
                $censusUpdateAuth  = $censusUpdateAuth ?? null;
                $updateLink = ! empty($censusUpdateAuth['token'])
                    ? '/census/update/' . $censusUpdateAuth['token']
                    : '/resident/dashboard';
             ?>
             <?php if ($censusUpdateDrive): ?>
                 <div class="dash-card" style="margin-bottom:16px;border-top:3px solid #e0b32a;">
                     <div class="dash-card-head">
                         <h4><i class="fas fa-user-edit" style="margin-right:8px;"></i><?= esc($censusUpdateDrive['title']) ?></h4>
                         <a href="<?= esc($updateLink) ?>">Update household →</a>
                     </div>
                     <div style="padding:14px 16px;font-size:13px;color:#3d4658;line-height:1.6;">
                         <?= nl2br(esc($censusUpdateDrive['message'] ?: 'Please update your household information and add new family members, such as newborns.')) ?>
                         <div style="margin-top:8px;color:#16325c;font-weight:600;">
                             Deadline: <?= esc(date('F j, Y', strtotime($censusUpdateDrive['deadline']))) ?>
                         </div>
                     </div>
                 </div>
             <?php endif; ?>

             <!-- Stats -->
             <div class="dash-grid">
                 <?php
                    $totalRequests  = $totalRequests  ?? 0;
                    $approved       = $approved       ?? 0;
                    $pending        = $pending        ?? 0;
                    $recentRequests = $recentRequests ?? [];
                    ?>
                 <div class="dash-stat">
                     <div class="dash-stat-icon" style="background:rgba(91,111,214,0.15);color:#5b6fd6;">
                         <i class="fas fa-file-alt"></i>
                     </div>
                     <div>
                         <span class="dash-stat-num"><?= $totalRequests ?></span>
                         <span class="dash-stat-lbl">My Requests</span>
                     </div>
                 </div>
                 <div class="dash-stat">
                     <div class="dash-stat-icon" style="background:rgba(22,199,154,0.15);color:#16c79a;">
                         <i class="fas fa-check-circle"></i>
                     </div>
                     <div>
                         <span class="dash-stat-num"><?= $approved ?></span>
                         <span class="dash-stat-lbl">Approved</span>
                     </div>
                 </div>
                 <div class="dash-stat">
                     <div class="dash-stat-icon" style="background:rgba(255,193,7,0.15);color:#f0a500;">
                         <i class="fas fa-clock"></i>
                     </div>
                     <div>
                         <span class="dash-stat-num"><?= $pending ?></span>
                         <span class="dash-stat-lbl">Pending</span>
                     </div>
                 </div>
             </div>

             <div class="dash-row">
                 <div class="dash-card">
                     <div class="dash-card-head">
                         <h4><i class="fas fa-file-alt" style="color:#5b6fd6;margin-right:8px;"></i>My Recent Requests</h4>
                         <a href="/<?= esc($role) ?>/clearance">View all →</a>
                     </div>
                     <div class="db-table-wrap">
                         <table class="dash-mini-table">
                             <thead>
                                 <tr>
                                     <th>Document</th>
                                     <th>Purpose</th>
                                     <th>Date</th>
                                     <th>Status</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php
                                    $badgeMap = [
                                        'pending'  => 'dbadge--pending',
                                        'approved' => 'dbadge--approved',
                                        'rejected' => 'dbadge--rejected',
                                        'released' => 'dbadge--resolved',
                                    ];
                                    if (empty($recentRequests)): ?>
                                     <tr>
                                         <td colspan="4" class="dash-empty">
                                             <i class="fas fa-inbox"></i>
                                             <p>No requests yet.</p>
                                         </td>
                                     </tr>
                                 <?php else: ?>
                                     <?php foreach ($recentRequests as $r):
                                            $badgeClass = $badgeMap[$r['status']] ?? 'dbadge--pending';
                                            $label      = ucfirst($r['status']);
                                            $filed      = date('M d, Y', strtotime($r['created_at']));
                                        ?>
                                         <tr>
                                             <td><?= esc($r['document_type']) ?></td>
                                             <td><?= esc($r['purpose']) ?></td>
                                             <td style="color:#9aa0b4;font-size:12px;"><?= $filed ?></td>
                                             <td><span class="dbadge <?= $badgeClass ?>"><?= $label ?></span></td>
                                         </tr>
                                     <?php endforeach; ?>
                                 <?php endif; ?>
                             </tbody>
                         </table>
                     </div>
                 </div>

                 <div class="dash-card">
                     <div class="dash-card-head">
                         <h4><i class="fas fa-calendar-check" style="color:#5b6fd6;margin-right:8px;"></i>My Appointments</h4>
                         <a href="/<?= esc($role) ?>/notifications">Notifications</a>
                     </div>
                     <div class="db-table-wrap">
                         <table class="dash-mini-table">
                             <thead>
                                 <tr>
                                     <th>Subject</th>
                                     <th>Date</th>
                                     <th>Status</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php $recentAppointments = $recentAppointments ?? []; ?>
                                 <?php if (empty($recentAppointments)): ?>
                                     <tr>
                                         <td colspan="3" class="dash-empty">
                                             <i class="fas fa-calendar-alt"></i>
                                             <p>No appointments yet.</p>
                                         </td>
                                     </tr>
                                 <?php else: ?>
                                     <?php foreach ($recentAppointments as $appointment): ?>
                                         <tr>
                                             <td><?= esc($appointment['subject'] ?: 'Barangay Appointment') ?></td>
                                             <td style="color:#9aa0b4;font-size:12px;">
                                                 <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?>
                                             </td>
                                             <td>
                                                 <span class="dbadge <?= $appointment['status'] === 'resolved' ? 'dbadge--approved' : 'dbadge--pending' ?>">
                                                     <?= esc(ucfirst($appointment['status'])) ?>
                                                 </span>
                                             </td>
                                         </tr>
                                     <?php endforeach; ?>
                                 <?php endif; ?>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>

             <div class="dash-card" style="margin-top:16px;">
                 <div class="dash-card-head">
                     <h4><i class="fas fa-bullhorn" style="color:#5b6fd6;margin-right:8px;"></i>Barangay Activities</h4>
                     <a href="/resident/activities">View all →</a>
                 </div>
                 <div class="db-table-wrap">
                     <table class="dash-mini-table">
                         <thead>
                             <tr>
                                 <th>Activity</th>
                                 <th>Date</th>
                                 <th>Venue</th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php $barangayActivities = $barangayActivities ?? []; ?>
                             <?php if ($barangayActivities === []): ?>
                                 <tr>
                                     <td colspan="3" class="dash-empty">
                                         <i class="fas fa-bullhorn"></i>
                                         <p>No barangay activities posted yet.</p>
                                     </td>
                                 </tr>
                             <?php else: ?>
                                 <?php foreach ($barangayActivities as $activity): ?>
                                     <tr>
                                         <td>
                                             <?php $banner = trim((string) ($activity['banner_path'] ?? '')); ?>
                                             <?php if ($banner !== ''): ?>
                                                 <img src="/uploads/<?= esc(ltrim($banner, '/')) ?>" alt="" style="display:block;width:72px;height:40px;object-fit:cover;margin-bottom:6px;border:1px solid #e2e6ee;">
                                             <?php endif; ?>
                                             <?= esc($activity['title']) ?>
                                         </td>
                                         <td style="color:#9aa0b4;font-size:12px;">
                                             <?= ! empty($activity['activity_date']) ? esc(date('M d, Y', strtotime($activity['activity_date']))) : '—' ?>
                                         </td>
                                         <td><?= esc($activity['venue'] ?: '—') ?></td>
                                     </tr>
                                 <?php endforeach; ?>
                             <?php endif; ?>
                         </tbody>
                     </table>
                 </div>
             </div>

         </div>
     </div>

     <!-- ══ CONCERN MODAL ══ -->
     <div class="db-modal-overlay" id="concernModal">
         <div class="db-modal" style="max-width:520px;">
             <div class="db-modal-header">
                 <h3><i class="fas fa-comments"></i> Raise a Concern</h3>
                 <button class="db-modal-close" onclick="closeModal('concernModal')"><i class="fas fa-times"></i></button>
             </div>
             <div class="db-modal-body">
                 <div class="db-form-grid">
                     <div class="db-form-group db-form-group--full">
                         <label>Category</label>
                         <select>
                             <option value="">-- Select Category --</option>
                             <option>Infrastructure / Roads</option>
                             <option>Garbage / Sanitation</option>
                             <option>Street Lighting</option>
                             <option>Water Supply</option>
                             <option>Peace and Order</option>
                             <option>Barangay Services</option>
                             <option>Health and Sanitation</option>
                             <option>Suggestion / Feedback</option>
                             <option>Other</option>
                         </select>
                     </div>
                     <div class="db-form-group db-form-group--full">
                         <label>Subject</label>
                         <input type="text" placeholder="Brief subject of your concern">
                     </div>
                     <div class="db-form-group db-form-group--full">
                         <label>Details</label>
                         <textarea rows="4" placeholder="Describe your concern or suggestion in detail..."></textarea>
                     </div>
                     <div class="db-form-group db-form-group--full">
                         <label>Preferred Contact Method</label>
                         <select>
                             <option>Email</option>
                             <option>In-person at Barangay Hall</option>
                             <option>No follow-up needed</option>
                         </select>
                     </div>
                 </div>
             </div>
             <div class="db-modal-footer">
                 <button class="db-btn db-btn--outline" onclick="closeModal('concernModal')">Cancel</button>
                 <button class="db-btn db-btn--primary"><i class="fas fa-paper-plane"></i> Submit Concern</button>
             </div>
         </div>
     </div>

     <script>
         function openModal(id) {
             document.getElementById(id).classList.add('active');
         }

         function closeModal(id) {
             document.getElementById(id).classList.remove('active');
         }

         document.querySelectorAll('.db-nav-item').forEach(item => {
             item.addEventListener('click', () => document.getElementById('sidebar').classList.remove('open'));
         });
     </script>
 </body>

 </html>