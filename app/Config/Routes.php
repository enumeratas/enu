<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$registerPiiRoutes = static function ($routes): void {
    $routes->get('pii/directory/residents', 'PiiController::residentDirectory');
    $routes->get('pii/resident/(:num)',     'PiiController::residentCard/$1');
    $routes->get('pii/member/(:num)',       'PiiController::member/$1');
    $routes->get('pii/(:segment)',          'PiiController::image/$1');
};

// ── Public (no auth required) ─────────────────────────────────────────────────
$routes->get('/',            'UIController::home');
$routes->get('/index',       'UIController::home');
$routes->get('/login',       'UIController::login');
$routes->post('/login',      'AuthController::login');
$routes->get('/select_role', 'UIController::select_role');
$routes->get('/logout',      'AuthController::logout');
$routes->get('/verify-email',          'AuthController::showVerifyEmail');
$routes->post('/verify-email',         'AuthController::verifyEmail');
$routes->get('/resend-otp',            'AuthController::resendOtp');
$routes->get('census/update/(:alphanum)', 'CensusController::publicUpdateAuthorization/$1');
$routes->get('/resident/census-update', 'CensusController::residentCensusUpdateForm');
$routes->post('/resident/census-update', 'CensusController::saveResidentCensusUpdate');
$routes->post('/public/blotter/store', 'BlotterController::storePublic');
$routes->get('/public/blotter/busy-dates', 'BlotterController::busyDates');
$routes->get('/public/blotter/busy-slots', 'BlotterController::busySlots');

// Public concern / inquiry submission
$routes->post('/public/concern/store',      'ConcernController::storePublic');
$routes->get('/public/concern/slots',       'ConcernController::slots');
$routes->post('/api/offline-sync',         'OfflineSyncController::sync');
$routes->post('/api/offline-sync',         'OfflineSyncController::sync', ['filter' => 'auth']);
$routes->post('/public/concern/send-otp',   'ConcernController::sendOtp');
$routes->post('/public/concern/verify-otp', 'ConcernController::verifyOtp');
$routes->get('/faqs',           'UIController::faqs');
$routes->get('/events',         'ScheduleController::publicCalendar');
$routes->get('/privacy-policy', 'UIController::privacy_policy');
$routes->get('/terms',          'UIController::terms_of_use');
$routes->get('/assistant',      'UIController::assistant');

// Public chatbot API (for residents without login)
$routes->post('/api/chatbot/chat',                       'ChatbotController::chat');
$routes->get('/api/chatbot/history',                     'ChatbotController::getHistory');
$routes->get('/api/chatbot/conversation/(:num)',          'ChatbotController::getConversation/$1');
$routes->post('/api/chatbot/save-log',                   'ChatbotController::saveLog');
$routes->get('/api/chatbot/logs',                        'ChatbotController::getLogs');

// Public signup — Resident only (SK/Captain/Secretary are created by admin)
$routes->get('/signup',           'UIController::create_acc');
$routes->get('/signup/(:alpha)',  'UIController::create_acc');   // legacy compat
$routes->post('/signup/store',    'AuthController::register');

// Authenticated household uploads are served locally or through a signed Cloud Storage URL.
$routes->get('uploads/(:any)', 'HouseholdUploadController::show/$1', ['filter' => 'auth']);
$routes->get('household-files/(:any)', 'HouseholdUploadController::showFile/$1', ['filter' => 'auth']);

// Forgot password flow
$routes->get('/forgot-password',          'AuthController::showForgotPassword');
$routes->post('/forgot-password',         'AuthController::sendForgotPasswordOtp');
$routes->get('/forgot-password/verify',   'AuthController::showForgotPasswordOtp');
$routes->post('/forgot-password/verify',  'AuthController::verifyForgotPasswordOtp');
$routes->get('/forgot-password/resend',   'AuthController::resendForgotPasswordOtp');
$routes->get('/forgot-password/new-password',  'AuthController::showNewPassword');
$routes->post('/forgot-password/reset',   'AuthController::saveNewPassword');

