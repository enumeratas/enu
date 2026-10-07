<?php

namespace App\Controllers;

use App\Models\BarangayActivityModel;
use App\Models\NotificationModel;

class BarangayActivityController extends BaseController
{
    public function index()
    {
        $model = new BarangayActivityModel();
        $model->syncDateStatuses();
        $activities = $model->forOfficials();
        foreach ($activities as &$activity) {
            $activity['registration_count'] = $model->registrationCount((int) $activity['id']);
            $this->syncActivityCalendar($activity);
        }
        unset($activity);

        $search = \App\Libraries\RecordSearch::term();
        $activities = \App\Libraries\RecordSearch::filter(
            $activities,
            $search,
            static fn(array $activity): string => implode(' ', [
                (string) ($activity['title'] ?? ''),
                (string) ($activity['category'] ?? ''),
                (string) ($activity['venue'] ?? ''),
                (string) ($activity['status'] ?? ''),
                (string) ($activity['description'] ?? ''),
            ]),
            static fn(array $activity): array => [
                $activity['conducted_date'] ?? null,
                $activity['activity_date'] ?? null,
                $activity['start_date'] ?? null,
                $activity['end_date'] ?? null,
            ]
        );

        return view('dashboard/activities/manage', [
            'role'       => $this->manageRole(),
            'activities' => $activities,
            'search'     => $search,
        ]);
    }

    public function create()
    {
        return view('dashboard/activities/form', [
            'role'     => $this->manageRole(),
            'activity' => null,
        ]);
    }

    public function edit(int $id)
    {
        $role     = $this->manageRole();
        $activity = (new BarangayActivityModel())->find($id);
        if (! $activity) {
            return redirect()->to('/' . $role . '/activities')->with('error', 'Activity not found.');
        }

        return view('dashboard/activities/form', [
            'role'     => $role,
            'activity' => $activity,
        ]);
    }

    public function store()
    {
        $role    = $this->manageRole();
        $payload = $this->activityFields();
        if ($payload === null) {
            return redirect()->to('/' . $role . '/activities/new')->withInput();
        }

        $banner = $this->saveBanner(null);
        if ($banner === false) {
            return redirect()->to('/' . $role . '/activities/new')->withInput();
        }

        $model = new BarangayActivityModel();
        $createdBy = (int) session()->get('user_id') ?: null;
        $activityId = (int) $model->insert($payload + [
            'banner_path'      => $banner,
            'notify_residents' => 0,
            'created_by'       => $createdBy,
        ]);
        $this->syncActivityCalendar($payload + ['id' => $activityId, 'created_by' => $createdBy]);

        $this->notifyResidents($payload['title'], $payload['conducted_date'], $payload['status']);

        return redirect()->to('/' . $role . '/activities')
            ->with('success', 'Activity "' . $payload['title'] . '" added and residents notified.');
    }

    public function update(int $id)
    {
        $role     = $this->manageRole();
        $model    = new BarangayActivityModel();
        $existing = $model->find($id);
        if (! $existing) {
            return redirect()->to('/' . $role . '/activities')->with('error', 'Activity not found.');
        }

        $payload = $this->activityFields();
        if ($payload === null) {
            return redirect()->to('/' . $role . '/activities/edit/' . $id)->withInput();
        }

        $banner = $this->saveBanner($existing['banner_path'] ?? null);
        if ($banner === false) {
            return redirect()->to('/' . $role . '/activities/edit/' . $id)->withInput();
        }

        $model->update($id, $payload + ['banner_path' => $banner]);
        $this->syncActivityCalendar($payload + [
            'id'         => $id,
            'created_by' => $existing['created_by'] ?? session()->get('user_id'),
        ]);

        return redirect()->to('/' . $role . '/activities')
            ->with('success', 'Activity updated.');
    }

    public function delete(int $id)
    {
        $role     = $this->manageRole();
        $model    = new BarangayActivityModel();
        $existing = $model->find($id);
        if ($existing) {
            $this->deleteBannerFile($existing['banner_path'] ?? null);
            $model->delete($id);
            (new \App\Models\ScheduleModel())->deleteMarkedEvent('[activity:' . $id . ']');
        }

        return redirect()->to('/' . $role . '/activities')
            ->with('success', 'Activity removed. It no longer appears for residents.');
    }

