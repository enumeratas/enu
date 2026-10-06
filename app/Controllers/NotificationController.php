<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    protected NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    // ── GET /resident/notifications/poll  (JSON, for topbar bell) ─────────────
    public function poll(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session()->get('user_id');
        if (! $userId) {
            return $this->response->setJSON(['unread' => 0]);
        }

        $role = strtolower((string) session()->get('role'));

        return $this->response->setJSON([
            'unread' => $this->model->countUnread(
                $userId,
                NotificationModel::includesBroadcastsForRole($role)
            ),
        ]);
    }

    // ── POST /resident/notifications/read/{id}  (AJAX) ───────────────────────
    public function markRead(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session()->get('user_id');
        $unread = 0;

        if ($userId) {
            $role = strtolower((string) session()->get('role'));
            $includeBroadcasts = NotificationModel::includesBroadcastsForRole($role);
            $this->model->markReadById($id, $userId, $includeBroadcasts);
            $unread = $this->model->countUnread($userId, $includeBroadcasts);
        }

        return $this->response->setJSON([
            'ok'     => true,
            'unread' => $unread,
        ]);
    }

    // ── POST /resident/notifications/read-all  (AJAX) ────────────────────────
    public function markAllRead(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session()->get('user_id');
        $unread = 0;

        if ($userId > 0) {
            $role = strtolower((string) session()->get('role'));
            $includeBroadcasts = NotificationModel::includesBroadcastsForRole($role);

            $this->model->markAllRead($userId, $includeBroadcasts);

            if (in_array($role, ['admin', 'secretary', 'captain'], true)) {
                controller(AdminNotificationController::class)->dismissAllFeedForUser($userId);
                $unread = controller(AdminNotificationController::class)->getBellUnreadCount($userId);
            } else {
                $unread = $this->model->countUnread($userId, $includeBroadcasts);
            }
        }

        return $this->response->setJSON([
            'ok'     => true,
            'unread' => $unread,
        ]);
    }

    // ── POST /{role}/notifications/dismiss-feed  (AJAX) ─────────────────────
    public function dismissFeed(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session()->get('user_id');
        $type   = strtolower(trim((string) $this->request->getPost('type')));
        $ref    = trim((string) $this->request->getPost('ref'));

        if ($userId > 0 && $type !== '' && $ref !== '') {
            (new \App\Models\NotificationDismissalModel())->dismiss($userId, $type, $ref);
        }

        return $this->response->setJSON(['ok' => true]);
    }
}
