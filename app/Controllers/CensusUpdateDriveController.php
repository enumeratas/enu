<?php

namespace App\Controllers;

use App\Models\CensusUpdateAuthorizationModel;
use App\Models\CensusUpdateDriveModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class CensusUpdateDriveController extends BaseController
{
    public function index()
    {
        $search = \App\Libraries\RecordSearch::term();

        return view('dashboard/census_update_drives/index', [
            'role'   => $this->currentRole(),
            'drives' => \App\Libraries\RecordSearch::filter(
                (new CensusUpdateDriveModel())->listAll(),
                $search,
                static fn(array $drive): string => implode(' ', [
                    (string) ($drive['title'] ?? ''),
                    (string) ($drive['message'] ?? ''),
                    (string) ($drive['first_name'] ?? ''),
                    (string) ($drive['last_name'] ?? ''),
                ]),
                static fn(array $drive): array => [
                    $drive['deadline'] ?? null,
                    $drive['created_at'] ?? null,
                ]
            ),
            'open'   => (new CensusUpdateDriveModel())->currentOpen(),
            'search' => $search,
        ]);
    }

    public function store()
    {
        $role     = $this->currentRole();
        $title    = trim((string) $this->request->getPost('title'));
        $message  = trim((string) $this->request->getPost('message'));
        $deadline = trim((string) $this->request->getPost('deadline'));

        if ($title === '') {
            return redirect()->to('/' . $role . '/census-updates')->with('error', 'A title is required.');
        }
        if (mb_strlen($title) > 200) {
            return redirect()->to('/' . $role . '/census-updates')->with('error', 'Title must be 200 characters or less.');
        }
        $parsed = \DateTime::createFromFormat('Y-m-d', $deadline);
        if (! $parsed || $parsed->format('Y-m-d') !== $deadline) {
            return redirect()->to('/' . $role . '/census-updates')->with('error', 'A valid deadline date is required.');
        }
        if ($deadline < date('Y-m-d')) {
            return redirect()->to('/' . $role . '/census-updates')->with('error', 'The deadline cannot be in the past.');
        }
        if (mb_strlen($message) > 2000) {
            return redirect()->to('/' . $role . '/census-updates')->with('error', 'Message must be 2000 characters or less.');
        }

        $residents = (new UserModel())
            ->where('role', 'resident')
            ->where('status', 'active')
            ->findAll();

        if ($residents === []) {
            return redirect()->to('/' . $role . '/census-updates')
                ->with('error', 'There are no active resident accounts to notify.');
        }

        $expiresAt = $deadline . ' 23:59:59';
        $authModel = new CensusUpdateAuthorizationModel();
        $count     = 0;
        $body = $message !== ''
            ? $message
            : 'Please update your household record. Add new family members such as newborns, and correct any outdated information before ' . date('F j, Y', strtotime($deadline)) . '.';
        $noticeTitle = mb_strlen($title) > 200 ? mb_substr($title, 0, 197) . '...' : $title;

        foreach ($residents as $resident) {
            $auth = $authModel->createForUser(
                (int) $resident['id'],
                $resident['household_no'] ?? null,
                7,
                $expiresAt
            );
            if (! $auth) {
                continue;
            }

            $authModel->update($auth['id'], [
                'status'  => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
            ]);

            NotificationModel::push(
                (int) $resident['id'],
                'census_update',
                $noticeTitle,
                $body,
                '/census/update/' . $auth['token']
            );
            $count++;
        }

        (new CensusUpdateDriveModel())->insert([
            'title'          => $title,
            'message'        => $message !== '' ? $message : null,
            'deadline'       => $deadline,
            'notified_count' => $count,
            'created_by'     => (int) session()->get('user_id') ?: null,
        ]);

        return redirect()->to('/' . $role . '/census-updates')
            ->with('success', 'Census update notice sent to ' . $count . ' resident' . ($count === 1 ? '' : 's') . '. Deadline: ' . date('F j, Y', strtotime($deadline)) . '.');
    }

    private function currentRole(): string
    {
        $role = strtolower((string) session()->get('role'));

        return in_array($role, ['admin', 'secretary', 'captain'], true) ? $role : 'secretary';
    }
}