// ── Captain ───────────────────────────────────────────────────────────────────
$routes->group('/captain', ['filter' => ['auth', 'role:captain']], function ($routes) use ($registerPiiRoutes) {
    $registerPiiRoutes($routes);
    $routes->get('search', 'GlobalSearchController::index');
    $routes->get('dashboard',                    'UIController::captain_dashboard');
    $routes->get('census',                       'UIController::captain_census');
    $routes->get('household-finder',             'CensusController::finder');
    $routes->get('household/(:segment)',         'UIController::captain_household/$1');
    $routes->get('clearance',                    'ClearanceController::adminIndex/captain');
    $routes->get('clearance/request/(:num)',     'ClearanceController::residentDetail/$1');
    $routes->post('clearance/approve/(:num)',     'ClearanceController::approve/$1');
    $routes->post('clearance/release/(:num)',     'ClearanceController::release/$1');
    $routes->post('clearance/reject/(:num)',      'ClearanceController::reject/$1');
    $routes->post('clearance/store',             'ClearanceController::store');
    $routes->post('clearance/cancel/(:num)',     'ClearanceController::cancel/$1');
    $routes->get('reports',                      'UIController::captain_reports');
    $routes->get('reports/export',               'ReportsExportController::export/captain');
    $routes->get('reports/download',             'ReportsExportController::download/captain');
    $routes->get('chatbot',                      'UIController::captain_chatbot');
    $routes->post('chatbot/api/chat',           'ChatbotController::chat');
    $routes->post('chatbot/api/save-log',       'ChatbotController::saveLog');
    $routes->get('chatbot/api/logs',            'ChatbotController::getLogs');

    // ── Customer Service / Human Support ─────────────────────────────────
    $routes->get('customer-service',                        'UIController::customerService');
    $routes->get('support-tickets',                         'SupportTicketController::staffIndex');
    $routes->post('support-tickets/approve/(:num)',         'SupportTicketController::approve/$1');
    $routes->post('support-tickets/decline/(:num)',         'SupportTicketController::decline/$1');
    $routes->get('chatbot/api/support-conversations',       'ChatbotController::getSupportConversations');
    $routes->get('chatbot/api/support-conversation',        'ChatbotController::getSupportConversation');
    $routes->post('chatbot/api/take-over',                  'ChatbotController::takeOverConversation');
    $routes->post('chatbot/api/staff-message',              'ChatbotController::sendStaffMessage');
    $routes->post('chatbot/api/return-to-ai',               'ChatbotController::returnToAI');
    $routes->post('chatbot/api/close-support',              'ChatbotController::closeSupportConversation');
    $routes->get('blotter',                      'BlotterController::adminIndex/captain');
    $routes->get('blotter/(:num)',               'BlotterController::show/$1');
    $routes->post('blotter/status/(:num)',        'BlotterController::updateStatus/$1');
    $routes->post('blotter/summons/(:num)',       'BlotterController::sendSummons/$1');
    $routes->post('blotter/reschedule/(:num)',    'BlotterController::reschedule/$1');
    $routes->get('blotter/evidence/(:num)/(:num)', 'BlotterController::evidence/$1/$2');
    $routes->get('blotter/letter/(:num)',         'BlotterController::viewLetter/$1');
    $routes->get('blotter/certificate/(:num)',    'BlotterController::viewCertificate/$1');
    $routes->get('settings',                     'UIController::captain_settings');

    // Calendar / Schedule
    $routes->get('calendar',                     'ScheduleController::index');
    $routes->get('calendar/events',              'ScheduleController::listAll');
    $routes->get('calendar/day/(:segment)',       'ScheduleController::day/$1');
    $routes->get('calendar/view/(:num)',          'ScheduleController::view/$1');
    $routes->post('calendar/store',              'ScheduleController::store');
    $routes->post('calendar/update/(:num)',      'ScheduleController::update/$1');
    $routes->post('calendar/delete/(:num)',      'ScheduleController::delete/$1');

    $routes->get('activities',                              'BarangayActivityController::index');
    $routes->get('activities/new',                          'BarangayActivityController::create');
    $routes->get('activities/edit/(:num)',                  'BarangayActivityController::edit/$1');
    $routes->post('activities/store',                       'BarangayActivityController::store');
    $routes->post('activities/update/(:num)',               'BarangayActivityController::update/$1');
    $routes->post('activities/delete/(:num)',               'BarangayActivityController::delete/$1');
    $routes->get('activities/registrations/(:num)',         'BarangayActivityController::registrations/$1');
    $routes->post('activities/registrations/update/(:num)', 'BarangayActivityController::updateRegistration/$1');

    // OCR-backed ID verification for the census form.
    $routes->post('ocr/verify-id',             'OcrController::verifyId');

    // Household Moves — captain files and approves/rejects.
    $routes->get('moves',                      'HouseholdMoveController::index');
    $routes->get('moves/new',                  'HouseholdMoveController::create');
    $routes->get('moves/search',               'HouseholdMoveController::search');
    $routes->post('moves/store',               'HouseholdMoveController::store');
    $routes->post('moves/approve/(:num)',      'HouseholdMoveController::approve/$1');
    $routes->post('moves/reject/(:num)',       'HouseholdMoveController::reject/$1');

    $routes->get('deceased-accounts',                 'DeceasedAccountController::index');
    $routes->post('deceased-accounts/policy',         'DeceasedAccountController::savePolicy');
    $routes->post('deceased-accounts/account/(:num)', 'DeceasedAccountController::setAccount/$1');

    $routes->get('census-updates',        'CensusUpdateDriveController::index');
    $routes->post('census-updates/store', 'CensusUpdateDriveController::store');

    // Captain can approve/reject pending accounts
    $routes->get('pending-accounts',        'AuthController::pendingAccounts');
    $routes->post('approve-account/(:num)', 'AuthController::approveAccount/$1');
    $routes->post('reject-account/(:num)',  'AuthController::rejectAccount/$1');

    // Notifications
    $routes->get('notifications',       'AdminNotificationController::index/captain');
    $routes->get('notifications/poll',  'AdminNotificationController::poll');
    $routes->post('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/read-all', 'NotificationController::markAllRead');
    $routes->post('notifications/dismiss-feed', 'NotificationController::dismissFeed');

    // Concerns
    $routes->get('concerns',                      'ConcernController::index');
    $routes->get('concern/(:num)',                'ConcernController::show/$1');
    $routes->post('concern/schedule/(:num)',      'ConcernController::schedule/$1');
    $routes->post('concern/approve/(:num)',       'ConcernController::approve/$1');
    $routes->post('concern/resolve/(:num)',       'ConcernController::resolve/$1');
    $routes->post('concern/dismiss/(:num)',       'ConcernController::dismiss/$1');
    $routes->post('concern/reschedule/(:num)',    'ConcernController::reschedule/$1');
    $routes->get('concern/availability',          'ConcernController::availability');
    $routes->get('concern/unavailable-dates',     'ConcernController::unavailableDates');

    // Captain creates Secretary accounts
    $routes->get('create-account',        'UIController::captain_create_account');
    $routes->post('create-account/store', 'AuthController::createOfficialAccount');
    $routes->post('promote-secretary',    'AuthController::promoteResident');
    $routes->post('revoke-secretary/(:num)', 'AuthController::demoteOfficial/$1');

    // Settings
    $routes->post('settings/profile',         'SettingsController::updateProfile');
    $routes->post('settings/request-otp',     'SettingsController::requestPasswordOtp');
    $routes->post('settings/verify-otp',      'SettingsController::verifyPasswordOtp');
    $routes->post('settings/change-password', 'SettingsController::changePassword');
    $routes->post('settings/avatar',          'SettingsController::uploadAvatar');
    $routes->post('settings/system',          'SettingsController::saveSystemPreferences');

    // Census CRUD
    $routes->get('census/new',                     'CensusController::create');
    $routes->get('census/household-head/(:segment)', 'CensusController::lookupHouseholdHead/$1');
    $routes->post('census/store',                    'CensusController::store');
    $routes->post('census/update/(:segment)',         'CensusController::updateHousehold/$1');
    $routes->post('census/delete/(:segment)',         'CensusController::delete/$1');
    $routes->post('census/member/add/(:segment)',     'CensusController::addMember/$1');
    $routes->post('census/members/add/(:segment)',    'CensusController::addFamilyMembers/$1');
    $routes->post('census/member/update/(:num)',      'CensusController::updateMember/$1');
    $routes->post('census/member/delete/(:num)',      'CensusController::deleteMember/$1');
    $routes->post('census/member/separate/(:num)',    'CensusController::requestSeparation/$1');

    // Deceased / reassignment
    $routes->post('census/member/deceased/(:num)',    'CensusController::markMemberDeceased/$1');
    $routes->post('census/member/undeceased/(:num)',  'CensusController::unmarkMemberDeceased/$1');
    $routes->post('census/head/deceased/(:segment)',  'CensusController::markHeadDeceased/$1');
    $routes->post('census/head/undeceased/(:segment)', 'CensusController::unmarkHeadDeceased/$1');

    // Census PDF export
    $routes->get('census/export/pdf', 'CensusExportController::exportPdf');
});

