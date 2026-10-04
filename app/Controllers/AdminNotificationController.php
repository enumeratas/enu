<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * AdminNotificationController
 *
 * Provides real-time activity notifications for secretary and captain
 * by querying live counts directly from the database.
 * These are not stored notifications — they reflect actual pending work.
 */
class AdminNotificationController extends BaseController
{
    // ── GET /secretary/notifications  ─────────────────────────────────────────
    public function index(string $role = 'secretary')
    {
        // SK gets a completely different notifications page — activity-focused
        if ($role === 'sk') {
            $userId = (int) session()->get('user_id');
            $notificationModel = new \App\Models\NotificationModel();
            return view('dashboard/sk/notifications', [
                'personalNotifications' => $notificationModel->getForUser($userId, false),
            ]);
        }

        // Council receives only personal updates, not the secretary/captain
        // activity feed for other residents and barangay operations.
        if ($role === 'council') {
            $userId = (int) session()->get('user_id');
            $notificationModel = new \App\Models\NotificationModel();
            $includeBroadcasts = \App\Models\NotificationModel::includesBroadcastsForRole('council');

            return view('dashboard/resident/notifications', [
                'role'        => 'council',
                'notifs'      => $notificationModel->getForUser($userId, $includeBroadcasts),
                'unreadCount' => $notificationModel->countUnread($userId, $includeBroadcasts),
            ]);
        }

        $data = $this->_buildNotificationData();
        $data['role'] = $role;

        $view = ($role === 'captain')
            ? 'dashboard/captain/notifications'
            : 'dashboard/secretary/notifications';

        return view($view, $data);
    }

    // ── GET /secretary/notifications/poll  (JSON for topbar bell) ─────────────
    public function poll(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setJSON([
            'unread' => $this->getBellUnreadCount((int) session()->get('user_id')),
        ]);
    }

    /**
     * Topbar bell badge: unread rows in notifications only.
     */
    public function getBellUnreadCount(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        return (new \App\Models\NotificationModel())->countUnread(
            $userId,
            false
        );
    }

    /**
     * Notifications page badge: unread feed items plus unread notification rows.
     */
    public function getPageUnreadCount(int $userId): int
    {
        return $this->_buildNotificationData($userId)['unreadCount'];
    }

    public function dismissAllFeedForUser(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $data  = $this->_buildNotificationData($userId);
        $items = [];

        foreach ($data['feedItems'] as $item) {
            if (! empty($item['is_read'])) {
                continue;
            }

            $items[] = [
                'type'   => (string) ($item['type'] ?? ''),
                'ref_id' => (string) ($item['ref_id'] ?? ''),
            ];
        }

        (new \App\Models\NotificationDismissalModel())->dismissMany($userId, $items);
    }

