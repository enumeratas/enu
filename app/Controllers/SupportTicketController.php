<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class SupportTicketController extends BaseController
{
    public function residentForm()
    {
        $target = $this->targetRole((string) $this->request->getGet('to'));
        if ($target === null) {
            return redirect()->to('/resident/chatbot')->with('error', 'Choose the Admin or the Secretary before submitting a ticket.');
        }

        $userId = (int) session()->get('user_id');
        $pending = \Config\Database::connect()->table('support_tickets')
            ->where('user_id', $userId)
            ->where('target_role', $target)
            ->where('status', 'pending')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        return view('dashboard/resident/support_ticket', [
            'role'    => 'resident',
            'target'  => $target,
            'pending' => $pending,
        ]);
    }

    public function store()
    {
        $target = $this->targetRole((string) $this->request->getPost('target_role'));
        $title = trim(strip_tags((string) $this->request->getPost('title')));
        $concern = trim(strip_tags((string) $this->request->getPost('concern')));
        $userId = (int) session()->get('user_id');

        if ($target === null) {
            return redirect()->to('/resident/chatbot')->with('error', 'Choose the Admin or the Secretary before submitting a ticket.');
        }

        if ($title === '' || $concern === '') {
            return redirect()->to('/resident/support-ticket?to=' . $target)->with('error', 'Enter a title and your concern.');
        }

        if (mb_strlen($title) > 160) {
            $title = mb_substr($title, 0, 160);
        }
        if (mb_strlen($concern) > 2000) {
            $concern = mb_substr($concern, 0, 2000);
        }

        $db = \Config\Database::connect();
        $existing = $db->table('support_tickets')
            ->where('user_id', $userId)
            ->where('target_role', $target)
            ->where('status', 'pending')
            ->countAllResults();

        if ($existing > 0) {
            return redirect()->to('/resident/support-ticket?to=' . $target)
                ->with('error', 'You already have a pending ticket for the ' . $this->roleLabel($target) . '.');
        }

        $db->table('support_tickets')->insert([
            'user_id'     => $userId,
            'target_role' => $target,
            'title'       => $title,
            'concern'     => $concern,
            'status'      => 'pending',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $staff = $db->table('users')
            ->select('id')
            ->where('role', $target)
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        foreach ($staff as $person) {
            NotificationModel::push(
                (int) $person['id'],
                'support_ticket',
                'New support ticket',
                $title,
                '/' . $target . '/support-tickets'
            );
        }

        return redirect()->to('/resident/chatbot')->with(
            'success',
            'Your ticket was sent to the ' . $this->roleLabel($target) . '. This chat will open a live conversation after it is approved.'
        );
    }

    public function live()
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'live' => false]);
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('support_tickets')) {
            return $this->response->setJSON(['success' => true, 'live' => false]);
        }

        $ticket = $db->table('support_tickets')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('conversation_id >', 0)
            ->orderBy('reviewed_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if (! $ticket) {
            return $this->response->setJSON(['success' => true, 'live' => false]);
        }

        $conversation = $db->table('chat_conversations')
            ->where('id', (int) $ticket['conversation_id'])
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        $mode = strtolower((string) ($conversation['support_mode'] ?? ''));
        if (! $conversation || $mode !== 'human') {
            return $this->response->setJSON(['success' => true, 'live' => false]);
        }

        $messages = $db->table('chat_messages')
            ->where('conversation_id', (int) $conversation['id'])
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $role = (string) ($ticket['target_role'] ?? 'secretary');

        return $this->response->setJSON([
            'success'         => true,
            'live'            => true,
            'conversation_id' => (int) $conversation['id'],
            'staff_role'      => $role,
            'staff_label'     => $this->roleLabel($role),
            'title'           => (string) ($ticket['title'] ?? ''),
            'messages'        => $messages,
        ]);
    }

    public function staffIndex()
    {
        $role = $this->staffRole();
        if ($role === null) {
            $current = strtolower((string) session()->get('role'));

            return redirect()->to($current !== '' ? '/' . $current . '/dashboard' : '/login');
        }

        $db = \Config\Database::connect();
        $search = \App\Libraries\RecordSearch::term();
        $ticketQuery = $db->table('support_tickets t')
            ->select('t.*, u.first_name, u.last_name, u.username')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->where('t.target_role', $role);
        if ($search !== '') {
            $ticketQuery->where(\App\Libraries\RecordSearch::clause([
                'u.first_name',
                'u.last_name',
                'u.username',
                't.title',
                't.concern',
                't.status',
            ], ['t.created_at'], $search), null, false);
        }
        $tickets = $ticketQuery
            ->orderBy('t.created_at', 'DESC')
            ->limit(80)
            ->get()
            ->getResultArray();
        $rank = ['pending' => 0, 'approved' => 1, 'declined' => 2];
        usort($tickets, static function (array $left, array $right) use ($rank): int {
            $status = ($rank[$left['status'] ?? ''] ?? 9) <=> ($rank[$right['status'] ?? ''] ?? 9);
            if ($status !== 0) {
                return $status;
            }

            return strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''));
        });

        return view('dashboard/support_tickets', [
            'role'    => $role,
            'tickets' => $tickets,
            'search'  => $search,
        ]);
    }

    public function approve(int $id)
    {
        return $this->review($id, true);
    }

    public function decline(int $id)
    {
        return $this->review($id, false);
    }

    private function review(int $id, bool $approve)
    {
        $role = $this->staffRole();
        if ($role === null) {
            $current = strtolower((string) session()->get('role'));

            return redirect()->to($current !== '' ? '/' . $current . '/dashboard' : '/login');
        }

        $db = \Config\Database::connect();
        $ticket = $db->table('support_tickets')
            ->where('id', $id)
            ->where('target_role', $role)
            ->get()
            ->getRowArray();

        if (! $ticket || ($ticket['status'] ?? '') !== 'pending') {
            return redirect()->to('/' . $role . '/support-tickets')->with('error', 'That ticket is no longer waiting for review.');
        }

        $staffId = (int) session()->get('user_id');
        $now = date('Y-m-d H:i:s');

        if (! $approve) {
            $db->table('support_tickets')->where('id', $id)->update([
                'status'      => 'declined',
                'reviewed_by' => $staffId,
                'reviewed_at' => $now,
            ]);
            NotificationModel::push(
                (int) $ticket['user_id'],
                'support_ticket',
                'Support ticket declined',
                'Your ticket "' . $ticket['title'] . '" was declined by the ' . $this->roleLabel($role) . '.',
                '/resident/chatbot'
            );

            return redirect()->to('/' . $role . '/support-tickets')->with('success', 'Ticket declined.');
        }

        $conversationId = $this->openLiveConversation($ticket, $staffId, $role, $now);
        if ($conversationId <= 0) {
            return redirect()->to('/' . $role . '/support-tickets')->with('error', 'The live conversation could not be opened.');
        }

        $db->table('support_tickets')->where('id', $id)->update([
            'status'          => 'approved',
            'conversation_id' => $conversationId,
            'reviewed_by'     => $staffId,
            'reviewed_at'     => $now,
        ]);

        NotificationModel::push(
            (int) $ticket['user_id'],
            'support_ticket',
            'Support ticket approved',
            'The ' . $this->roleLabel($role) . ' approved "' . $ticket['title'] . '". Open the chatbot to continue the conversation.',
            '/resident/chatbot'
        );

        return redirect()->to('/' . $role . '/customer-service')->with('success', 'Ticket approved. You can continue the conversation in Customer Service.');
    }

    private function openLiveConversation(array $ticket, int $staffId, string $role, string $now): int
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('chat_conversations');
        $data = [
            'user_id'    => (int) $ticket['user_id'],
            'title'      => mb_substr((string) $ticket['title'], 0, 120),
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (in_array('support_mode', $fields, true)) {
            $data['support_mode'] = 'human';
        }
        if (in_array('assigned_staff_id', $fields, true)) {
            $data['assigned_staff_id'] = $staffId;
        }
        if (in_array('assigned_role', $fields, true)) {
            $data['assigned_role'] = $role;
        }
        if (in_array('last_activity_at', $fields, true)) {
            $data['last_activity_at'] = $now;
        }

        $db->table('chat_conversations')->insert($data);
        $conversationId = (int) $db->insertID();
        if ($conversationId <= 0) {
            return 0;
        }

        $residentText = trim((string) $ticket['title']) . "\n\n" . trim((string) $ticket['concern']);
        $staffText = 'Your ticket was approved. You are now in a live conversation with the ' . $this->roleLabel($role) . '. Send a message here to continue.';
        $this->insertMessage($conversationId, 'user', $residentText, (int) $ticket['user_id'], $now);
        $this->insertMessage($conversationId, 'staff', $staffText, $staffId, $now);

        return $conversationId;
    }

    private function insertMessage(int $conversationId, string $sender, string $message, int $senderUserId, string $now): void
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('chat_messages');
        $data = [
            'conversation_id' => $conversationId,
            'sender'          => $sender,
            'message'         => $message,
            'created_at'      => $now,
            'updated_at'      => $now,
        ];
        if (in_array('sender_user_id', $fields, true)) {
            $data['sender_user_id'] = $senderUserId;
        }
        $db->table('chat_messages')->insert($data);
    }

    private function staffRole(): ?string
    {
        $role = strtolower((string) session()->get('role'));

        return in_array($role, ['admin', 'secretary'], true) ? $role : null;
    }

    private function targetRole(string $role): ?string
    {
        $role = strtolower(trim($role));

        return in_array($role, ['admin', 'secretary'], true) ? $role : null;
    }

    private function roleLabel(string $role): string
    {
        return $role === 'admin' ? 'Barangay Admin' : 'Barangay Secretary';
    }
}