// ── Secretary (Super Admin) ───────────────────────────────────────────────────
$routes->group('/secretary', ['filter' => ['auth', 'role:secretary']], function ($routes) use ($registerPiiRoutes) {
    $registerPiiRoutes($routes);
    $routes->get('search', 'GlobalSearchController::index');
    $routes->get('dashboard',          'UIController::secretary_dashboard');
    $routes->get('census',             'UIController::secretary_census');
    $routes->get('household-finder',   'CensusController::finder');
    $routes->get('residents',          'UIController::secretary_residents');
    $routes->get('household/(:segment)', 'UIController::secretary_household/$1');
    $routes->get('clearance',          'ClearanceController::adminIndex/secretary');
    $routes->get('clearance/request/(:num)',     'ClearanceController::residentDetail/$1');
    $routes->post('clearance/approve/(:num)',     'ClearanceController::approve/$1');
    $routes->post('clearance/release/(:num)',     'ClearanceController::release/$1');
    $routes->post('clearance/reject/(:num)',      'ClearanceController::reject/$1');
    $routes->post('clearance/store-secretary',     'ClearanceController::storeSecretary');
    // Document templates are edited directly from the Clearance cards.
    $routes->get('clearance/templates/edit/(:segment)',    'DocumentTemplateController::edit/$1');
    $routes->post('clearance/templates/update/(:segment)', 'DocumentTemplateController::update/$1');
    // Backward-compatible redirects for old template-manager URLs.
    $routes->get('templates', function () {
        return redirect()->to('/secretary/clearance');
    });
    $routes->get('templates/edit/(:segment)', function (string $key) {
        return redirect()->to('/secretary/clearance/templates/edit/' . $key);
    });
    $routes->post('templates/update/(:segment)', 'DocumentTemplateController::update/$1');
    $routes->get('barangay-settings',                'DocumentTemplateController::barangaySettings');
    $routes->post('barangay-settings/save',          'DocumentTemplateController::saveBarangaySettings');
    $routes->get('requests',           'UIController::secretary_requests');
    $routes->get('reports',                    'UIController::secretary_reports');
    $routes->get('reports/front-page',         'DocumentTemplateController::reportFrontPageSettings');
    $routes->post('reports/front-page/save',   'DocumentTemplateController::saveReportFrontPageSettings');
    $routes->get('reports/export',             'ReportsExportController::export/secretary');
    $routes->get('reports/download',           'ReportsExportController::download/secretary');
    $routes->get('chatbot',                    'UIController::secretary_chatbot');
    $routes->post('chatbot/api/chat',           'ChatbotController::chat');
    $routes->post('chatbot/api/save-log',       'ChatbotController::saveLog');
    $routes->get('chatbot/api/logs',            'ChatbotController::getLogs');

    // ── Customer Service / Human Support ─────────────────────────────────
    $routes->get('customer-service',                        'UIController::customerService');
    $routes->get('support-tickets',                         'SupportTicketController::staffIndex');
    $routes->post('support-tickets/approve/(:num)',         'SupportTicketController::approve/$1');
    $routes->post('support-tickets/decline/(:num)',         'SupportTicketController::decline/$1');
    $routes->get('chatbot/api/support-conversations',       'ChatbotController::getSupportConversations');
    $routes->get('chatbot/api/support-conversation',        'ChatbotController::getSupportConversation');
    $routes->post('chatbot/api/take-over',                  'ChatbotController::takeOverConversation');
    $routes->post('chatbot/api/staff-message',              'ChatbotController::sendStaffMessage');
    $routes->post('chatbot/api/return-to-ai',               'ChatbotController::returnToAI');
    $routes->post('chatbot/api/close-support',              'ChatbotController::closeSupportConversation');

    $routes->get('blotter',            'BlotterController::adminIndex/secretary');
    $routes->get('blotter/new',        'BlotterController::createSecretary');
    $routes->post('blotter/store',     'BlotterController::storeSecretary');
    $routes->get('blotter/(:num)',     'BlotterController::show/$1');
    $routes->post('blotter/status/(:num)',  'BlotterController::updateStatus/$1');
    $routes->post('blotter/narrative/(:num)', 'BlotterController::updateNarrative/$1');
    $routes->post('blotter/hearing-narrative/(:num)', 'BlotterController::updateHearingNarrative/$1');
    $routes->post('blotter/summons/(:num)', 'BlotterController::sendSummons/$1');
    $routes->post('blotter/reschedule/(:num)', 'BlotterController::reschedule/$1');
    $routes->get('blotter/evidence/(:num)/(:num)', 'BlotterController::evidence/$1/$2');
    $routes->get('blotter/letter/(:num)',   'BlotterController::viewLetter/$1');
    $routes->get('blotter/certificate/(:num)', 'BlotterController::viewCertificate/$1');
    $routes->get('settings',           'UIController::secretary_settings');

    // Calendar / Schedule
    $routes->get('calendar',                'ScheduleController::index');
    $routes->get('calendar/events',         'ScheduleController::listAll');
    $routes->get('calendar/day/(:segment)', 'ScheduleController::day/$1');
    $routes->get('calendar/view/(:num)',    'ScheduleController::view/$1');
    $routes->post('calendar/store',         'ScheduleController::store');
    $routes->post('calendar/update/(:num)', 'ScheduleController::update/$1');
    $routes->post('calendar/delete/(:num)', 'ScheduleController::delete/$1');

    $routes->get('activities',                              'BarangayActivityController::index');
    $routes->get('activities/new',                          'BarangayActivityController::create');
    $routes->get('activities/edit/(:num)',                  'BarangayActivityController::edit/$1');
    $routes->post('activities/store',                       'BarangayActivityController::store');
    $routes->post('activities/update/(:num)',               'BarangayActivityController::update/$1');
    $routes->post('activities/delete/(:num)',               'BarangayActivityController::delete/$1');
    $routes->get('activities/registrations/(:num)',         'BarangayActivityController::registrations/$1');
    $routes->post('activities/registrations/update/(:num)', 'BarangayActivityController::updateRegistration/$1');

    // OCR-backed ID verification for the census form.
    $routes->post('ocr/verify-id',             'OcrController::verifyId');

    // Household Moves — secretary can file a request; captain approves.
    $routes->get('moves',                      'HouseholdMoveController::index');
    $routes->get('moves/new',                  'HouseholdMoveController::create');
    $routes->get('moves/search',               'HouseholdMoveController::search');
    $routes->post('moves/store',               'HouseholdMoveController::store');

    $routes->get('deceased-accounts',                 'DeceasedAccountController::index');
    $routes->post('deceased-accounts/policy',         'DeceasedAccountController::savePolicy');
    $routes->post('deceased-accounts/account/(:num)', 'DeceasedAccountController::setAccount/$1');

    $routes->get('census-updates',        'CensusUpdateDriveController::index');
    $routes->post('census-updates/store', 'CensusUpdateDriveController::store');

    // Secretary (super admin) — approve/reject pending accounts (merged into /residents)
    $routes->get('pending-accounts',        function () {
        return redirect()->to('/secretary/residents');
    });
    $routes->post('approve-account/(:num)', 'AuthController::approveAccount/$1');
    $routes->post('reject-account/(:num)',  'AuthController::rejectAccount/$1');

    // Notifications
    $routes->get('notifications',       'AdminNotificationController::index/secretary');
    $routes->get('notifications/poll',  'AdminNotificationController::poll');
    $routes->post('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/read-all', 'NotificationController::markAllRead');
    $routes->post('notifications/dismiss-feed', 'NotificationController::dismissFeed');

    // Concerns
    $routes->get('concerns',                      'ConcernController::index');
    $routes->get('concern/(:num)',                'ConcernController::show/$1');
    $routes->post('concern/schedule/(:num)',      'ConcernController::schedule/$1');
    $routes->post('concern/approve/(:num)',       'ConcernController::approve/$1');
    $routes->post('concern/resolve/(:num)',       'ConcernController::resolve/$1');
    $routes->post('concern/dismiss/(:num)',       'ConcernController::dismiss/$1');
    $routes->post('concern/reschedule/(:num)',    'ConcernController::reschedule/$1');
    $routes->get('concern/availability',          'ConcernController::availability');
    $routes->get('concern/unavailable-dates',     'ConcernController::unavailableDates');

    // Secretary creates Captain, Resident, SK accounts
    $routes->get('create-account',        'UIController::secretary_create_account');
    $routes->post('create-account/store', 'AuthController::createOfficialAccount');

    // Officials history
    $routes->get('officials-history',     'AuthController::officialsHistory');

    // Secretary promotes existing resident to official role
    $routes->post('promote-resident',          'AuthController::promoteResident');

    // Secretary demotes official back to resident
    $routes->post('demote-official/(:num)',    'AuthController::demoteOfficial/$1');

    // Secretary resets any user's password (no verification required)
    $routes->post('reset-password/(:num)',  'SettingsController::adminResetPassword/$1');

    // Secretary changes a resident's email (OTP sent to new address)
    $routes->post('change-email/(:num)',      'SettingsController::adminRequestEmailOtp/$1');
    $routes->post('verify-email-otp/(:num)', 'SettingsController::adminVerifyEmailOtp/$1');

    // Secretary deletes a resident account
    $routes->post('delete-account/(:num)',  'SettingsController::deleteAccount/$1');

    // Secretary changes a resident's username
    $routes->post('change-username/(:num)', 'SettingsController::adminChangeUsername/$1');

    // Secretary deactivates a captain or secretary account
    $routes->post('deactivate-user/(:num)', 'SettingsController::deactivateUser/$1');

    // Census update authorization requests
    $routes->post('resident/send-census-update', 'CensusController::sendResidentUpdateAuthorization');
    $routes->post('resident/approve-census-update/(:num)', 'CensusController::approveResidentUpdate/$1');
    $routes->post('resident/reject-census-update/(:num)', 'CensusController::rejectResidentUpdate/$1');

    // Settings
    $routes->post('settings/profile',          'SettingsController::updateProfile');
    $routes->post('settings/request-otp',      'SettingsController::requestPasswordOtp');
    $routes->post('settings/verify-otp',       'SettingsController::verifyPasswordOtp');
    $routes->post('settings/change-password',  'SettingsController::changePassword');
    $routes->post('settings/avatar',           'SettingsController::uploadAvatar');
    $routes->post('settings/system',           'SettingsController::saveSystemPreferences');

    // Census CRUD
    $routes->get('census/new',                     'CensusController::create');
    $routes->get('census/household-head/(:segment)', 'CensusController::lookupHouseholdHead/$1');
    $routes->post('census/store',                    'CensusController::store');
    $routes->post('census/update/(:segment)',         'CensusController::updateHousehold/$1');
    $routes->post('census/delete/(:segment)',         'CensusController::delete/$1');
    $routes->post('census/member/add/(:segment)',     'CensusController::addMember/$1');
    $routes->post('census/members/add/(:segment)',    'CensusController::addFamilyMembers/$1');
    $routes->post('census/member/update/(:num)',      'CensusController::updateMember/$1');
    $routes->post('census/member/delete/(:num)',      'CensusController::deleteMember/$1');
    $routes->post('census/member/separate/(:num)',    'CensusController::requestSeparation/$1');

    // Deceased / reassignment
    $routes->post('census/member/deceased/(:num)',    'CensusController::markMemberDeceased/$1');
    $routes->post('census/member/undeceased/(:num)',  'CensusController::unmarkMemberDeceased/$1');
    $routes->post('census/head/deceased/(:segment)',  'CensusController::markHeadDeceased/$1');
    $routes->post('census/head/undeceased/(:segment)', 'CensusController::unmarkHeadDeceased/$1');

    // Household split workflow — secretary approves/rejects separation requests
    $routes->post('census/separation/approve/(:num)', 'CensusController::approveSeparation/$1');
    $routes->post('census/separation/reject/(:num)',  'CensusController::rejectSeparation/$1');

    // Ownership classification change workflow — secretary approves/rejects
    $routes->post('census/ownership-change/approve/(:num)', 'CensusController::approveOwnershipChange/$1');
    $routes->post('census/ownership-change/reject/(:num)',  'CensusController::rejectOwnershipChange/$1');

    // Council-submitted household approvals
    $routes->post('census/approve/(:segment)', 'CensusController::approveHousehold/$1');
    $routes->post('census/reject/(:segment)',  'CensusController::rejectHousehold/$1');

    // Census PDF export
    $routes->get('census/export/pdf', 'CensusExportController::exportPdf');
});