    public function resident()
    {
        $model      = new BarangayActivityModel();
        $model->syncDateStatuses();
        $userId     = (int) session()->get('user_id');
        $activities = $model->visibleToResidents();
        foreach ($activities as &$activity) {
            $activity['registration']       = $model->registrationFor((int) $activity['id'], $userId);
            $activity['registration_count'] = $model->registrationCount((int) $activity['id']);
            $activity['requirements_list']  = BarangayActivityModel::parseRequirements($activity['requirements'] ?? null);
        }
        unset($activity);

        $search = \App\Libraries\RecordSearch::term();
        $activities = \App\Libraries\RecordSearch::filter(
            $activities,
            $search,
            static fn(array $activity): string => implode(' ', [
                (string) ($activity['title'] ?? ''),
                (string) ($activity['category'] ?? ''),
                (string) ($activity['venue'] ?? ''),
                (string) ($activity['status'] ?? ''),
                (string) ($activity['description'] ?? ''),
            ]),
            static fn(array $activity): array => [
                $activity['conducted_date'] ?? null,
                $activity['activity_date'] ?? null,
                $activity['start_date'] ?? null,
                $activity['end_date'] ?? null,
            ]
        );

        return view('dashboard/resident/activities', [
            'activities' => $activities,
            'search'     => $search,
        ]);
    }

    public function registrations(int $id)
    {
        $role  = $this->manageRole();
        $model = new BarangayActivityModel();
        $activity = $model->find($id);
        if (! $activity) {
            return redirect()->to('/' . $role . '/activities')->with('error', 'Activity not found.');
        }

        $rows = [];
        $db   = \Config\Database::connect();
        if ($db->tableExists('barangay_activity_registrations')) {
            $rows = $db->table('barangay_activity_registrations r')
                ->select('r.*, u.first_name, u.last_name, u.username, u.email')
                ->join('users u', 'u.id = r.user_id', 'left')
                ->where('r.activity_id', $id)
                ->orderBy('r.created_at', 'ASC')
                ->get()
                ->getResultArray();
        }

        $search = \App\Libraries\RecordSearch::term();
        $rows = \App\Libraries\RecordSearch::filter(
            $rows,
            $search,
            static fn(array $row): string => implode(' ', [
                (string) ($row['first_name'] ?? ''),
                (string) ($row['last_name'] ?? ''),
                (string) ($row['username'] ?? ''),
                (string) ($row['email'] ?? ''),
                (string) ($row['notes'] ?? ''),
                (string) ($row['status'] ?? ''),
                (string) ($row['requirements_submitted'] ?? ''),
            ]),
            static fn(array $row): array => [$row['created_at'] ?? null]
        );

        return view('dashboard/activities/registrations', [
            'role'          => $role,
            'activity'      => $activity,
            'registrations' => $rows,
            'reqList'       => BarangayActivityModel::parseRequirements($activity['requirements'] ?? null),
            'search'        => $search,
        ]);
    }

