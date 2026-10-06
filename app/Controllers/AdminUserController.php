<?php

namespace App\Controllers;

use App\Libraries\RecordSearch;

class AdminUserController extends BaseController
{
    /** @var list<string> */
    private array $roles = ['admin', 'captain', 'secretary', 'council', 'sk', 'resident'];

    /** @var list<string> */
    private array $statuses = ['active', 'pending', 'unverified', 'rejected', 'deceased'];

    public function index()
    {
        if (session_role() !== 'admin') {
            $role = session_role();

            return redirect()->to($role !== '' ? '/' . $role . '/dashboard' : '/login');
        }

        $roleFilter = strtolower(trim((string) $this->request->getGet('role')));
        $statusFilter = strtolower(trim((string) $this->request->getGet('status')));
        if (! in_array($roleFilter, $this->roles, true)) {
            $roleFilter = '';
        }
        if (! in_array($statusFilter, $this->statuses, true)) {
            $statusFilter = '';
        }

        $search = RecordSearch::term();
        $db = db_connect();
        $counts = [];
        foreach ($this->roles as $role) {
            $counts[$role] = (int) $db->table('users')->where('role', $role)->countAllResults();
        }

        $builder = $db->table('users')->select(
            'id, first_name, middle_name, last_name, username, email, role, status, household_no, contact_number, council_zone, email_verified, created_at'
        );
        if ($statusFilter !== '') {
            $builder->where('status', $statusFilter);
        }
        if ($search !== '') {
            $builder->where(RecordSearch::clause([
                'first_name',
                'middle_name',
                'last_name',
                'username',
                'email',
                'household_no',
                'contact_number',
                'role',
                'status',
                'council_zone',
            ], ['created_at'], $search), null, false);
        }

        return view('dashboard/admin/users', [
            'users'         => $builder->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray(),
            'roleFilter'    => $roleFilter,
            'statusFilter'  => $statusFilter,
            'search'        => $search,
            'roleCounts'    => $counts,
            'totalAccounts' => array_sum($counts),
            'activeAccounts' => (int) $db->table('users')->where('status', 'active')->countAllResults(),
            'pendingAccounts' => (int) $db->table('users')->where('status', 'pending')->countAllResults(),
            'currentUserId' => (int) session()->get('user_id'),
        ]);
    }

    public function delete(int $id)
    {
        if (session_role() !== 'admin') {
            $role = session_role();

            return redirect()->to($role !== '' ? '/' . $role . '/dashboard' : '/login');
        }

        if ((int) session()->get('user_id') === $id) {
            return redirect()->to('/admin/users')->with('error', 'You cannot delete your own account.');
        }

        $db = db_connect();
        $user = $db->table('users')->where('id', $id)->get()->getRowArray();
        if (! is_array($user)) {
            return redirect()->to('/admin/users')->with('error', 'That account was not found.');
        }

        if (($user['role'] ?? '') === 'admin') {
            $admins = (int) $db->table('users')->where('role', 'admin')->countAllResults();
            if ($admins <= 1) {
                return redirect()->to('/admin/users')->with('error', 'The last admin account cannot be deleted.');
            }
        }

        $name = trim(((string) ($user['first_name'] ?? '')) . ' ' . ((string) ($user['last_name'] ?? '')));
        if ($name === '') {
            $name = (string) ($user['username'] ?? 'This account');
        }

        try {
            $db->table('users')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            return redirect()->to('/admin/users')->with('error', 'This account is still linked to barangay records and could not be deleted.');
        }

        $error = $db->error();
        if (! empty($error['code'])) {
            return redirect()->to('/admin/users')->with('error', 'This account is still linked to barangay records and could not be deleted.');
        }

        return redirect()->to('/admin/users')->with('success', $name . ' was deleted.');
    }
}