// ── Admin (all pages; official accounts are created here only) ───────────────
$routes->group('/admin', ['filter' => ['auth', 'role:admin']], function ($routes) use ($registerPiiRoutes) {
    $registerPiiRoutes($routes);
    $routes->get('search', 'GlobalSearchController::index');
    $routes->get('dashboard',          'UIController::admin_dashboard');
    $routes->get('census',             'UIController::secretary_census');
    $routes->get('household-finder',   'CensusController::finder');
    $routes->get('residents',          'UIController::secretary_residents');
    $routes->get('household/(:segment)', 'UIController::secretary_household/$1');
    $routes->get('clearance',          'ClearanceController::adminIndex/admin');
    $routes->get('clearance/request/(:num)',     'ClearanceController::residentDetail/$1');
    $routes->post('clearance/approve/(:num)',     'ClearanceController::approve/$1');
    $routes->post('clearance/release/(:num)',     'ClearanceController::release/$1');
    $routes->post('clearance/reject/(:num)',      'ClearanceController::reject/$1');
    $routes->post('clearance/store-secretary',     'ClearanceController::storeSecretary');
    $routes->get('clearance/templates/edit/(:segment)',    'DocumentTemplateController::edit/$1');
    $routes->post('clearance/templates/update/(:segment)', 'DocumentTemplateController::update/$1');
    $routes->get('templates', function () {
        return redirect()->to('/admin/clearance');
    });
    $routes->get('templates/edit/(:segment)', function (string $key) {
        return redirect()->to('/admin/clearance/templates/edit/' . $key);
    });
    $routes->post('templates/update/(:segment)', 'DocumentTemplateController::update/$1');
    $routes->get('barangay-settings',                'DocumentTemplateController::barangaySettings');
    $routes->post('barangay-settings/save',          'DocumentTemplateController::saveBarangaySettings');
    $routes->get('requests',           'UIController::secretary_requests');
    $routes->get('reports',                    'UIController::secretary_reports');
    $routes->get('reports/front-page',         'DocumentTemplateController::reportFrontPageSettings');
    $routes->post('reports/front-page/save',   'DocumentTemplateController::saveReportFrontPageSettings');
    $routes->get('reports/export',             'ReportsExportController::export/admin');
    $routes->get('reports/download',           'ReportsExportController::download/admin');
    $routes->get('chatbot',                    'UIController::secretary_chatbot');
    $routes->post('chatbot/api/chat',           'ChatbotController::chat');
    $routes->post('chatbot/api/save-log',       'ChatbotController::saveLog');
    $routes->get('chatbot/api/logs',            'ChatbotController::getLogs');
    $routes->get('customer-service',                        'UIController::customerService');
    $routes->get('support-tickets',                         'SupportTicketController::staffIndex');
    $routes->post('support-tickets/approve/(:num)',         'SupportTicketController::approve/$1');
    $routes->post('support-tickets/decline/(:num)',         'SupportTicketController::decline/$1');
    $routes->get('chatbot/api/support-conversations',       'ChatbotController::getSupportConversations');
    $routes->get('chatbot/api/support-conversation',        'ChatbotController::getSupportConversation');
    $routes->post('chatbot/api/take-over',                  'ChatbotController::takeOverConversation');
    $routes->post('chatbot/api/staff-message',              'ChatbotController::sendStaffMessage');
    $routes->post('chatbot/api/return-to-ai',               'ChatbotController::returnToAI');
    $routes->post('chatbot/api/close-support',              'ChatbotController::closeSupportConversation');
    $routes->get('blotter',            'BlotterController::adminIndex/admin');
    $routes->get('blotter/new',        'BlotterController::createSecretary');
    $routes->post('blotter/store',     'BlotterController::storeSecretary');
    $routes->get('blotter/(:num)',     'BlotterController::show/$1');
    $routes->post('blotter/status/(:num)',  'BlotterController::updateStatus/$1');
    $routes->post('blotter/narrative/(:num)', 'BlotterController::updateNarrative/$1');
    $routes->post('blotter/hearing-narrative/(:num)', 'BlotterController::updateHearingNarrative/$1');
    $routes->post('blotter/summons/(:num)', 'BlotterController::sendSummons/$1');
    $routes->post('blotter/reschedule/(:num)', 'BlotterController::reschedule/$1');
    $routes->get('blotter/evidence/(:num)/(:num)', 'BlotterController::evidence/$1/$2');
    $routes->get('blotter/letter/(:num)',   'BlotterController::viewLetter/$1');
    $routes->get('blotter/certificate/(:num)', 'BlotterController::viewCertificate/$1');
    $routes->get('settings',           'UIController::secretary_settings');
    $routes->get('calendar',                'ScheduleController::index');
    $routes->get('calendar/events',         'ScheduleController::listAll');
    $routes->get('calendar/day/(:segment)', 'ScheduleController::day/$1');
    $routes->get('calendar/view/(:num)',    'ScheduleController::view/$1');
    $routes->post('calendar/store',         'ScheduleController::store');
    $routes->post('calendar/update/(:num)', 'ScheduleController::update/$1');
    $routes->post('calendar/delete/(:num)', 'ScheduleController::delete/$1');
    $routes->get('activities',                              'BarangayActivityController::index');
    $routes->get('activities/new',                          'BarangayActivityController::create');
    $routes->get('activities/edit/(:num)',                  'BarangayActivityController::edit/$1');
    $routes->post('activities/store',                       'BarangayActivityController::store');
    $routes->post('activities/update/(:num)',               'BarangayActivityController::update/$1');
    $routes->post('activities/delete/(:num)',               'BarangayActivityController::delete/$1');
    $routes->get('activities/registrations/(:num)',         'BarangayActivityController::registrations/$1');
    $routes->post('activities/registrations/update/(:num)', 'BarangayActivityController::updateRegistration/$1');
    $routes->post('ocr/verify-id',             'OcrController::verifyId');
    $routes->get('moves',                      'HouseholdMoveController::index');
    $routes->get('moves/new',                  'HouseholdMoveController::create');
    $routes->get('moves/search',               'HouseholdMoveController::search');
    $routes->post('moves/store',               'HouseholdMoveController::store');
    $routes->post('moves/approve/(:num)',      'HouseholdMoveController::approve/$1');
    $routes->post('moves/reject/(:num)',       'HouseholdMoveController::reject/$1');
    $routes->get('deceased-accounts',                 'DeceasedAccountController::index');
    $routes->post('deceased-accounts/policy',         'DeceasedAccountController::savePolicy');
    $routes->post('deceased-accounts/account/(:num)', 'DeceasedAccountController::setAccount/$1');
    $routes->get('census-updates',        'CensusUpdateDriveController::index');
    $routes->post('census-updates/store', 'CensusUpdateDriveController::store');
    $routes->get('pending-accounts', function () {
        return redirect()->to('/admin/residents');
    });
    $routes->post('approve-account/(:num)', 'AuthController::approveAccount/$1');
    $routes->post('reject-account/(:num)',  'AuthController::rejectAccount/$1');
    $routes->get('notifications',       'AdminNotificationController::index/admin');
    $routes->get('notifications/poll',  'AdminNotificationController::poll');
    $routes->post('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/read-all', 'NotificationController::markAllRead');
    $routes->post('notifications/dismiss-feed', 'NotificationController::dismissFeed');
    $routes->get('concerns',                      'ConcernController::index');
    $routes->get('concern/(:num)',                'ConcernController::show/$1');
    $routes->post('concern/schedule/(:num)',      'ConcernController::schedule/$1');
    $routes->post('concern/approve/(:num)',       'ConcernController::approve/$1');
    $routes->post('concern/resolve/(:num)',       'ConcernController::resolve/$1');
    $routes->post('concern/dismiss/(:num)',       'ConcernController::dismiss/$1');
    $routes->post('concern/reschedule/(:num)',    'ConcernController::reschedule/$1');
    $routes->get('concern/availability',          'ConcernController::availability');
    $routes->get('concern/unavailable-dates',     'ConcernController::unavailableDates');
    $routes->get('users',                 'AdminUserController::index');
    $routes->get('create-account',        'UIController::secretary_create_account');
    $routes->post('create-account/store', 'AuthController::createOfficialAccount');
    $routes->get('officials-history',     'AuthController::officialsHistory');
    $routes->post('promote-resident',          'AuthController::promoteResident');
    $routes->post('demote-official/(:num)',    'AuthController::demoteOfficial/$1');
    $routes->post('reset-password/(:num)',  'SettingsController::adminResetPassword/$1');
    $routes->post('change-email/(:num)',      'SettingsController::adminRequestEmailOtp/$1');
    $routes->post('verify-email-otp/(:num)', 'SettingsController::adminVerifyEmailOtp/$1');
    $routes->post('delete-account/(:num)',  'SettingsController::deleteAccount/$1');
    $routes->post('change-username/(:num)', 'SettingsController::adminChangeUsername/$1');
    $routes->post('deactivate-user/(:num)', 'SettingsController::deactivateUser/$1');
    $routes->post('resident/send-census-update', 'CensusController::sendResidentUpdateAuthorization');
    $routes->post('resident/approve-census-update/(:num)', 'CensusController::approveResidentUpdate/$1');
    $routes->post('resident/reject-census-update/(:num)', 'CensusController::rejectResidentUpdate/$1');
    $routes->post('settings/profile',          'SettingsController::updateProfile');
    $routes->post('settings/request-otp',      'SettingsController::requestPasswordOtp');
    $routes->post('settings/verify-otp',       'SettingsController::verifyPasswordOtp');
    $routes->post('settings/change-password',  'SettingsController::changePassword');
    $routes->post('settings/avatar',           'SettingsController::uploadAvatar');
    $routes->post('settings/system',           'SettingsController::saveSystemPreferences');
    $routes->get('census/new',                     'CensusController::create');
    $routes->get('census/household-head/(:segment)', 'CensusController::lookupHouseholdHead/$1');
    $routes->post('census/store',                    'CensusController::store');
    $routes->post('census/update/(:segment)',         'CensusController::updateHousehold/$1');
    $routes->post('census/delete/(:segment)',         'CensusController::delete/$1');
    $routes->post('census/member/add/(:segment)',     'CensusController::addMember/$1');
    $routes->post('census/members/add/(:segment)',    'CensusController::addFamilyMembers/$1');
    $routes->post('census/member/update/(:num)',      'CensusController::updateMember/$1');
    $routes->post('census/member/delete/(:num)',      'CensusController::deleteMember/$1');
    $routes->post('census/member/separate/(:num)',    'CensusController::requestSeparation/$1');
    $routes->post('census/member/deceased/(:num)',    'CensusController::markMemberDeceased/$1');
    $routes->post('census/member/undeceased/(:num)',  'CensusController::unmarkMemberDeceased/$1');
    $routes->post('census/head/deceased/(:segment)',  'CensusController::markHeadDeceased/$1');
    $routes->post('census/head/undeceased/(:segment)', 'CensusController::unmarkHeadDeceased/$1');
    $routes->post('census/separation/approve/(:num)', 'CensusController::approveSeparation/$1');
    $routes->post('census/separation/reject/(:num)',  'CensusController::rejectSeparation/$1');
    $routes->post('census/ownership-change/approve/(:num)', 'CensusController::approveOwnershipChange/$1');
    $routes->post('census/ownership-change/reject/(:num)',  'CensusController::rejectOwnershipChange/$1');
    $routes->post('census/approve/(:segment)', 'CensusController::approveHousehold/$1');
    $routes->post('census/reject/(:segment)',  'CensusController::rejectHousehold/$1');
    $routes->get('census/export/pdf', 'CensusExportController::exportPdf');
    $routes->get('profiling',             'SkController::profiling');
    $routes->get('profiling/add',         'SkController::addForm');
    $routes->post('profiling/store',      'SkController::store');
    $routes->get('profiling/view/(:num)', 'SkController::view/$1');
    $routes->get('profiling/edit/(:num)', 'SkController::editForm/$1');
    $routes->post('profiling/update/(:num)', 'SkController::update/$1');
    $routes->post('profiling/photo/(:num)',  'SkController::uploadPhoto/$1');
    $routes->post('profiling/delete/(:num)', 'SkController::delete/$1');
    $routes->post('profiling/schedule',   'SkController::saveProfilingWindow');
    $routes->get('programs',              'SkController::programs');
    $routes->get('programs/new',          'SkController::programForm');
    $routes->get('programs/edit/(:num)',  'SkController::editProgramForm/$1');
    $routes->post('programs/store',       'SkController::storeProgram');
    $routes->post('programs/update/(:num)', 'SkController::updateProgram/$1');
    $routes->post('programs/status/(:num)', 'SkController::updateProgramStatus/$1');
    $routes->post('programs/delete/(:num)', 'SkController::deleteProgram/$1');
    $routes->get('programs/registrations/(:num)', 'SkController::viewRegistrations/$1');
    $routes->post('programs/registrations/update/(:num)', 'SkController::updateRegistration/$1');
    $routes->get('sk-reports',            'UIController::sk_reports');
    $routes->get('sk-activities',         'SkController::residentActivities');
});

