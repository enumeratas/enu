<?php

namespace App\Controllers;

use App\Libraries\DeceasedAccountService;

class DeceasedAccountController extends BaseController
{
    public function index()
    {
        $service = new DeceasedAccountService();

        return view('dashboard/deceased_accounts/index', [
            'role'    => $this->currentRole(),
            'action'  => $service->currentAction(),
            'accounts' => $service->listLinkedAccounts(),
        ]);
    }

    public function savePolicy()
    {
        $role    = $this->currentRole();
        $service = new DeceasedAccountService();
        $action  = (string) $this->request->getPost('deceased_account_action');
        $service->saveAction($action);

        $label = $service->currentAction() === DeceasedAccountService::ACTION_BLOCK
            ? 'block the login'
            : 'keep the login';

        return redirect()->to('/' . $role . '/deceased-accounts')
            ->with('success', 'Policy saved. New deceased marks will ' . $label . '.');
    }

    public function setAccount(int $id)
    {
        $role    = $this->currentRole();
        $status  = (string) $this->request->getPost('status');
        $service = new DeceasedAccountService();

        if (! $service->setAccountStatus($id, $status)) {
            return redirect()->to('/' . $role . '/deceased-accounts')
                ->with('error', 'That account could not be updated.');
        }

        $message = $status === 'deceased'
            ? 'That resident account can no longer log in.'
            : 'That resident account can log in again.';

        return redirect()->to('/' . $role . '/deceased-accounts')->with('success', $message);
    }

    private function currentRole(): string
    {
        $role = strtolower((string) session()->get('role'));

        return in_array($role, ['admin', 'secretary', 'captain'], true) ? $role : 'secretary';
    }
}