    public function updateRegistration(int $regId)
    {
        $newStatus = (string) $this->request->getPost('status');
        if (! in_array($newStatus, ['approved', 'rejected'], true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $reason = trim((string) $this->request->getPost('rejection_reason'));
        if ($newStatus === 'rejected' && $reason === '') {
            return redirect()->back()->with('error', 'Please provide a reason before rejecting this registration.');
        }

        $db  = \Config\Database::connect();
        $reg = $db->table('barangay_activity_registrations')->where('id', $regId)->get()->getRowArray();
        if (! $reg) {
            return redirect()->back()->with('error', 'Registration not found.');
        }

        $db->table('barangay_activity_registrations')->where('id', $regId)->update([
            'status'           => $newStatus,
            'rejection_reason' => $newStatus === 'rejected' ? $reason : null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $activity = (new BarangayActivityModel())->find((int) $reg['activity_id']);
        if ($activity) {
            $message = $newStatus === 'approved'
                ? 'Your registration for "' . $activity['title'] . '" has been approved.'
                : 'Your registration for "' . $activity['title'] . '" was not approved. Reason: ' . $reason;
            $registrant = (new \App\Models\UserModel())->select('role')->find((int) $reg['user_id']);
            $activityLink = strtolower((string) ($registrant['role'] ?? '')) === 'council'
                ? '/council/activities'
                : '/resident/activities';
            NotificationModel::push(
                (int) $reg['user_id'],
                'activity_registration_' . $newStatus,
                'Registration ' . ucfirst($newStatus) . ' — ' . $activity['title'],
                $message,
                $activityLink
            );
        }

        return redirect()->back()->with('success', 'Registration ' . $newStatus . '.');
    }

    public function join(int $id)
    {
        $userId = (int) session()->get('user_id');
        $model  = new BarangayActivityModel();
        $model->syncDateStatuses();
        $activity = $model->find($id);

        if (! $activity || ! in_array($activity['status'] ?? '', ['Active', 'Upcoming', 'Posted'], true)) {
            return redirect()->to($this->activitiesHome())->with('error', 'This activity is not open for registration.');
        }

        $user = (new \App\Models\UserModel())->find($userId);
        $age = resident_census_age($user);
        if (! age_within_inclusive_range($age, $activity['min_age'] ?? null, $activity['max_age'] ?? null)) {
            return redirect()->to($this->activitiesHome())->with('error', 'You are not within the required age range for this activity.');
        }
        $educationError = BarangayActivityModel::educationEligibilityError($user, $activity);
        if ($educationError !== null) {
            return redirect()->to($this->activitiesHome())->with('error', $educationError);
        }

        if (! empty($activity['end_date']) && $activity['end_date'] < date('Y-m-d')) {
            return redirect()->to($this->activitiesHome())->with('error', 'Registration for this activity has already closed.');
        }

        if ($model->registrationFor($id, $userId)) {
            return redirect()->to($this->activitiesHome())->with('error', 'You are already registered for this activity.');
        }

        $requirements = BarangayActivityModel::parseRequirements($activity['requirements'] ?? null);
        $uploads = array_values(array_filter(
            $requirements,
            static fn (string $requirement): bool => BarangayActivityModel::isFileRequirement($requirement)
        ));
        $files = $this->request->getFileMultiple('attachments') ?? [];
        $attachments = [];
        foreach ($uploads as $index => $requirement) {
            $file = $files[$index] ?? null;
            if (! $file || ! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                return redirect()->to($this->activitiesHome())->with('error', 'Please attach all required documents and images before joining.');
            }
            $kind = BarangayActivityModel::requirementKind($requirement);
            $allowedMimes = $kind === 'image'
                ? ['image/jpeg', 'image/png', 'image/webp']
                : ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            if ($file->getSize() > 5 * 1024 * 1024 || ! in_array($file->getMimeType(), $allowedMimes, true)) {
                return redirect()->to($this->activitiesHome())->with('error', $kind === 'image'
                    ? 'Each image must be a JPG, PNG, or WebP file up to 5 MB.'
                    : 'Each document must be a JPG, PNG, WebP, or PDF file up to 5 MB.');
            }
            $directory = FCPATH . 'uploads/barangay_activities/';
            if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                return redirect()->to($this->activitiesHome())->with('error', 'The upload folder could not be created.');
            }
            $fileName = $file->getRandomName();
            $file->move($directory, $fileName);
            $attachments[] = ['requirement' => $requirement, 'path' => 'barangay_activities/' . $fileName];
        }

        $submitted = $this->request->getPost('requirements') ?? [];
        $submitted = is_array($submitted) ? array_filter(array_map('trim', $submitted)) : [];

        \Config\Database::connect()->table('barangay_activity_registrations')->insert([
            'activity_id'            => $id,
            'user_id'                => $userId,
            'status'                 => 'pending',
            'requirements_submitted' => $submitted !== [] ? implode(', ', $submitted) : null,
            'attachments'            => $attachments !== [] ? json_encode($attachments) : null,
            'notes'                  => trim((string) $this->request->getPost('notes')) ?: null,
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ]);

        $this->notifyOfficeOfJoin($activity, $userId);

        return redirect()->to($this->activitiesHome())->with('success', 'You registered for "' . $activity['title'] . '". The barangay office will review it.');
    }

    public function unjoin(int $id)
    {
        $userId = (int) session()->get('user_id');
        $db     = \Config\Database::connect();
        $row    = $db->table('barangay_activity_registrations')
            ->where('activity_id', $id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->get()
            ->getRowArray();

        if ($row) {
            $db->table('barangay_activity_registrations')->where('id', $row['id'])->delete();
        }

        return redirect()->to($this->activitiesHome())->with('success', 'Registration cancelled.');
    }

    private function activitiesHome(): string
    {
        return session()->get('role') === 'council' ? '/council/activities' : '/resident/activities';
    }

    private function manageRole(): string
    {
        $role = strtolower((string) session()->get('role'));

        return in_array($role, ['admin', 'secretary', 'captain', 'sk'], true) ? $role : 'secretary';
    }

    /** @return array<string, mixed>|null */
    private function activityFields(): ?array
    {
        $title    = trim((string) $this->request->getPost('title'));
        $category = trim((string) $this->request->getPost('category'));
        if ($title === '' || ! in_array($category, BarangayActivityModel::CATEGORIES, true)) {
            session()->setFlashdata('error', 'Activity name and category are required.');
            return null;
        }
        if (mb_strlen($title) > 200) {
            session()->setFlashdata('error', 'Activity name must be 200 characters or less.');
            return null;
        }

        $cleanup       = BarangayActivityModel::isCleanupDrive($category, $title);
        $startDate     = $cleanup ? '' : trim((string) $this->request->getPost('start_date'));
        $endDate       = $cleanup ? '' : trim((string) $this->request->getPost('end_date'));
        $conductedDate = trim((string) $this->request->getPost('conducted_date'));
        $venue         = trim((string) $this->request->getPost('venue'));
        $description   = trim((string) $this->request->getPost('description'));

        if (! $cleanup && ($startDate === '' || ! $this->validDate($startDate))) {
            session()->setFlashdata('error', 'A valid start date for submitting requirements is required.');
            return null;
        }
        if ($endDate !== '' && ! $this->validDate($endDate)) {
            session()->setFlashdata('error', 'The end date for submitting requirements is not valid.');
            return null;
        }
        if ($conductedDate === '' || ! $this->validDate($conductedDate)) {
            session()->setFlashdata('error', 'The date the activity will be conducted is required.');
            return null;
        }
        if (! $cleanup && $conductedDate < $startDate) {
            session()->setFlashdata('error', 'The conducted date cannot be before the start date.');
            return null;
        }
        if ($venue === '') {
            session()->setFlashdata('error', 'Venue is required.');
            return null;
        }
        if (mb_strlen($venue) > 255) {
            session()->setFlashdata('error', 'Venue must be 255 characters or less.');
            return null;
        }
        if (mb_strlen($description) > 5000) {
            session()->setFlashdata('error', 'Description must be 5000 characters or less.');
            return null;
        }

        $minAge = $this->normalizeAge($this->request->getPost('min_age'));
        $maxAge = $this->normalizeAge($this->request->getPost('max_age'));
        if ($minAge !== null && $maxAge !== null && $minAge > $maxAge) {
            session()->setFlashdata('error', 'Minimum age cannot be greater than maximum age.');
            return null;
        }

        $requirements = $cleanup
            ? null
            : BarangayActivityModel::encodeRequirements(BarangayActivityModel::collectPostedRequirements($this->request));

        $eligibilityError = null;
        $eligibility = BarangayActivityModel::collectPostedEligibility($this->request, $eligibilityError);
        if ($eligibility === null) {
            session()->setFlashdata('error', $eligibilityError ?: 'Eligibility settings are not valid.');
            return null;
        }

        $today  = date('Y-m-d');
        $status = $conductedDate < $today ? 'Completed' : ($conductedDate === $today ? 'Active' : 'Upcoming');

        $payload = [
            'title'               => $title,
            'category'            => $category,
            'description'         => $description !== '' ? $description : null,
            'requirements'        => $requirements,
            'start_date'          => $startDate !== '' ? $startDate : null,
            'end_date'            => $endDate !== '' ? $endDate : null,
            'conducted_date'      => $conductedDate,
            'activity_date'       => $conductedDate,
            'min_age'             => $minAge,
            'max_age'             => $maxAge,
            'venue'               => $venue,
            'target_participants' => max(0, (int) $this->request->getPost('target_participants')),
            'status'              => $status,
        ];

        $db = \Config\Database::connect();
        if ($db->tableExists('barangay_activities') && $db->fieldExists('eligibility_groups', 'barangay_activities')) {
            $payload = $payload + $eligibility;
        }

        return $payload;
    }

    private function normalizeAge($age): ?int
    {
        if ($age === null || $age === '') {
            return null;
        }
        $age = (int) $age;

        return ($age >= 0 && $age <= 120) ? $age : null;
    }

    private function residentAge(?array $user): ?int
    {
        return resident_census_age($user);
    }

    private function notifyOfficeOfJoin(array $activity, int $residentId): void
    {
        $resident = (new \App\Models\UserModel())->find($residentId);
        $name = trim(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? '')) ?: 'A resident';
        $staff = \Config\Database::connect()->table('users')
            ->select('id, role')
            ->whereIn('role', ['admin', 'secretary', 'captain'])
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        foreach ($staff as $person) {
            $link = '/' . $person['role'] . '/activities/registrations/' . (int) $activity['id'];
            NotificationModel::push(
                (int) $person['id'],
                'activity_join',
                'New registration — ' . $activity['title'],
                $name . ' registered for "' . $activity['title'] . '" and is waiting for approval.',
                $link
            );
        }
    }

    private function syncActivityCalendar(array $activity): void
    {
        $id = (int) ($activity['id'] ?? 0);
        $date = $activity['conducted_date'] ?? $activity['activity_date'] ?? '';
        if ($id <= 0 || $date === '') {
            return;
        }

        (new \App\Models\ScheduleModel())->upsertMarkedEvent('[activity:' . $id . ']', [
            'title'       => (string) ($activity['title'] ?? 'Barangay activity'),
            'description' => trim((string) ($activity['category'] ?? 'Activity') . ' at ' . ((string) ($activity['venue'] ?? 'Barangay Hall'))),
            'event_date'  => $date,
            'location'    => $activity['venue'] ?? 'Barangay Hall',
            'created_by'  => $activity['created_by'] ?? null,
        ]);
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);

        return $parsed instanceof \DateTime && $parsed->format('Y-m-d') === $date;
    }

    private function validTime(string $time): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $time) === 1;
    }