// ── Barangay Council ─────────────────────────────────────────────────────────
$routes->group('/council', ['filter' => ['auth', 'role:council']], function ($routes) use ($registerPiiRoutes) {
    $registerPiiRoutes($routes);
    $routes->get('search', 'GlobalSearchController::index');
    $routes->get('dashboard', 'UIController::council_dashboard');
    $routes->get('census', 'UIController::council_census');
    $routes->get('household-finder', 'CensusController::finder');
    $routes->get('household/(:segment)', 'UIController::council_household/$1');
    $routes->get('programs', 'SkController::programs');
    $routes->get('activities', 'BarangayActivityController::resident');
    $routes->post('activities/join/(:num)', 'BarangayActivityController::join/$1');
    $routes->post('activities/unjoin/(:num)', 'BarangayActivityController::unjoin/$1');
    $routes->get('census-update', 'CensusController::residentCensusUpdateForm');
    $routes->post('census-update', 'CensusController::saveResidentCensusUpdate');
    $routes->get('sk-profiling', 'SkController::residentProfilingForm');
    $routes->post('sk-profiling/store', 'SkController::storeResidentProfiling');
    $routes->post('sk-profiling/update/(:num)', 'SkController::update/$1');
    $routes->post('sk-profiling/photo/(:num)', 'SkController::uploadPhoto/$1');
    $routes->get('settings', 'UIController::resident_profile');
    $routes->post('settings/request-otp', 'SettingsController::requestPasswordOtp');
    $routes->post('settings/verify-otp', 'SettingsController::verifyPasswordOtp');
    $routes->post('settings/change-password', 'SettingsController::changePassword');
    $routes->post('settings/profile', 'SettingsController::updateProfile');
    $routes->post('settings/avatar', 'SettingsController::uploadAvatar');
    $routes->get('census/new', 'CensusController::create');
    $routes->get('census/household-head/(:segment)', 'CensusController::lookupHouseholdHead/$1');
    $routes->post('census/store', 'CensusController::store');
    $routes->post('census/delete/(:segment)', 'CensusController::delete/$1');
    $routes->post('census/update/(:segment)', 'CensusController::updateHousehold/$1');
    $routes->post('census/member/add/(:segment)', 'CensusController::addMember/$1');
    $routes->post('census/members/add/(:segment)', 'CensusController::addFamilyMembers/$1');
    $routes->post('census/member/update/(:num)', 'CensusController::updateMember/$1');
    $routes->post('census/member/delete/(:num)', 'CensusController::deleteMember/$1');
    $routes->post('census/member/separate/(:num)', 'CensusController::requestSeparation/$1');
    $routes->post('census/member/deceased/(:num)', 'CensusController::markMemberDeceased/$1');
    $routes->post('census/member/undeceased/(:num)', 'CensusController::unmarkMemberDeceased/$1');
    $routes->post('census/head/deceased/(:segment)', 'CensusController::markHeadDeceased/$1');
    $routes->post('census/head/undeceased/(:segment)', 'CensusController::unmarkHeadDeceased/$1');
    $routes->get('census/export/pdf', 'CensusExportController::exportPdf');
    // OCR-backed ID verification for the census form.
    $routes->post('ocr/verify-id', 'OcrController::verifyId');
    $routes->get('clearance', 'ClearanceController::residentIndex');
    $routes->post('clearance/store', 'ClearanceController::store');
    $routes->post('clearance/cancel/(:num)', 'ClearanceController::cancel/$1');
    $routes->get('notifications', 'AdminNotificationController::index/council');
    $routes->get('notifications/poll', 'AdminNotificationController::poll');
    $routes->post('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/read-all', 'NotificationController::markAllRead');
    $routes->post('notifications/dismiss-feed', 'NotificationController::dismissFeed');
    $routes->post('chatbot/api/chat', 'ChatbotController::chat');
    $routes->post('chatbot/api/save-log', 'ChatbotController::saveLog');
    $routes->get('chatbot/api/logs', 'ChatbotController::getLogs');
});