    // ── Shared data builder ───────────────────────────────────────────────────
    private function _buildNotificationData(?int $userId = null): array
    {
        $db = \Config\Database::connect();
        $userId = $userId ?? (int) session()->get('user_id');
        $dismissed = $userId > 0
            ? (new \App\Models\NotificationDismissalModel())->getDismissedKeys($userId)
            : [];
        $personalNotifications = $userId > 0
            ? (new \App\Models\NotificationModel())->getForUser($userId, false)
            : [];

        // ── 1. Pending accounts awaiting approval ─────────────────────────────
        $pendingAccounts = (int) $db->table('users')
            ->where('status', 'pending')
            ->countAllResults();

        // ── 2. Pending clearance / document requests ──────────────────────────
        $pendingClearances = (int) $db->table('clearance_requests')
            ->where('status', 'pending')
            ->countAllResults();

        // ── 3. New blotter reports (filed within the last 7 days, still open) ─
        $newBlotters = (int) $db->table('blotter_reports')
            ->whereIn('status', ['pending', 'under_investigation'])
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->countAllResults();

        // ── 4. Unresolved blotter reports (all open/pending) ──────────────────
        $openBlotters = (int) $db->table('blotter_reports')
            ->whereIn('status', ['pending', 'under_investigation'])
            ->countAllResults();

        // ── 5. Blotter hearings scheduled for today or tomorrow ───────────────
        $upcomingHearings = (int) $db->table('blotter_reports')
            ->where('appointment_date >=', date('Y-m-d'))
            ->where('appointment_date <=', date('Y-m-d', strtotime('+1 day')))
            ->whereIn('status', ['pending', 'under_investigation'])
            ->countAllResults();

        // ── 6. Recent clearance requests (last 24h) ───────────────────────────
        $recentClearances = $db->table('clearance_requests cr')
            ->select('cr.id, cr.user_id, cr.document_type, cr.purpose, cr.status, cr.created_at,
                      CONCAT(TRIM(u.first_name), " ", TRIM(u.last_name)) AS resident_name')
            ->join('users u', 'u.id = cr.user_id', 'left')
            ->where('cr.status', 'pending')
            ->orderBy('cr.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // ── 7. Recent blotter reports (last 7 days) ───────────────────────────
        $recentBlotters = $db->table('blotter_reports')
            ->select('id, complainant_name, incident_type, status, created_at, appointment_date')
            ->whereIn('status', ['pending', 'under_investigation'])
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // ── 8. Pending user accounts (detail) ────────────────────────────────
        $pendingUsers = $db->table('users')
            ->select('id, first_name, last_name, role, email, created_at')
            ->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // ── 9. Upcoming schedules (today + next 3 days) ───────────────────────
        $upcomingSchedules = (int) $db->table('schedules')
            ->where('event_date >=', date('Y-m-d'))
            ->where('event_date <=', date('Y-m-d', strtotime('+3 days')))
            ->countAllResults();

        $upcomingScheduleList = $db->table('schedules')
            ->select('id, title, event_date, start_time, location, created_at')
            ->where('event_date >=', date('Y-m-d'))
            ->where('event_date <=', date('Y-m-d', strtotime('+3 days')))
            ->orderBy('event_date', 'ASC')
            ->orderBy('start_time', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        // ── 10. Pending concern / inquiry submissions ─────────────────────────
        $pendingConcerns = 0;
        $recentConcerns  = [];
        if ($db->tableExists('concern_submissions')) {
            $pendingConcerns = (int) $db->table('concern_submissions')
                ->where('status', 'pending')
                ->countAllResults();
            $recentConcerns = $db->table('concern_submissions')
                ->select('id, full_name, email, contact_number, category, subject, message, appointment_date, appointment_time, created_at')
                ->where('status', 'pending')
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        }

        // ── Groups for the bell badge total ──────────────────────────────────
        $groups = [
            ['key' => 'pending_accounts',  'count' => $pendingAccounts],
            ['key' => 'pending_clearances', 'count' => $pendingClearances],
            ['key' => 'new_blotters',      'count' => $newBlotters],
            ['key' => 'upcoming_hearings', 'count' => $upcomingHearings],
            ['key' => 'upcoming_schedules', 'count' => $upcomingSchedules],
            ['key' => 'pending_concerns',  'count' => $pendingConcerns],
        ];

        // ── Unified feed: merge all items, sort newest-first ─────────────────
        $feedItems = [];

        foreach ($pendingUsers as $u) {
            $refId = (string) ($u['id'] ?? '');
            $feedItems[] = [
                'type'        => 'account',
                'ref_id'      => $refId,
                'title'       => trim($u['first_name'] . ' ' . $u['last_name']),
                'sub'         => strtoupper($u['role']) . ' · ' . $u['email'],
                'link'        => null,
                'action_url'  => null,
                'user_id'     => $u['id'],
                'username'    => null,
                'user_role'   => $u['role'],
                'created_at'  => $u['created_at'],
                'is_read'     => isset($dismissed[\App\Models\NotificationDismissalModel::key('account', $refId)]),
            ];
        }

        foreach ($recentClearances as $cr) {
            $refId = (string) ($cr['id'] ?? '');
            $feedItems[] = [
                'type'        => 'clearance',
                'ref_id'      => $refId,
                'title'       => $cr['resident_name'] ?? 'Unknown',
                'sub'         => $cr['document_type'] . ' — ' . $cr['purpose'],
                'link'        => null,
                'user_id'     => $cr['user_id'],
                'created_at'  => $cr['created_at'],
                'is_read'     => isset($dismissed[\App\Models\NotificationDismissalModel::key('clearance', $refId)]),
            ];
        }

        foreach ($recentBlotters as $b) {
            $refId = (string) ($b['id'] ?? '');
            $feedItems[] = [
                'type'        => 'blotter',
                'ref_id'      => $refId,
                'title'       => $b['complainant_name'],
                'sub'         => $b['incident_type'] . ' · ' . ($b['status'] === 'under_investigation' ? 'Under Investigation' : ucfirst(str_replace('_', ' ', $b['status']))),
                'link'        => null, // filled in view with role prefix
                'blotter_id'  => $b['id'],
                'appt_date'   => $b['appointment_date'] ?? null,
                'created_at'  => $b['created_at'],
                'is_read'     => isset($dismissed[\App\Models\NotificationDismissalModel::key('blotter', $refId)]),
            ];
        }

        foreach ($recentConcerns as $c) {
            $refId = (string) ($c['id'] ?? '');
            $feedItems[] = [
                'type'        => 'concern',
                'ref_id'      => $refId,
                'title'       => $c['full_name'],
                'sub'         => ($c['category'] ? $c['category'] . ' · ' : '') . $c['subject'],
                'email'       => $c['email'],
                'concern_id'  => $c['id'],
                'category'    => $c['category'] ?? '',
                'subject'     => $c['subject'],
                'message'     => $c['message'] ?? '',
                'contact'     => $c['contact_number'] ?? '',
                'appt_date'   => $c['appointment_date'] ?? null,
                'appt_time'   => $c['appointment_time'] ?? null,
                'created_at'  => $c['created_at'],
                'is_read'     => isset($dismissed[\App\Models\NotificationDismissalModel::key('concern', $refId)]),
            ];
        }

        foreach ($upcomingScheduleList as $s) {
            // Sort by when the notification was created, not the future event date.
            $dt = $s['created_at'] ?? null;
            if (! $dt) {
                $dt = $s['event_date'] . ($s['start_time'] ? ' ' . $s['start_time'] : ' 00:00:00');
            }
            $refId = (string) ($s['id'] ?? '');
            $feedItems[] = [
                'type'        => 'schedule',
                'ref_id'      => $refId,
                'title'       => $s['title'],
                'sub'         => date('M d, Y', strtotime($s['event_date']))
                    . ($s['start_time'] ? ' at ' . date('g:i A', strtotime($s['start_time'])) : '')
                    . ($s['location']   ? ' — ' . $s['location'] : ''),
                'schedule_id' => $s['id'],
                'created_at'  => $dt,
                'is_read'     => isset($dismissed[\App\Models\NotificationDismissalModel::key('schedule', $refId)]),
            ];
        }

        // Sort all items newest-first
        usort($feedItems, static function (array $a, array $b): int {
            $timeCompare = strtotime((string) ($b['created_at'] ?? '')) <=> strtotime((string) ($a['created_at'] ?? ''));
            if ($timeCompare !== 0) {
                return $timeCompare;
            }

            return strcmp((string) ($b['type'] ?? ''), (string) ($a['type'] ?? ''));
        });

        $unreadFeed = count(array_filter(
            $feedItems,
            static fn(array $item): bool => empty($item['is_read'])
        ));
        $unreadPersonal = count(array_filter(
            $personalNotifications,
            static fn(array $notification): bool => empty($notification['read_at'])
        ));

        return [
            'groups'               => $groups,
            'feedItems'            => $feedItems,
            'personalNotifications' => $personalNotifications,
            'unreadCount'          => $unreadFeed + $unreadPersonal,
            'totalItems'           => count($feedItems) + count($personalNotifications),
            // Keep individual counts for the summary stat cards
            'pendingAccounts'      => $pendingAccounts,
            'pendingClearances'    => $pendingClearances,
            'newBlotters'          => $newBlotters,
            'openBlotters'         => $openBlotters,
            'upcomingHearings'     => $upcomingHearings,
            'upcomingSchedules'    => $upcomingSchedules,
            'pendingConcerns'      => $pendingConcerns,
            'recentConcerns'       => $recentConcerns,
            'recentClearances'     => $recentClearances,
            'recentBlotters'       => $recentBlotters,
            'pendingUsers'         => $pendingUsers,
            'upcomingScheduleList' => $upcomingScheduleList,
        ];
    }
}