    /**
     * Store an optional banner. Returns the path to keep, null when none remains,
     * or false when the upload is invalid.
     *
     * @return string|null|false
     */
    private function saveBanner(?string $existingPath)
    {
        $file      = $this->request->getFile('banner');
        $hasUpload = $file && $file->getError() !== UPLOAD_ERR_NO_FILE;

        if ($hasUpload) {
            if (! $file->isValid()) {
                session()->setFlashdata('error', 'The banner image could not be uploaded.');
                return false;
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                session()->setFlashdata('error', 'Banner image must be 5 MB or smaller.');
                return false;
            }
            $mime = (string) $file->getMimeType();
            if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                session()->setFlashdata('error', 'Banner image must be a JPG, PNG, WEBP, or GIF file.');
                return false;
            }

            $directory = FCPATH . 'uploads/barangay_activities/';
            if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                session()->setFlashdata('error', 'The banner folder could not be created.');
                return false;
            }

            $fileName = $file->getRandomName();
            $file->move($directory, $fileName);
            $this->deleteBannerFile($existingPath);

            return 'barangay_activities/' . $fileName;
        }

        $existingPath = trim((string) $existingPath);

        return $existingPath !== '' ? $existingPath : null;
    }

    private function deleteBannerFile(?string $path): void
    {
        $path = str_replace('\\', '/', trim((string) $path));
        if ($path === '' || str_contains($path, '..')) {
            return;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }
        if (! str_starts_with($path, 'barangay_activities/')) {
            return;
        }

        $full = FCPATH . 'uploads/' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }

    private function notifyResidents(string $title, ?string $date, string $status): void
    {
        $db = \Config\Database::connect();
        $residents = $db->table('users')
            ->select('id')
            ->where('role', 'resident')
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        $when = $date ? ' on ' . date('M j, Y', strtotime($date)) : '';
        $body = 'A new barangay activity was added: "' . $title . '" (' . $status . ')' . $when . '. Open Brgy Activities to learn more and join.';
        $noticeTitle = 'New barangay activity: ' . $title;
        if (mb_strlen($noticeTitle) > 200) {
            $noticeTitle = mb_substr($noticeTitle, 0, 197) . '...';
        }

        foreach ($residents as $resident) {
            NotificationModel::push(
                (int) $resident['id'],
                'announcement',
                $noticeTitle,
                $body,
                '/resident/activities'
            );
        }
    }
}