// ── Resident ──────────────────────────────────────────────────────────────────
$routes->group('/resident', ['filter' => ['auth', 'role:resident']], function ($routes) use ($registerPiiRoutes) {
    $registerPiiRoutes($routes);
    $routes->get('search', 'GlobalSearchController::index');
    $routes->get('dashboard',     'UIController::resident_dashboard');
    $routes->get('clearance',     'ClearanceController::residentIndex');
    $routes->post('clearance/store',       'ClearanceController::store');
    $routes->post('clearance/cancel/(:num)', 'ClearanceController::cancel/$1');
    $routes->get('profile',       'UIController::resident_profile');
    $routes->get('chatbot',       'UIController::resident_chatbot');
    $routes->get('support-ticket',                'SupportTicketController::residentForm');
    $routes->post('support-ticket',               'SupportTicketController::store');
    $routes->get('support-ticket/live',           'SupportTicketController::live');
    $routes->post('chatbot/api/chat',           'ChatbotController::chat');
    $routes->post('chatbot/api/conversation',   'ChatbotController::newConversation');
    $routes->get('chatbot/api/history',         'ChatbotController::getHistory');
    $routes->get('chatbot/api/conversation/(:num)', 'ChatbotController::getConversation/$1');
    $routes->post('chatbot/api/save-log',       'ChatbotController::saveLog');
    $routes->get('chatbot/api/logs',            'ChatbotController::getLogs');
    $routes->post('chatbot/api/request-human',  'ChatbotController::requestHumanSupport');
    $routes->get('chatbot/api/support-status',  'ChatbotController::getSupportStatus');
    $routes->post('chatbot/api/heartbeat',      'ChatbotController::heartbeat');
    $routes->get('notifications', 'UIController::resident_notifications');
    $routes->get('notifications/poll',        'NotificationController::poll');
    $routes->post('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/read-all',    'NotificationController::markAllRead');
    $routes->get('concerns',                   'ConcernController::residentForm');
    $routes->post('concerns/store',            'ConcernController::storeResident');
    $routes->post('concerns/cancel/(:num)',    'ConcernController::cancelOwn/$1');

    $routes->get('activities',                        'BarangayActivityController::resident');
    $routes->post('activities/join/(:num)',           'BarangayActivityController::join/$1');
    $routes->post('activities/unjoin/(:num)',         'BarangayActivityController::unjoin/$1');

    // SK Activities — residents can view and join
    $routes->get('sk-activities',                     'SkController::residentActivities');
    $routes->post('sk-activities/join/(:num)',         'SkController::joinProgram/$1');
    $routes->post('sk-activities/unjoin/(:num)',       'SkController::unjoinProgram/$1');

    // SK Profiling — only for residents aged 15–30, automatically recorded under the active SK official
    $routes->get('sk-profiling',                      'SkController::residentProfilingForm');
    $routes->post('sk-profiling/store',               'SkController::storeResidentProfiling');
    $routes->post('sk-profiling/update/(:num)',       'SkController::update/$1');
    $routes->post('sk-profiling/photo/(:num)',        'SkController::uploadPhoto/$1');

    // Password change via OTP
    $routes->post('settings/request-otp',     'SettingsController::requestPasswordOtp');
    $routes->post('settings/verify-otp',      'SettingsController::verifyPasswordOtp');
    $routes->post('settings/change-password', 'SettingsController::changePassword');
    $routes->post('settings/avatar',          'SettingsController::uploadAvatar');
});

