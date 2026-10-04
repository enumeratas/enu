<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConcernModel;
use App\Models\UserModel;

class ConcernController extends BaseController
{
    protected ConcernModel $model;

    public function __construct()
    {
        $this->model = new ConcernModel();
    }

    // ── Admin: Concerns list ──────────────────────────────────────────────────

    public function index()
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'])) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $db     = \Config\Database::connect();
        $status = $this->request->getGet('status') ?? '';
        $search = trim($this->request->getGet('search') ?? '');
        $page   = max(1, (int)($this->request->getGet('page') ?? 1));
        $perPage = 15;

        $builder = $db->table('concern_submissions')
            ->orderBy('created_at', 'DESC');

        if ($status !== '' && in_array($status, ['pending', 'approved', 'dismissed'])) {
            if ($status === 'approved') {
                $builder->whereIn('status', ['approved', 'resolved']);
            } else {
                $builder->where('status', $status);
            }
        }

        if ($search !== '') {
            $builder->where(\App\Libraries\RecordSearch::clause([
                'full_name',
                'email',
                'subject',
                'category',
            ], ['created_at', 'appointment_date'], $search), null, false);
        }

        $allConcerns = $builder->get()->getResultArray();
        $groups      = $this->groupConcernsByPerson($allConcerns);
        $total       = count($groups);
        $concernGroups = array_slice($groups, ($page - 1) * $perPage, $perPage);
        $concerns    = $allConcerns;

        // Stat counts
        $counts = [
            'all'       => $db->table('concern_submissions')->countAllResults(),
            'pending'   => $db->table('concern_submissions')->where('status', 'pending')->countAllResults(),
            'approved'  => $db->table('concern_submissions')->whereIn('status', ['approved', 'resolved'])->countAllResults(),
            'dismissed' => $db->table('concern_submissions')->where('status', 'dismissed')->countAllResults(),
        ];