// ── SK ────────────────────────────────────────────────────────────────────────
$routes->group('/sk', ['filter' => ['auth', 'role:sk']], function ($routes) use ($registerPiiRoutes) {
    $registerPiiRoutes($routes);
    $routes->get('search', 'GlobalSearchController::index');
    $routes->get('dashboard',             'UIController::sk_dashboard');

    // Profiling — read from census (households + household_members)
    $routes->get('profiling',             'SkController::profiling');
    $routes->get('household/(:segment)',  'UIController::sk_household/$1');

    // sk_youth CRUD (manual add/edit still available)
    $routes->get('profiling/add',         'SkController::addForm');
    $routes->post('profiling/store',      'SkController::store');
    $routes->get('profiling/view/(:num)', 'SkController::view/$1');
    $routes->get('profiling/edit/(:num)', 'SkController::editForm/$1');
    $routes->post('profiling/update/(:num)', 'SkController::update/$1');
    $routes->post('profiling/photo/(:num)',  'SkController::uploadPhoto/$1');
    $routes->post('profiling/delete/(:num)', 'SkController::delete/$1');
    $routes->post('profiling/schedule',   'SkController::saveProfilingWindow');

    $routes->get('activities',                              'BarangayActivityController::index');
    $routes->get('activities/new',                          'BarangayActivityController::create');
    $routes->get('activities/edit/(:num)',                  'BarangayActivityController::edit/$1');
    $routes->post('activities/store',                       'BarangayActivityController::store');
    $routes->post('activities/update/(:num)',               'BarangayActivityController::update/$1');
    $routes->post('activities/delete/(:num)',               'BarangayActivityController::delete/$1');
    $routes->get('activities/registrations/(:num)',         'BarangayActivityController::registrations/$1');
    $routes->post('activities/registrations/update/(:num)', 'BarangayActivityController::updateRegistration/$1');

    $routes->get('programs',              'SkController::programs');
    $routes->get('programs/new',          'SkController::programForm');
    $routes->get('programs/edit/(:num)',  'SkController::editProgramForm/$1');
    $routes->post('programs/store',       'SkController::storeProgram');
    $routes->post('programs/update/(:num)', 'SkController::updateProgram/$1');
    $routes->post('programs/status/(:num)', 'SkController::updateProgramStatus/$1');
    $routes->post('programs/delete/(:num)', 'SkController::deleteProgram/$1');
    $routes->get('programs/registrations/(:num)', 'SkController::viewRegistrations/$1');
    $routes->post('programs/registrations/update/(:num)', 'SkController::updateRegistration/$1');
    $routes->get('chatbot',               'UIController::sk_chatbot');
    $routes->post('chatbot/api/chat',           'ChatbotController::chat');
    $routes->post('chatbot/api/save-log',       'ChatbotController::saveLog');
    $routes->get('chatbot/api/logs',            'ChatbotController::getLogs');
    $routes->get('reports',               'UIController::sk_reports');
    $routes->get('settings',              'UIController::sk_settings');

    // Document requests (clearance) — same as resident flow
    $routes->get('clearance',                    'UIController::sk_clearance');
    $routes->post('clearance/store',             'ClearanceController::store');
    $routes->post('clearance/cancel/(:num)',     'ClearanceController::cancel/$1');

    // Blotter filing
    $routes->get('blotter',                      'UIController::sk_blotter');
    $routes->post('blotter/store',               'BlotterController::store');
    $routes->get('concerns',                     'ConcernController::skForm');
    $routes->post('concerns/store',              'ConcernController::storeSk');
    $routes->post('concerns/cancel/(:num)',      'ConcernController::cancelOwn/$1');

    // Settings — password change via OTP
    $routes->post('settings/request-otp',     'SettingsController::requestPasswordOtp');
    $routes->post('settings/verify-otp',      'SettingsController::verifyPasswordOtp');
    $routes->post('settings/change-password', 'SettingsController::changePassword');
    $routes->post('settings/profile',         'SettingsController::updateProfile');
    $routes->post('settings/avatar',          'SettingsController::uploadAvatar');

    // Notifications
    $routes->get('notifications',      'AdminNotificationController::index/sk');
    $routes->get('notifications/poll', 'AdminNotificationController::poll');
    $routes->post('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/read-all', 'NotificationController::markAllRead');
    $routes->post('notifications/dismiss-feed', 'NotificationController::dismissFeed');

    $routes->get('test-env', 'TestEnv::index');
    $routes->get('test-openrouter', 'ChatbotController::testOpenRouter');
    $routes->get('test-email', 'EmailTestController::index');
});