        return view('dashboard/' . staff_view_folder($role) . '/concerns', [
            'role'        => $role,
            'pageTitle'   => 'Concerns',
            'active'      => 'concerns',
            'concerns'    => $concerns,
            'concernGroups' => $concernGroups,
            'counts'      => $counts,
            'total'       => $total,
            'currentPage' => $page,
            'perPage'     => $perPage,
            'filterStatus' => $status,
            'search'      => $search,
        ]);
    }

    // ── Public: send OTP to verify email before concern submission ───────────

    public function sendOtp()
    {
        $email = trim($this->request->getPost('email') ?? '');
        $name  = trim($this->request->getPost('full_name') ?? '');

        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please enter a valid email address.']);
        }

        if (empty($name)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please enter your full name.']);
        }

        $otp     = strval(random_int(100000, 999999));
        $expires = time() + 900; // 15 minutes

        session()->set([
            'concern_otp'         => $otp,
            'concern_otp_expires' => $expires,
            'concern_otp_email'   => $email,
        ]);

        try {
            (new \App\Libraries\EmailService())->sendConcernOtp($email, $name, $otp);
        } catch (\Throwable $e) {
            log_message('error', 'Concern OTP email failed: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Could not send verification email. Please try again.']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Verification code sent to ' . $email . '.']);
    }

    // ── Public: verify the OTP ────────────────────────────────────────────────

    public function verifyOtp()
    {
        $otp   = trim($this->request->getPost('otp') ?? '');
        $email = trim($this->request->getPost('email') ?? '');

        $storedOtp     = session()->get('concern_otp');
        $storedExpires = (int) session()->get('concern_otp_expires');
        $storedEmail   = session()->get('concern_otp_email');

        if (empty($storedOtp)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No verification code found. Please request a new one.']);
        }

        if ($storedEmail !== $email) {
            return $this->response->setJSON(['success' => false, 'message' => 'Email mismatch. Please request a new code.']);
        }

        if (time() > $storedExpires) {
            session()->remove(['concern_otp', 'concern_otp_expires', 'concern_otp_email']);
            return $this->response->setJSON(['success' => false, 'message' => 'Verification code has expired. Please request a new one.']);
        }

        if ($storedOtp !== $otp) {
            return $this->response->setJSON(['success' => false, 'message' => 'Incorrect verification code. Please try again.']);
        }

        // Mark as verified so storePublic() will accept the submission
        session()->set('concern_email_verified', $email);

        return $this->response->setJSON(['success' => true, 'message' => 'Email verified successfully.']);
    }

    // ── Public (unauthenticated): submit a concern/inquiry ────────────────────

    public function storePublic()
    {
        $fullName        = trim($this->request->getPost('full_name')         ?? '');
        $email           = trim($this->request->getPost('email')             ?? '');
        $rawContactNumber = $this->request->getPost('contact_number');
        $contactNumber    = $this->cleanContactNumber($rawContactNumber);
        $category        = trim($this->request->getPost('category')          ?? '');
        $subject         = trim($this->request->getPost('subject')           ?? '');
        $message         = trim($this->request->getPost('message')           ?? '');
        $appointmentDate = $this->request->getPost('appointment_date') ?: null;
        $appointmentTime = $this->request->getPost('appointment_time') ?: null;

        // ── Validate required fields ──────────────────────────────────────────
        if (empty($fullName) || empty($email) || empty($subject) || empty($message)) {
            return redirect()->back()
                ->with('concern_error', 'Please fill in all required fields (Name, Email, Subject, and Message).')
                ->withInput();
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()
                ->with('concern_error', 'Please enter a valid email address.')
                ->withInput();
        }

        if ($rawContactNumber !== null && $rawContactNumber !== '' && $contactNumber === null) {
            return redirect()->back()
                ->with('concern_error', 'Contact number must contain exactly 11 digits.')
                ->withInput();
        }

        $slot = $this->prepareAppointment($appointmentDate, $appointmentTime);
        if ($slot['error'] !== null) {
            return redirect()->back()
                ->with('concern_error', $slot['error'])
                ->withInput();
        }
        $appointmentDate = $slot['date'];
        $appointmentTime = $slot['time'];
        if ($appointmentDate) {
            $duplicate = $this->duplicateAppointmentMessage([
                'email'            => $email,
                'full_name'        => $fullName,
                'contact_number'   => $contactNumber,
                'subject'          => $subject,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
            ]);
            if ($duplicate !== null) {
                return redirect()->back()->with('concern_error', $duplicate)->withInput();
            }
        }

        // ── Verify email was OTP-confirmed before this submission ────────────
        // Official users can create a request from the dashboard without an extra OTP step.
        $verifiedEmail = session()->get('concern_email_verified');
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'], true) && $verifiedEmail !== $email) {
            return redirect()->back()
                ->with('concern_error', 'Email address not verified. Please verify your email before submitting.')
                ->withInput();
        }

        // ── Save to DB ────────────────────────────────────────────────────────
        $db = \Config\Database::connect();
        $resident = $this->findResidentForConcern(['email' => $email]);
        $this->model->insert([
            'user_id'          => $resident['id'] ?? null,
            'full_name'        => $fullName,
            'email'            => $email,
            'contact_number'   => $contactNumber ?: null,
            'category'         => $category       ?: null,
            'subject'          => $subject,
            'message'          => $message,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime ?: null,
            'status'           => 'pending',
        ]);

        // ── Notify all active secretary + captain officials ───────────────────
        $officials = $db->table('users')
            ->whereIn('role', ['secretary', 'captain', 'admin'])
            ->where('status', 'active')
            ->get()->getResultArray();

        $body = $fullName . ' submitted a concern: "' . $subject . '".'
            . ($appointmentDate ? ' Requested appointment: ' . date('M d, Y', strtotime($appointmentDate)) . '.' : '');

        foreach ($officials as $off) {
            \App\Models\NotificationModel::push(
                (int) $off['id'],
                'new_concern',
                'New Concern: ' . $subject,
                $body,
                '/' . $off['role'] . '/notifications'
            );
        }

        // ── Build success message ─────────────────────────────────────────────
        // Clear OTP session keys — verified submission consumed
        session()->remove(['concern_otp', 'concern_otp_expires', 'concern_otp_email', 'concern_email_verified']);
        $msg = 'Your concern has been submitted successfully. The barangay will contact you at ' . $email . '.';
        if ($appointmentDate) {
            $msg .= ' Requested appointment: ' . date('F d, Y', strtotime($appointmentDate));
            if ($appointmentTime) {
                $msg .= ' at ' . date('h:i A', strtotime($appointmentTime));
            }
            $msg .= '.';
        }

        $redirectPath = in_array($role, ['secretary', 'captain', 'admin'], true)
            ? '/' . $role . '/concerns'
            : '/';
        $flashKey = in_array($role, ['secretary', 'captain', 'admin'], true)
            ? 'success'
            : 'concern_success';

        return redirect()->to($redirectPath)->with($flashKey, $msg);
    }

    public function skForm()
    {
        if (session()->get('role') !== 'sk') {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $user = (new UserModel())->find((int) session()->get('user_id'));
        $search = \App\Libraries\RecordSearch::term();
        return view('dashboard/sk/concerns', [
            'account'  => $user ?? [],
            'concerns' => $this->filterOwnConcerns($user ?? [], $search),
            'search'   => $search,
        ]);
    }

    public function cancelOwn(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['resident', 'sk'], true)) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $concern = $this->model->find($id);
        $userId = (int) session()->get('user_id');
        if (! $concern || (int) ($concern['user_id'] ?? 0) !== $userId) {
            return redirect()->to('/' . $role . '/concerns')->with('error', 'Request not found.');
        }
        if (($concern['status'] ?? '') !== 'pending') {
            return redirect()->to('/' . $role . '/concerns')->with('error', 'Only a pending request can be cancelled.');
        }

        $db = \Config\Database::connect();
        $scheduleId = (int) ($concern['schedule_id'] ?? 0);
        if ($scheduleId <= 0) {
            $scheduleId = $this->findScheduleIdForConcern($concern, $id);
        }
        $this->model->delete($id);
        if ($scheduleId > 0) {
            $db->table('schedules')->where('id', $scheduleId)->delete();
        }
        if ($db->fieldExists('concern_id', 'schedules')) {
            $db->table('schedules')->where('concern_id', $id)->delete();
        }

        return redirect()->to('/' . $role . '/concerns')->with('success', 'Request cancelled. That appointment slot is open again.');
    }

    public function slots()
    {
        $date = trim((string) $this->request->getGet('date'));
        $excludeScheduleId = (int) ($this->request->getGet('exclude_schedule_id') ?? 0);
        $excludeConcernId = (int) ($this->request->getGet('exclude_concern_id') ?? 0);
        $times = [];

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            foreach ($this->appointmentSlotValues() as $value) {
                $times[] = [
                    'value'     => $value,
                    'label'     => date('g:i A', strtotime($value)),
                    'available' => ! $this->hasAppointmentConflict($date, $value, $excludeScheduleId, $excludeConcernId),
                ];
            }
        }

        return $this->response->setJSON([
            'dates' => $this->fullyBookedDates($excludeScheduleId, $excludeConcernId),
            'times' => $times,
        ]);
    }

    public function residentForm()
    {
        $user = (new UserModel())->find((int) session()->get('user_id'));
        $search = \App\Libraries\RecordSearch::term();
        return view('dashboard/sk/concerns', [
            'account'  => $user ?? [],
            'role'     => 'resident',
            'concerns' => $this->filterOwnConcerns($user ?? [], $search),
            'search'   => $search,
        ]);
    }

    public function storeResident()
    {
        return $this->storeAuthenticatedConcern('resident');
    }

    public function storeSk()
    {
        return $this->storeAuthenticatedConcern('sk');
    }

    private function storeAuthenticatedConcern(string $role)
    {
        if (session()->get('role') !== $role) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $user = (new UserModel())->find((int) session()->get('user_id'));
        $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $email = trim((string) ($user['email'] ?? ''));
        $subject = trim((string) $this->request->getPost('subject'));
        $message = trim((string) $this->request->getPost('message'));
        $appointmentDate = $this->request->getPost('appointment_date') ?: null;
        $appointmentTime = $this->request->getPost('appointment_time') ?: null;
        $contact = $this->cleanContactNumber($this->request->getPost('contact_number'));

        if ($subject === '' || $message === '') {
            return redirect()->back()->with('error', 'Please fill in the subject and message.')->withInput();
        }
        $slot = $this->prepareAppointment($appointmentDate, $appointmentTime);
        if ($slot['error'] !== null) {
            return redirect()->back()->with('error', $slot['error'])->withInput();
        }
        $appointmentDate = $slot['date'];
        $appointmentTime = $slot['time'];
        if ($appointmentDate) {
            $duplicate = $this->duplicateAppointmentMessage([
                'user_id'          => (int) session()->get('user_id'),
                'email'            => $email,
                'full_name'        => $fullName,
                'contact_number'   => $contact,
                'subject'          => $subject,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
            ]);
            if ($duplicate !== null) {
                return redirect()->back()->with('error', $duplicate)->withInput();
            }
        }
        if ($this->request->getPost('contact_number') && $contact === null) {
            return redirect()->back()->with('error', 'Contact number must contain exactly 11 digits.')->withInput();
        }

        $db = \Config\Database::connect();
        $this->model->insert([
            'user_id' => (int) session()->get('user_id'),
            'full_name' => $fullName,
            'email' => $email,
            'contact_number' => $contact,
            'category' => trim((string) $this->request->getPost('category')) ?: null,
            'subject' => $subject,
            'message' => $message,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'status' => 'pending',
        ]);

        foreach ($db->table('users')->whereIn('role', ['secretary', 'captain', 'admin'])->where('status', 'active')->get()->getResultArray() as $official) {
            \App\Models\NotificationModel::push((int) $official['id'], 'new_concern', 'New Concern: ' . $subject, $fullName . ' submitted a concern.', '/' . $official['role'] . '/notifications');
        }

        return redirect()->to('/' . $role . '/concerns')->with('success', 'Your appointment/concern has been submitted successfully.');
    }

    // ── Admin: Create a concern appointment schedule ──────────────────────────

    public function schedule(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'])) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $concern = $this->model->find($id);
        if (! $concern) {
            return redirect()->to('/' . $role . '/notifications')->with('error', 'Concern not found.');
        }

        $newDate = $this->request->getPost('appointment_date');
        $newTime = $this->request->getPost('appointment_time') ?: null;
        $notes    = trim($this->request->getPost('schedule_notes') ?? '');

        $existingScheduleId = $this->findScheduleIdForConcern($concern, $id);
        $slot = $this->prepareAppointment($newDate, $newTime, $existingScheduleId, $id, true);
        if ($slot['error'] !== null) {
            return redirect()->back()
                ->with('concern_form_error', $slot['error'])
                ->withInput();
        }
        $newDate = $slot['date'];
        $newTime = $slot['time'];
        $duplicate = $this->duplicateAppointmentMessage([
            'user_id'          => $concern['user_id'] ?? null,
            'email'            => $concern['email'] ?? '',
            'full_name'        => $concern['full_name'] ?? '',
            'contact_number'   => $concern['contact_number'] ?? '',
            'subject'          => $concern['subject'] ?? '',
            'appointment_date' => $newDate,
            'appointment_time' => $newTime,
        ], $id);
        if ($duplicate !== null) {
            return redirect()->back()->with('concern_form_error', $duplicate)->withInput();
        }

        $db = \Config\Database::connect();
        $resident = $this->findResidentForConcern($concern);
        $this->model->update($id, [
            'user_id'          => $resident['id'] ?? null,
            'appointment_date' => $newDate,
            'appointment_time' => $newTime,
            'status'           => 'pending',
            'notes'            => $notes ?: null,
        ]);

        $userId = (int) session()->get('user_id');
        $scheduleData = [
            'title'       => 'Concern Appointment: ' . ($concern['subject'] ?: 'Barangay Concern'),
            'description' => 'Concern for ' . ($concern['full_name'] ?: 'Resident') . '.',
            'event_date'  => $newDate,
            'start_time'  => $newTime,
            'end_time'    => null,
            'event_type'  => 'appointment',
            'color'       => '#1d2448',
            'location'    => 'Barangay Hall',
            'created_by'  => $userId,
            'concern_id'  => $id,
            'visibility'  => 'private',
            'shared_with' => null,
        ];
        if ($existingScheduleId > 0) {
            $db->table('schedules')->where('id', $existingScheduleId)->update($scheduleData);
        } else {
            $db->table('schedules')->insert($scheduleData);
            $existingScheduleId = (int) $db->insertID();
        }
        $this->model->update($id, ['schedule_id' => $existingScheduleId]);

        $this->notifyResidentAppointment($resident['id'] ?? null, $newDate, $newTime, 'Appointment Scheduled');

        try {
            $formattedDate = date('F d, Y', strtotime($newDate));
            $formattedTime = $newTime ? date('g:i A', strtotime($newTime)) : 'To be confirmed';

            (new \App\Libraries\EmailService())->sendConcernReschedule(
                $concern['email'],
                $concern['full_name'],
                $concern['subject'],
                $formattedDate,
                $formattedTime,
                $notes
            );
        } catch (\Throwable $e) {
            log_message('error', 'Concern appointment scheduling email failed: ' . $e->getMessage());
        }

        return redirect()->to('/' . $role . '/concern/' . $id)
            ->with('success', 'Appointment scheduled for ' . date('F d, Y', strtotime($newDate)) . '. The submitter has been notified.');
    }

    // ── Admin: Reschedule appointment for a resolved/dismissed concern ────────

    public function reschedule(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'])) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $concern = $this->model->find($id);
        if (! $concern) {
            return redirect()->to('/' . $role . '/concerns')->with('error', 'Concern not found.');
        }

        $newDate  = $this->request->getPost('appointment_date');
        $newTime  = $this->request->getPost('appointment_time') ?: null;
        $notes    = trim($this->request->getPost('response') ?? '');

        $existingScheduleId = $this->findScheduleIdForConcern($concern, $id);
        $slot = $this->prepareAppointment($newDate, $newTime, $existingScheduleId, $id, true);
        if ($slot['error'] !== null) {
            return redirect()->back()
                ->with('concern_form_error', $slot['error'])
                ->withInput();
        }
        $newDate = $slot['date'];
        $newTime = $slot['time'];
        $duplicate = $this->duplicateAppointmentMessage([
            'user_id'          => $concern['user_id'] ?? null,
            'email'            => $concern['email'] ?? '',
            'full_name'        => $concern['full_name'] ?? '',
            'contact_number'   => $concern['contact_number'] ?? '',
            'subject'          => $concern['subject'] ?? '',
            'appointment_date' => $newDate,
            'appointment_time' => $newTime,
        ], $id);
        if ($duplicate !== null) {
            return redirect()->back()->with('concern_form_error', $duplicate)->withInput();
        }

        $db = \Config\Database::connect();
        $resident = $this->findResidentForConcern($concern);

        $this->model->update($id, [
            'appointment_date' => $newDate,
            'appointment_time' => $newTime,
            'user_id'          => $resident['id'] ?? null,
            'status'           => 'pending',
            'notes'            => $notes ?: null,
        ]);

        $scheduleData = [
            'title'       => 'Concern Appointment: ' . ($concern['subject'] ?: 'Barangay Concern'),
            'description' => 'Concern for ' . ($concern['full_name'] ?: 'Resident') . '.',
            'event_date'  => $newDate,
            'start_time'  => $newTime,
            'end_time'    => null,
            'event_type'  => 'appointment',
            'color'       => '#1d2448',
            'location'    => 'Barangay Hall',
            'concern_id'  => $id,
        ];
        if ($existingScheduleId > 0) {
            $db->table('schedules')->where('id', $existingScheduleId)->update($scheduleData);
        } else {
            $scheduleData['created_by'] = (int) session()->get('user_id');
            $scheduleData['visibility'] = 'private';
            $scheduleData['shared_with'] = null;
            $db->table('schedules')->insert($scheduleData);
            $existingScheduleId = (int) $db->insertID();
        }
        $this->model->update($id, ['schedule_id' => $existingScheduleId]);

        $this->notifyResidentAppointment($resident['id'] ?? null, $newDate, $newTime, 'Appointment Rescheduled');

        // Notify the submitter of the new schedule
        try {
            $formattedDate = date('F d, Y', strtotime($newDate));
            $formattedTime = $newTime ? date('g:i A', strtotime($newTime)) : 'To be confirmed';

            (new \App\Libraries\EmailService())->sendConcernReschedule(
                $concern['email'],
                $concern['full_name'],
                $concern['subject'],
                $formattedDate,
                $formattedTime,
                $notes
            );
        } catch (\Throwable $e) {
            log_message('error', 'Concern reschedule email failed: ' . $e->getMessage());
        }

        return redirect()->to('/' . $role . '/concern/' . $id)
            ->with('success', 'Appointment rescheduled to ' . date('F d, Y', strtotime($newDate)) . '. The submitter has been notified.');
    }

    // ── Admin: View a single concern for review ───────────────────────────────

    public function show(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'])) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $concern = $this->model->find($id);
        if (! $concern) {
            return redirect()->to('/' . $role . '/notifications')->with('error', 'Concern not found.');
        }

        return view('dashboard/' . staff_view_folder($role) . '/concern_review', [
            'concern'   => $concern,
            'role'      => $role,
            'pageTitle' => 'Review Concern',
        ]);
    }

    public function availability()
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'], true)) {
            return $this->response->setStatusCode(403)->setJSON(['available' => false, 'message' => 'Unauthorized.']);
        }

        $date = $this->request->getGet('date');
        $time = $this->request->getGet('time') ?: null;
        if (! $date) {
            return $this->response->setJSON(['available' => false, 'message' => 'Select an appointment date.']);
        }

        $excludeScheduleId = (int) ($this->request->getGet('exclude_schedule_id') ?? 0);
        $busy = $this->isDateFullyBooked($date, $excludeScheduleId)
            || ($time && $this->hasAppointmentConflict($date, $time, $excludeScheduleId));
        return $this->response->setJSON([
            'available' => ! $busy,
            'message'   => $busy
                ? ($this->isDateFullyBooked($date, $excludeScheduleId)
                    ? 'This date is fully booked. Please choose another date.'
                    : 'This time is already occupied. Please choose another time.')
                : ($time ? 'Available schedule.' : 'Date available. Select a time if needed.'),
        ]);
    }

    public function unavailableDates()
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'], true)) {
            return $this->response->setStatusCode(403)->setJSON(['dates' => []]);
        }

        $excludeScheduleId = (int) ($this->request->getGet('exclude_schedule_id') ?? 0);
        $excludeConcernId = (int) ($this->request->getGet('exclude_concern_id') ?? 0);

        return $this->response->setJSON([
            'dates' => $this->fullyBookedDates($excludeScheduleId, $excludeConcernId),
        ]);
    }

    private function groupConcernsByPerson(array $concerns): array
    {
        $groups = [];
        foreach ($concerns as $row) {
            $key = $this->personKey($row);
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'full_name'       => $row['full_name'] ?? '',
                    'email'           => $row['email'] ?? '',
                    'contact_number'  => $row['contact_number'] ?? '',
                    'appointments'    => [],
                ];
            }
            $groups[$key]['appointments'][] = $row;
        }

        return array_values($groups);
    }

    private function personKey(array $row): string
    {
        $userId = (int) ($row['user_id'] ?? 0);
        if ($userId > 0) {
            return 'u:' . $userId;
        }

        $email = strtolower(trim((string) ($row['email'] ?? '')));
        if ($email !== '') {
            return 'e:' . $email;
        }

        $name = preg_replace('/\s+/', ' ', strtolower(trim((string) ($row['full_name'] ?? ''))));
        $contact = preg_replace('/\D+/', '', (string) ($row['contact_number'] ?? ''));

        return 'n:' . $name . '|c:' . $contact;
    }

    private function normalizeSubject(string $subject): string
    {
        return preg_replace('/\s+/', ' ', strtolower(trim($subject)));
    }

    private function normalizeTime(?string $time): ?string
    {
        $time = trim((string) $time);
        if ($time === '') {
            return null;
        }

        $stamp = strtotime('1970-01-01 ' . $time);

        return $stamp ? date('H:i', $stamp) : null;
    }

    private function filterOwnConcerns(array $user, string $search): array
    {
        return \App\Libraries\RecordSearch::filter(
            $this->concernsForAccount($user),
            $search,
            static fn(array $row): string => implode(' ', [
                (string) ($row['subject'] ?? ''),
                (string) ($row['category'] ?? ''),
                (string) ($row['notes'] ?? ''),
                (string) ($row['status'] ?? ''),
            ]),
            static fn(array $row): array => [
                $row['created_at'] ?? null,
                $row['appointment_date'] ?? null,
            ]
        );
    }

    private function concernsForAccount(array $user): array
    {
        $userId = (int) ($user['id'] ?? 0);
        $email = strtolower(trim((string) ($user['email'] ?? '')));
        if ($userId <= 0 && $email === '') {
            return [];
        }

        $builder = \Config\Database::connect()->table('concern_submissions');
        $builder->groupStart();
        if ($userId > 0) {
            $builder->where('user_id', $userId);
        }
        if ($email !== '') {
            $userId > 0
                ? $builder->orWhere('email', $email)
                : $builder->where('email', $email);
        }
        $builder->groupEnd()->orderBy('id', 'DESC');

        return $builder->get()->getResultArray();
    }

    /** @return list<string> */
    private function appointmentSlotValues(): array
    {
        $values = [];
        for ($hour = 8; $hour <= 16; $hour++) {
            $values[] = sprintf('%02d:00', $hour);
        }

        return $values;
    }

    /**
     * @return array{date: ?string, time: ?string, error: ?string}
     */
    private function prepareAppointment(?string $date, ?string $time, int $excludeScheduleId = 0, int $excludeConcernId = 0, bool $required = false): array
    {
        $date = trim((string) $date);
        $postedTime = trim((string) $time);
        if ($date === '') {
            if ($required) {
                return ['date' => null, 'time' => null, 'error' => 'Please select an appointment date.'];
            }
            if ($postedTime !== '') {
                return ['date' => null, 'time' => null, 'error' => 'Choose a date for that appointment time.'];
            }

            return ['date' => null, 'time' => null, 'error' => null];
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
            return ['date' => null, 'time' => null, 'error' => 'Please select a valid appointment date.'];
        }
        if ($date < date('Y-m-d', strtotime('+1 day'))) {
            return ['date' => $date, 'time' => null, 'error' => 'Appointment date must be at least one day from today.'];
        }
        if ((int) date('w', strtotime($date)) === 0) {
            return ['date' => $date, 'time' => null, 'error' => 'Appointments are not available on Sundays.'];
        }

        $normalized = $this->normalizeTime($postedTime);
        if ($normalized === null || ! in_array($normalized, $this->appointmentSlotValues(), true)) {
            return ['date' => $date, 'time' => null, 'error' => 'Choose an open appointment time. Booked times stay unavailable.'];
        }
        if ($this->hasAppointmentConflict($date, $normalized, $excludeScheduleId, $excludeConcernId)) {
            return ['date' => $date, 'time' => $normalized, 'error' => 'That appointment time is already booked. Please choose another open slot.'];
        }

        return ['date' => $date, 'time' => $normalized, 'error' => null];
    }

    /** @return list<string> */
    private function fullyBookedDates(int $excludeScheduleId = 0, int $excludeConcernId = 0): array
    {
        $db = \Config\Database::connect();
        $scheduleQuery = $db->table('schedules')
            ->select('event_date')
            ->where('event_date IS NOT NULL', null, false);
        if ($excludeScheduleId > 0) {
            $scheduleQuery->where('id !=', $excludeScheduleId);
        }

        $dates = array_column($scheduleQuery->get()->getResultArray(), 'event_date');
        $dates = array_merge($dates, array_column(
            $db->table('blotter_reports')
                ->select('appointment_date, hearing_date')
                ->groupStart()
                    ->where('appointment_date IS NOT NULL', null, false)
                    ->orWhere('hearing_date IS NOT NULL', null, false)
                ->groupEnd()
                ->get()->getResultArray(),
            'appointment_date'
        ));

        foreach (
            $db->table('blotter_reports')
                ->select('hearing_date')
                ->where('hearing_date IS NOT NULL', null, false)
                ->get()->getResultArray() as $hearing
        ) {
            $dates[] = $hearing['hearing_date'];
        }

        $concernQuery = $db->table('concern_submissions')
            ->select('appointment_date')
            ->where('appointment_date IS NOT NULL', null, false)
            ->whereIn('status', ['pending', 'approved']);
        if ($excludeConcernId > 0) {
            $concernQuery->where('id !=', $excludeConcernId);
        }
        foreach ($concernQuery->get()->getResultArray() as $concernRow) {
            $dates[] = $concernRow['appointment_date'];
        }

        $unique = [];
        foreach ($dates as $date) {
            $day = substr((string) $date, 0, 10);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
                $unique[$day] = $day;
            }
        }

        $booked = [];
        foreach ($unique as $day) {
            if ($this->isDateFullyBooked($day, $excludeScheduleId, $excludeConcernId)) {
                $booked[] = $day;
            }
        }

        return $booked;
    }

    private function duplicateAppointmentMessage(array $data, int $excludeId = 0): ?string
    {
        $existing = $this->openAppointmentsForPerson($data, $excludeId);
        $date     = $data['appointment_date'] ?? null;
        $newTime  = $this->normalizeTime($data['appointment_time'] ?? null);
        $subject  = $this->normalizeSubject((string) ($data['subject'] ?? ''));

        foreach ($existing as $row) {
            if ($subject !== '' && $this->normalizeSubject((string) ($row['subject'] ?? '')) === $subject) {
                return 'This person already has an open appointment for the same concern. A different concern can still be booked on another available date and time.';
            }

            if (! $date || empty($row['appointment_date']) || $row['appointment_date'] !== $date) {
                continue;
            }

            $existingTime = $this->normalizeTime($row['appointment_time'] ?? null);
            if ($existingTime !== null && $newTime !== null && $existingTime === $newTime) {
                return 'This person already has an appointment at that date and time. Choose a different slot for the new concern.';
            }
            if ($existingTime === null && $newTime === null) {
                return 'This person already has an appointment on that date. Choose a different date or time for the new concern.';
            }
        }

        return null;
    }

    private function openAppointmentsForPerson(array $data, int $excludeId = 0): array
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('concern_submissions')
            ->whereIn('status', ['pending', 'approved']);
        if ($excludeId > 0) {
            $builder->where('id !=', $excludeId);
        }

        $matches = [];
        foreach ($builder->get()->getResultArray() as $row) {
            if ($this->isSamePerson($data, $row)) {
                $matches[] = $row;
            }
        }

        return $matches;
    }

    private function isSamePerson(array $left, array $right): bool
    {
        $leftId  = (int) ($left['user_id'] ?? 0);
        $rightId = (int) ($right['user_id'] ?? 0);
        if ($leftId > 0 && $rightId > 0 && $leftId === $rightId) {
            return true;
        }

        $leftEmail  = strtolower(trim((string) ($left['email'] ?? '')));
        $rightEmail = strtolower(trim((string) ($right['email'] ?? '')));
        if ($leftEmail !== '' && $leftEmail === $rightEmail) {
            return true;
        }

        $leftName    = preg_replace('/\s+/', ' ', strtolower(trim((string) ($left['full_name'] ?? ''))));
        $rightName   = preg_replace('/\s+/', ' ', strtolower(trim((string) ($right['full_name'] ?? ''))));
        $leftContact = preg_replace('/\D+/', '', (string) ($left['contact_number'] ?? ''));
        $rightContact = preg_replace('/\D+/', '', (string) ($right['contact_number'] ?? ''));

        return $leftName !== '' && $leftName === $rightName
            && $leftContact !== '' && $leftContact === $rightContact;
    }

    private function hasAppointmentConflict(string $date, ?string $time, int $excludeScheduleId = 0, int $excludeConcernId = 0): bool
    {
        if (! $time) {
            return false;
        }

        $db = \Config\Database::connect();
        $requestedStart = strtotime($date . ' ' . $time);
        $requestedEnd   = $requestedStart + 3600;

        $scheduleRows = $db->table('schedules')
            ->select('start_time, end_time')
            ->where('event_date', $date);
        if ($excludeScheduleId > 0) {
            $scheduleRows->where('id !=', $excludeScheduleId);
        }
        foreach ($scheduleRows->get()->getResultArray() as $schedule) {
            if (empty($schedule['start_time'])) {
                return true;
            }

            $existingStart = strtotime($date . ' ' . $schedule['start_time']);
            $existingEnd = ! empty($schedule['end_time'])
                ? strtotime($date . ' ' . $schedule['end_time'])
                : $existingStart + 3600;
            if ($requestedStart < $existingEnd && $requestedEnd > $existingStart) {
                return true;
            }
        }

        $blotterRows = $db->table('blotter_reports')
            ->select('appointment_date, appointment_time, hearing_date, hearing_time')
            ->groupStart()
            ->where('appointment_date', $date)
            ->orWhere('hearing_date', $date)
            ->groupEnd()
            ->get()->getResultArray();
        foreach ($blotterRows as $blotter) {
            $existingTime = null;
            if (($blotter['appointment_date'] ?? null) === $date) {
                $existingTime = $blotter['appointment_time'] ?? null;
            } elseif (($blotter['hearing_date'] ?? null) === $date) {
                $existingTime = $blotter['hearing_time'] ?? null;
            }
            if (! $existingTime) {
                return true;
            }

            $existingStart = strtotime($date . ' ' . $existingTime);
            $existingEnd = $existingStart + 3600;
            if ($requestedStart < $existingEnd && $requestedEnd > $existingStart) {
                return true;
            }
        }

        $concernQuery = $db->table('concern_submissions')
            ->select('appointment_time')
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending', 'approved']);
        if ($excludeConcernId > 0) {
            $concernQuery->where('id !=', $excludeConcernId);
        }
        foreach ($concernQuery->get()->getResultArray() as $concernRow) {
            if (empty($concernRow['appointment_time'])) {
                continue;
            }
            $existingStart = strtotime($date . ' ' . $concernRow['appointment_time']);
            $existingEnd   = $existingStart + 3600;
            if ($requestedStart < $existingEnd && $requestedEnd > $existingStart) {
                return true;
            }
        }

        return false;
    }

    private function isDateFullyBooked(string $date, int $excludeScheduleId = 0, int $excludeConcernId = 0): bool
    {
        $db = \Config\Database::connect();
        $intervals = [];
        $scheduleQuery = $db->table('schedules')->where('event_date', $date);
        if ($excludeScheduleId > 0) {
            $scheduleQuery->where('id !=', $excludeScheduleId);
        }

        foreach ($scheduleQuery->select('start_time, end_time')->get()->getResultArray() as $schedule) {
            if (empty($schedule['start_time'])) {
                return true;
            }
            $start = strtotime($date . ' ' . $schedule['start_time']);
            $end = ! empty($schedule['end_time'])
                ? strtotime($date . ' ' . $schedule['end_time'])
                : $start + 3600;
            $intervals[] = [date('H:i', $start), date('H:i', $end)];
        }

        foreach (
            $db->table('blotter_reports')
                ->select('appointment_date, appointment_time, hearing_date, hearing_time')
                ->groupStart()
                ->where('appointment_date', $date)
                ->orWhere('hearing_date', $date)
                ->groupEnd()
                ->get()->getResultArray() as $blotter
        ) {
            $time = ($blotter['appointment_date'] ?? null) === $date
                ? $blotter['appointment_time']
                : $blotter['hearing_time'];
            if (empty($time)) {
                return true;
            }
            $start = strtotime($date . ' ' . $time);
            $intervals[] = [date('H:i', $start), date('H:i', $start + 3600)];
        }

        $concernQuery = $db->table('concern_submissions')
            ->select('appointment_time')
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending', 'approved']);
        if ($excludeConcernId > 0) {
            $concernQuery->where('id !=', $excludeConcernId);
        }
        foreach ($concernQuery->get()->getResultArray() as $concernRow) {
            if (empty($concernRow['appointment_time'])) {
                continue;
            }
            $start = strtotime($date . ' ' . $concernRow['appointment_time']);
            $intervals[] = [date('H:i', $start), date('H:i', $start + 3600)];
        }

        usort($intervals, static fn(array $a, array $b): int => strcmp($a[0], $b[0]));
        $coveredUntil = '08:00';
        foreach ($intervals as [$start, $end]) {
            if ($end <= $coveredUntil) {
                continue;
            }
            if ($start > $coveredUntil) {
                return false;
            }
            $coveredUntil = max($coveredUntil, $end);
            if ($coveredUntil >= '17:00') {
                return true;
            }
        }

        return false;
    }

    private function findResidentForConcern(array $concern): ?array
    {
        $db = \Config\Database::connect();
        $userId = (int) ($concern['user_id'] ?? 0);

        if ($userId > 0) {
            $resident = $db->table('users')
                ->select('id')
                ->where('id', $userId)
                ->where('role', 'resident')
                ->get()->getRowArray();
            if ($resident) {
                return $resident;
            }
        }

        $email = trim((string) ($concern['email'] ?? ''));
        if ($email === '') {
            return null;
        }

        return $db->table('users')
            ->select('id')
            ->where('LOWER(email)', strtolower($email), false)
            ->where('role', 'resident')
            ->get()->getRowArray() ?: null;
    }

    private function findScheduleIdForConcern(array $concern, int $concernId): int
    {
        $db = \Config\Database::connect();
        $linked = $db->table('schedules')
            ->select('id')
            ->where('concern_id', $concernId)
            ->orderBy('id', 'ASC')
            ->get()->getRowArray();
        if ($linked) {
            return (int) $linked['id'];
        }

        $legacy = $db->table('schedules')
            ->select('id')
            ->where('title', 'Concern Appointment: ' . ($concern['subject'] ?: 'Barangay Concern'))
            ->where('description', 'Concern for ' . ($concern['full_name'] ?: 'Resident') . '.')
            ->where('event_date', $concern['appointment_date'])
            ->where('start_time', $concern['appointment_time'])
            ->orderBy('id', 'ASC')
            ->get()->getRowArray();

        return $legacy ? (int) $legacy['id'] : 0;
    }

    private function notifyResidentAppointment(?int $residentId, string $date, ?string $time, string $title): void
    {
        if (! $residentId) {
            return;
        }

        $dateLabel = date('F d, Y', strtotime($date));
        $timeLabel = $time ? ' at ' . date('g:i A', strtotime($time)) : '';
        \App\Models\NotificationModel::push(
            $residentId,
            'event_reminder',
            $title,
            'Your barangay appointment is scheduled for ' . $dateLabel . $timeLabel . '.',
            '/resident/dashboard'
        );
    }

    // ── Admin: Approve / resolve a concern ────────────────────────────────────

    public function approve(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'])) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $concern = $this->model->find($id);
        if (! $concern) {
            return redirect()->to('/' . $role . '/notifications')->with('error', 'Concern not found.');
        }

        $response = trim($this->request->getPost('response') ?? '');
        if (empty($response)) {
            return redirect()->back()
                ->with('concern_form_error', 'A response message is required before approving.')
                ->withInput();
        }

        $scheduleId = $this->findScheduleIdForConcern($concern, $id);
        if (! empty($concern['appointment_date']) && (
            $this->isDateFullyBooked($concern['appointment_date'], $scheduleId, $id)
            || ($concern['appointment_time'] && $this->hasAppointmentConflict(
                $concern['appointment_date'],
                $concern['appointment_time'],
                $scheduleId,
                $id
            ))
        )) {
            return redirect()->back()
                ->with('concern_form_error', 'That appointment date and time is already booked. Please reschedule before approving.')
                ->withInput();
        }

        $db = \Config\Database::connect();
        if (! empty($concern['appointment_date'])) {
            $scheduleData = [
                'title'       => 'Concern Appointment: ' . ($concern['subject'] ?: 'Barangay Concern'),
                'description' => 'Concern for ' . ($concern['full_name'] ?: 'Resident') . '.',
                'event_date'  => $concern['appointment_date'],
                'start_time'  => $concern['appointment_time'] ?: null,
                'end_time'    => null,
                'event_type'  => 'appointment',
                'color'       => '#1d2448',
                'location'    => 'Barangay Hall',
                'created_by'  => (int) session()->get('user_id'),
                'concern_id'  => $id,
                'visibility'  => 'private',
                'shared_with' => null,
            ];

            if ($scheduleId > 0) {
                $db->table('schedules')->where('id', $scheduleId)->update($scheduleData);
            } else {
                $db->table('schedules')->insert($scheduleData);
                $scheduleId = (int) $db->insertID();
            }
        }

        $updates = ['status' => 'approved', 'notes' => $response];
        if ($scheduleId > 0) {
            $updates['schedule_id'] = $scheduleId;
        }
        $this->model->update($id, $updates);

        try {
            (new \App\Libraries\EmailService())->sendConcernResponse(
                $concern['email'],
                $concern['full_name'],
                $concern['subject'],
                $concern['message'],
                $response,
                'approved',
                $concern['appointment_date'] ?? null,
                $concern['appointment_time'] ?? null
            );
        } catch (\Throwable $e) {
            log_message('error', 'Concern approval email failed: ' . $e->getMessage());
        }

        return redirect()->to('/' . $role . '/concerns')
            ->with('success', 'Concern from <strong>' . esc($concern['full_name']) . '</strong> approved. Response sent to ' . esc($concern['email']) . '.');
    }

    // ── Admin: resolve an appointment directly from the detail header ────────

    public function resolve(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'], true)) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $concern = $this->model->find($id);
        if (! $concern) {
            return redirect()->to('/' . $role . '/concerns')->with('error', 'Concern not found.');
        }

        $response = trim($this->request->getPost('response') ?? '');
        if ($response === '') {
            $response = 'Your appointment has been completed and this concern has been resolved.';
        }

        $this->model->update($id, [
            'status' => 'resolved',
            'notes'  => $response,
        ]);

        try {
            (new \App\Libraries\EmailService())->sendConcernResponse(
                $concern['email'],
                $concern['full_name'],
                $concern['subject'],
                $concern['message'],
                $response,
                'resolved',
                $concern['appointment_date'] ?? null,
                $concern['appointment_time'] ?? null
            );
        } catch (\Throwable $e) {
            log_message('error', 'Concern resolution email failed: ' . $e->getMessage());
        }

        return redirect()->to('/' . $role . '/concern/' . $id)
            ->with('success', 'Appointment marked as resolved. The submitter has been notified.');
    }

    // ── Admin: Dismiss a concern ──────────────────────────────────────────────

    public function dismiss(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['secretary', 'captain', 'admin'])) {
            return redirect()->to('/')->with('error', 'Unauthorized.');
        }

        $concern = $this->model->find($id);
        if (! $concern) {
            return redirect()->to('/' . $role . '/notifications')->with('error', 'Concern not found.');
        }

        $response = trim($this->request->getPost('response') ?? '');
        if (empty($response)) {
            return redirect()->back()
                ->with('concern_form_error', 'A response message is required before dismissing.')
                ->withInput();
        }

        $this->model->update($id, ['status' => 'dismissed', 'notes' => $response]);

        try {
            (new \App\Libraries\EmailService())->sendConcernResponse(
                $concern['email'],
                $concern['full_name'],
                $concern['subject'],
                $concern['message'],
                $response,
                'dismissed',
                $concern['appointment_date'] ?? null,
                $concern['appointment_time'] ?? null
            );
        } catch (\Throwable $e) {
            log_message('error', 'Concern dismissal email failed: ' . $e->getMessage());
        }

        return redirect()->to('/' . $role . '/concerns')
            ->with('success', 'Concern from <strong>' . esc($concern['full_name']) . '</strong> dismissed. Response sent to ' . esc($concern['email']) . '.');
    }
}
