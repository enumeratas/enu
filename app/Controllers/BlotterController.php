<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BlotterModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Libraries\EmailService;

class BlotterController extends BaseController
{
    protected BlotterModel $model;

    public function __construct()
    {
        $this->model = new BlotterModel();
    }

    // ── Public (non-resident): submit blotter report ─────────────────────────

    public function storePublic()
    {
        return redirect()->to('/')->with('blotter_error', 'Blotter reports can only be created by the barangay secretary.');

        // Complainant name — split fields
        $lastName   = trim($this->request->getPost('complainant_last_name') ?? '');
        $firstName  = trim($this->request->getPost('complainant_first_name') ?? '');
        $middleName = trim($this->request->getPost('complainant_middle_name') ?? '');

        // Compose "Last Name, First Name Middle Name" format
        $nameParts = $firstName . ($middleName ? ' ' . $middleName : '');
        $complainantName = $lastName . ', ' . $nameParts;

        $complainantEmail   = trim($this->request->getPost('complainant_email') ?? '');
        $rawContactNumber = $this->request->getPost('contact_number');
        $complainantContact = $this->cleanContactNumber($rawContactNumber);
        if ($rawContactNumber !== null && $rawContactNumber !== '' && $complainantContact === null) {
            return redirect()->back()->with('blotter_error', 'Contact number must contain exactly 11 digits.')->withInput();
        }
        $complainantAddress = trim($this->request->getPost('complainant_address') ?? '');
        $incidentType       = $this->request->getPost('incident_type');
        $incidentDate       = $this->request->getPost('incident_date');
        $incidentTime       = $this->request->getPost('incident_time');
        $location           = $this->request->getPost('location');
        $personsInvolved    = $this->request->getPost('persons_involved');
        $narrative          = trim($this->request->getPost('narrative') ?? '');
        $appointmentDate    = $this->request->getPost('appointment_date') ?: null;
        $appointmentTime    = $this->request->getPost('appointment_time') ?: null;

        if (empty($lastName) || empty($firstName) || empty($complainantEmail) || empty($incidentType) || empty($narrative)) {
            return redirect()->back()->with('error', 'Please fill in all required fields.')->withInput();
        }

        // Validate appointment date is not already booked
        if ($appointmentDate) {
            if ($this->isDateBooked($appointmentDate)) {
                return redirect()->back()->with('error', 'The selected appointment date (' . date('F d, Y', strtotime($appointmentDate)) . ') is already fully booked. Please choose another date.')->withInput();
            }

            // Validate time slot is not occupied
            if ($appointmentTime) {
                $conflict = $this->getTimeConflict($appointmentDate, $appointmentTime);
                if ($conflict) {
                    return redirect()->back()->with('error', 'The selected time (' . date('h:i A', strtotime($appointmentTime)) . ') conflicts with an existing event: "' . $conflict . '". Please choose a different time.')->withInput();
                }
            }
        }

        $blotterId = $this->model->insert([
            'complainant_user_id' => null,
            'complainant_name'    => $complainantName,
            'complainant_email'   => $complainantEmail,
            'complainant_contact' => $complainantContact ?: null,
            'appointment_date'    => $appointmentDate,
            'appointment_time'    => $appointmentTime,
            'incident_type'       => $incidentType,
            'incident_date'       => $incidentDate ?: null,
            'incident_time'       => $incidentTime ?: null,
            'location'            => $location ?: null,
            'persons_involved'    => $personsInvolved ?: null,
            'narrative'           => $narrative,
            'respondent_address'  => $complainantAddress ?: null,
            'status'              => 'pending',
        ], true); // true = return insert ID

        // If appointment was requested, create a calendar entry for the captain
        if ($appointmentDate && $blotterId) {
            $userModel   = new \App\Models\UserModel();
            $captainUser = $userModel->getActiveByRole('captain');

            if ($captainUser) {
                $scheduleModel = new \App\Models\ScheduleModel();
                $caseNo = str_pad($blotterId, 2, '0', STR_PAD_LEFT);
                $scheduleModel->insert([
                    'title'       => 'Blotter Appointment #' . $caseNo . ' — ' . $incidentType,
                    'description' => 'Complainant: ' . $complainantName . ($complainantContact ? ' · ' . $complainantContact : '') . "\n" . 'Re: ' . $incidentType,
                    'event_date'  => $appointmentDate,
                    'start_time'  => $appointmentTime ?: null,
                    'end_time'    => null,
                    'event_type'  => 'appointment',
                    'color'       => '#c0392b',
                    'location'    => 'Barangay Hall',
                    'blotter_id'  => $blotterId,
                    'created_by'  => (int) $captainUser['id'],
                    'visibility'  => 'private',
                    'shared_with' => null,
                ]);
            }
        }

        $msg = 'Your blotter report has been submitted successfully. The barangay will contact you at ' . $complainantEmail . '.';
        if ($appointmentDate) {
            $msg .= ' Your appointment is set for ' . date('F d, Y', strtotime($appointmentDate));
            if ($appointmentTime) {
                $msg .= ' at ' . date('h:i A', strtotime($appointmentTime));
            }
            $msg .= '.';
        }

        return redirect()->to('/')->with('blotter_success', $msg);
    }

    // ── Public: return booked dates as JSON for the date picker ──────────────

    public function busyDates()
    {
        $db = \Config\Database::connect();

        // Collect all appointment dates already filed
        $blotterDates = $db->table('blotter_reports')
            ->select('appointment_date AS date_val, COUNT(*) AS cnt')
            ->where('appointment_date IS NOT NULL')
            ->groupBy('appointment_date')
            ->get()->getResultArray();

        // Collect hearing dates
        $hearingDates = $db->table('blotter_reports')
            ->select('hearing_date AS date_val, COUNT(*) AS cnt')
            ->where('hearing_date IS NOT NULL')
            ->groupBy('hearing_date')
            ->get()->getResultArray();

        // Collect schedule events
        $scheduleDates = $db->table('schedules')
            ->select('event_date AS date_val, COUNT(*) AS cnt')
            ->groupBy('event_date')
            ->get()->getResultArray();

        $wholeDaySchedules = $db->table('schedules')
            ->select('event_date')
            ->where('event_date IS NOT NULL', null, false)
            ->groupStart()
            ->where('start_time IS NULL', null, false)
            ->orWhere('start_time', '')
            ->groupEnd()
            ->get()->getResultArray();

        // Aggregate occupied dates; only dates covering the full 8 AM–5 PM
        // office window are returned as unavailable.
        $counts = [];
        foreach (array_merge($blotterDates, $hearingDates, $scheduleDates) as $row) {
            $d = $row['date_val'];
            $counts[$d] = ($counts[$d] ?? 0) + (int) $row['cnt'];
        }

        foreach ($wholeDaySchedules as $row) {
            $counts[$row['event_date']] = max($counts[$row['event_date']] ?? 0, 4);
        }

        // Return counts so date pickers can disable only fully booked dates.
        $result = [];
        foreach ($counts as $date => $cnt) {
            $result[] = ['date' => $date, 'count' => $cnt, 'busy' => $this->isDateFullyBooked($date)];
        }

        return $this->response->setJSON(['dates' => $result]);
    }

    // ── Public: return occupied time slots for a given date ───────────────────

    public function busySlots()
    {
        $date = trim($this->request->getGet('date') ?? '');

        // Basic date validation
        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->response->setJSON(['slots' => []]);
        }

        $db    = \Config\Database::connect();
        $slots = [];

        // Blotter appointments for this date
        $blotters = $db->table('blotter_reports')
            ->select('appointment_time AS start_time, NULL AS end_time, incident_type AS label')
            ->where('appointment_date', $date)
            ->where('appointment_time IS NOT NULL')
            ->get()->getResultArray();

        foreach ($blotters as $b) {
            $slots[] = [
                'start' => $b['start_time'],
                'end'   => null,
                'label' => 'Blotter Appointment: ' . $b['label'],
            ];
        }

        // Hearing dates
        $hearings = $db->table('blotter_reports')
            ->select('hearing_time AS start_time, NULL AS end_time, incident_type AS label')
            ->where('hearing_date', $date)
            ->where('hearing_time IS NOT NULL')
            ->get()->getResultArray();

        foreach ($hearings as $h) {
            $slots[] = [
                'start' => $h['start_time'],
                'end'   => null,
                'label' => 'Blotter Hearing: ' . $h['label'],
            ];
        }

        // Calendar events for this date (with start + end times)
        $events = $db->table('schedules')
            ->select('start_time, end_time, title AS label')
            ->where('event_date', $date)
            ->where('start_time IS NOT NULL')
            ->get()->getResultArray();

        foreach ($events as $e) {
            $slots[] = [
                'start' => $e['start_time'],
                'end'   => $e['end_time'],
                'label' => $e['label'],
            ];
        }

        return $this->response->setJSON(['slots' => $slots]);
    }

    private function isDateFullyBooked(string $date): bool
    {
        $db = \Config\Database::connect();
        $intervals = [];

        foreach (
            $db->table('schedules')
                ->select('start_time, end_time')
                ->where('event_date', $date)
                ->get()->getResultArray() as $schedule
        ) {
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

        if (empty($intervals)) {
            return false;
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

    // ── Helper: is a date already booked (3+ appointments)? ──────────────────
    private function isDateBooked(string $date): bool
    {
        $db = \Config\Database::connect();
        $total = 0;

        $total += $db->table('blotter_reports')->where('appointment_date', $date)->countAllResults();
        $total += $db->table('blotter_reports')->where('hearing_date', $date)->countAllResults();
        $total += $db->table('schedules')->where('event_date', $date)->countAllResults();

        return $total >= 3;
    }

    // ── Helper: does a time overlap any existing slot? Returns conflict label or null ──
    private function getTimeConflict(string $date, string $time): ?string
    {
        $db      = \Config\Database::connect();
        $reqMin  = $this->toMinutes($time);
        $reqEnd  = $reqMin + 60; // 1-hour slot

        // Check calendar events with start_time + end_time
        $events = $db->table('schedules')
            ->select('title, start_time, end_time')
            ->where('event_date', $date)
            ->where('start_time IS NOT NULL')
            ->get()->getResultArray();

        foreach ($events as $ev) {
            $evStart = $this->toMinutes($ev['start_time']);
            $evEnd   = $ev['end_time'] ? $this->toMinutes($ev['end_time']) : $evStart + 60;
            if ($reqMin < $evEnd && $reqEnd > $evStart) {
                return $ev['title'];
            }
        }

        // Check existing blotter appointments (treat as 1-hour slots)
        $appts = $db->table('blotter_reports')
            ->select('incident_type, appointment_time AS slot_time')
            ->where('appointment_date', $date)
            ->where('appointment_time IS NOT NULL')
            ->get()->getResultArray();

        foreach ($appts as $a) {
            $s = $this->toMinutes($a['slot_time']);
            if ($reqMin < $s + 60 && $reqEnd > $s) {
                return 'Blotter Appointment: ' . $a['incident_type'];
            }
        }

        // Check hearing slots
        $hearings = $db->table('blotter_reports')
            ->select('incident_type, hearing_time AS slot_time')
            ->where('hearing_date', $date)
            ->where('hearing_time IS NOT NULL')
            ->get()->getResultArray();

        foreach ($hearings as $h) {
            $s = $this->toMinutes($h['slot_time']);
            if ($reqMin < $s + 60 && $reqEnd > $s) {
                return 'Blotter Hearing: ' . $h['incident_type'];
            }
        }

        return null;
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    }

    // ── Secretary: create a blotter report for linked residents ──────────────

    public function createSecretary()
    {
        if (! can_role('secretary')) {
            return redirect()->to('/' . session()->get('role') . '/blotter')->with('error', 'Only the secretary can create blotter reports.');
        }

        $db = \Config\Database::connect();

        $residents = $db->table('users u')
            ->select("u.id, u.first_name, u.last_name, u.middle_name, u.email, u.household_no, h.zone")
            ->join('households h', 'h.household_no = u.household_no', 'left')
            ->where('u.role', 'resident')
            ->where('u.status', 'active')
            ->orderBy('u.last_name', 'ASC')
            ->get()
            ->getResultArray();

        // id → {name, email, address} map for client-side auto-fill.
        // Address format: "Zone X, Bacolod City, Bacolod" for linked residents.
        $residentData = [];
        foreach ($residents as $r) {
            $zone    = trim($r['zone'] ?? '');
            $address = $zone !== ''
                ? $zone . ', Bacolod City, Bacolod'
                : 'Bacolod City, Bacolod';

            $residentData[(int) $r['id']] = [
                'name'    => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                'email'   => $r['email'] ?? '',
                'address' => $address,
            ];
        }

        return view('dashboard/secretary/blotter_create', [
            'residents'    => $residents,
            'residentData' => $residentData,
        ]);
    }

    public function storeSecretary()
    {
        if (! can_role('secretary')) {
            return redirect()->back()->with('error', 'Only the secretary can create blotter reports.');
        }

        $complainantUserValue = trim($this->request->getPost('complainant_user_id') ?? '');
        $complainantId = ctype_digit($complainantUserValue) ? (int) $complainantUserValue : 0;
        $externalComplainantName = trim($this->request->getPost('complainant_name') ?? '');
        $externalComplainantEmail = trim($this->request->getPost('complainant_email') ?? '');
        $externalComplainantContact = trim($this->request->getPost('complainant_contact') ?? '');
        $externalComplainantAddress = trim($this->request->getPost('complainant_address') ?? '');
        $respondentUserValue = trim($this->request->getPost('respondent_user_id') ?? '');
        $respondentId  = ctype_digit($respondentUserValue) ? (int) $respondentUserValue : 0;
        $externalRespondentName = trim($this->request->getPost('respondent_name') ?? '');
        $externalRespondentEmail = trim($this->request->getPost('respondent_email') ?? '');
        $externalRespondentAddress = trim($this->request->getPost('respondent_address') ?? '');
        $incidentType  = trim($this->request->getPost('incident_type') ?? '');
        $incidentDate  = $this->request->getPost('incident_date') ?: null;
        $incidentTime  = $this->request->getPost('incident_time') ?: null;
        $location      = trim($this->request->getPost('location') ?? '');
        $persons       = trim($this->request->getPost('persons_involved') ?? '');
        $complainantNarrative = trim($this->request->getPost('complainant_narrative') ?? '');
        $respondentNarrative  = trim($this->request->getPost('respondent_narrative') ?? '');

        $userModel = new UserModel();
        $complainant = $complainantId ? $userModel->where('id', $complainantId)->where('role', 'resident')->where('status', 'active')->first() : null;
        $respondent  = $respondentId ? $userModel->where('id', $respondentId)->where('role', 'resident')->where('status', 'active')->first() : null;
        $isExternalComplainant = $complainantUserValue === 'external';
        $isExternalRespondent = $respondentUserValue === 'external';

        if ($incidentType === '' || $complainantNarrative === '') {
            return redirect()->back()->with('error', 'Complainant narrative and incident type are required.')->withInput();
        }

        // Block same resident as both complainant and respondent
        if ($complainant && $respondent && $complainantId === $respondentId) {
            return redirect()->back()->with('error', 'The complainant and respondent cannot be the same person.')->withInput();
        }

        // Block same email between complainant and respondent
        $cEmail = $complainant['email'] ?? $externalComplainantEmail;
        $rEmail = $respondent['email']  ?? $externalRespondentEmail;
        if ($cEmail !== '' && $rEmail !== '' && strtolower($cEmail) === strtolower($rEmail)) {
            return redirect()->back()->with('error', 'The complainant and respondent cannot have the same email address.')->withInput();
        }

        if ($incidentDate && $incidentDate > date('Y-m-d')) {
            return redirect()->back()->with('error', 'The incident date cannot be a future date.')->withInput();
        }

        if ($isExternalComplainant && $externalComplainantEmail !== '' && $userModel->residentOwnsEmail($externalComplainantEmail)) {
            return redirect()->back()->with('error', 'A non-resident complainant cannot use an email that belongs to a resident account.')->withInput();
        }

        if ($isExternalRespondent && $externalRespondentEmail !== '' && $userModel->residentOwnsEmail($externalRespondentEmail)) {
            return redirect()->back()->with('error', 'A non-resident respondent cannot use an email that belongs to a resident account.')->withInput();
        }

        if ((! $isExternalComplainant && ! $complainant)) {
            return redirect()->back()->with('error', 'Select a resident complainant or enter a name for a non-resident.')->withInput();
        }

        if ($isExternalComplainant && $externalComplainantName === '') {
            return redirect()->back()->with('error', 'Enter the name of the non-resident complainant.')->withInput();
        }

        if (! $isExternalRespondent && ! $respondent) {
            return redirect()->back()->with('error', 'Select a resident respondent or enter a name for a non-resident.')->withInput();
        }

        if ($isExternalRespondent && $externalRespondentName === '') {
            return redirect()->back()->with('error', 'Enter the name of the non-resident respondent.')->withInput();
        }

        $complainantName = $complainant
            ? trim(($complainant['first_name'] ?? '') . ' ' . ($complainant['last_name'] ?? ''))
            : $externalComplainantName;
        $respondentName  = $respondent
            ? trim(($respondent['first_name'] ?? '') . ' ' . ($respondent['last_name'] ?? ''))
            : $externalRespondentName;
        $combinedNarrative = "Complainant's Narrative:\n" . $complainantNarrative . "\n\nRespondent's Narrative:\n" . $respondentNarrative;

        $this->model->insert([
            'complainant_user_id'   => $complainant ? $complainantId : null,
            'complainant_name'      => $complainantName,
            'complainant_email'     => $complainant['email'] ?? ($externalComplainantEmail ?: null),
            'complainant_contact'   => $complainant ? null : ($externalComplainantContact ?: null),
            'complainant_address'   => $externalComplainantAddress ?: null,
            'respondent_address'    => $externalRespondentAddress ?: null,
            'complainant_narrative' => $complainantNarrative,
            'respondent_user_id'    => $respondent ? $respondentId : null,
            'respondent_name'       => $respondentName,
            'respondent_email'      => $respondent['email'] ?? ($externalRespondentEmail ?: null),
            'incident_type'         => $incidentType,
            'incident_date'         => $incidentDate,
            'incident_time'         => $incidentTime,
            'location'              => $location ?: null,
            'persons_involved'      => $persons ?: $respondentName,
            'narrative'             => $combinedNarrative,
            'respondent_narrative'  => $respondentNarrative,
            'status'                => 'pending',
            'processed_by'          => session()->get('user_id'),
        ]);

        $blotterId = $this->model->getInsertID();

        // ── Evidence photos (optional) ──────────────────────────────────────
        $uploadedFiles = $this->request->getFileMultiple('evidence_photos');
        $evidencePaths = [];

        if (! empty($uploadedFiles)) {
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxBytes    = 5 * 1024 * 1024; // 5 MB per file
            $uploadDir   = WRITEPATH . 'uploads/blotter_evidence/';

            @mkdir($uploadDir, 0755, true);

            foreach ($uploadedFiles as $file) {
                // Skip empty file inputs
                if (! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                if (! in_array($file->getMimeType(), $allowedMime, true)) {
                    continue; // silently skip non-images
                }

                if ($file->getSize() > $maxBytes) {
                    continue; // silently skip oversized files
                }

                $ext      = strtolower($file->getExtension());
                $fileName = 'blotter_' . $blotterId . '_' . uniqid() . '.' . $ext;

                if ($file->move($uploadDir, $fileName)) {
                    $relativePath = 'uploads/blotter_evidence/' . $fileName;

                    // Mirror to GCS when available (production)
                    try {
                        (new \App\Libraries\HouseholdUploadStorage())
                            ->store($uploadDir . $fileName, $relativePath);
                    } catch (\Throwable $e) {
                        log_message('warning', 'Blotter evidence GCS upload failed: ' . $e->getMessage());
                        // Keep the local file — still accessible in development
                    }

                    $evidencePaths[] = $relativePath;
                }
            }

            if (! empty($evidencePaths)) {
                $this->model->update($blotterId, [
                    'evidence_photos' => json_encode($evidencePaths),
                ]);
            }
        }

        $linkedPeople = [];
        if ($complainant) {
            $linkedPeople[] = $complainant;
        }
        if ($respondent) {
            $linkedPeople[] = $respondent;
        }
        foreach ($linkedPeople as $person) {
            NotificationModel::push(
                (int) $person['id'],
                'new_blotter',
                'Blotter Report Filed',
                'A blotter report involving you was filed by the barangay secretary: ' . $incidentType . '.',
                '/resident/notifications'
            );
        }

        $savedPhotos = count($evidencePaths);
        $role = (string) (session()->get('role') ?: 'secretary');
        $message = 'Blotter report created.';
        if ($savedPhotos > 0) {
            $message .= ' ' . $savedPhotos . ' evidence photo' . ($savedPhotos === 1 ? ' is' : 's are') . ' saved and can be viewed on this report.';
        }

        return redirect()->to('/' . $role . '/blotter/' . $blotterId)->with('success', $message);
    }

    // ── Resident / SK: submit blotter report ─────────────────────────────────

    public function store()
    {
        if (! can_role('secretary')) {
            return redirect()->back()->with('blotter_error', 'Blotter reports can only be created by the barangay secretary.');
        }

        $userId    = (int) session()->get('user_id');
        $userModel = new UserModel();
        $user      = $userModel->find($userId);
        $role      = session()->get('role'); // 'resident' or 'sk'

        $incidentType    = $this->request->getPost('incident_type');
        $incidentDate    = $this->request->getPost('incident_date');
        $narrative       = trim($this->request->getPost('narrative') ?? '');
        $respondentLast  = trim($this->request->getPost('respondent_last_name')      ?? '');
        $respondentFirst = trim($this->request->getPost('respondent_first_name')     ?? '');
        $respondentMI    = trim($this->request->getPost('respondent_middle_initial') ?? '');
        // Compose "Last Name, First Name M.I." format
        $respondentName  = '';
        if ($respondentLast !== '' || $respondentFirst !== '') {
            $respondentName = $respondentLast;
            if ($respondentFirst !== '') {
                $respondentName .= ($respondentName !== '' ? ', ' : '') . $respondentFirst;
            }
            if ($respondentMI !== '') {
                $respondentName .= ' ' . strtoupper(rtrim($respondentMI, '.')) . '.';
            }
        }
        $contactNumber   = trim($this->request->getPost('contact_number') ?? '');
        $appointmentDate = $this->request->getPost('appointment_date') ?: null;
        $appointmentTime = $this->request->getPost('appointment_time') ?: null;

        // Build complainant name from form fields if provided, else from session
        $cLast   = trim($this->request->getPost('complainant_last_name')   ?? ($user['last_name']  ?? ''));
        $cFirst  = trim($this->request->getPost('complainant_first_name')  ?? ($user['first_name'] ?? ''));
        $cEmail  = trim($this->request->getPost('complainant_email')       ?? ($user['email']      ?? ''));
        $cName   = trim("$cFirst $cLast") ?: trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

        if (empty($incidentType) || empty($narrative)) {
            return redirect()->back()->with('blotter_error', 'Please fill in the required fields.')->withInput();
        }

        $insert = [
            'complainant_user_id'  => $userId,
            'complainant_name'     => $cName,
            'complainant_email'    => $cEmail ?: ($user['email'] ?? ''),
            'complainant_contact'  => $contactNumber ?: null,
            'incident_type'        => $incidentType,
            'incident_date'        => $incidentDate ?: null,
            'narrative'            => $narrative,
            'status'               => 'pending',
        ];

        if ($respondentName !== '') {
            $insert['respondent_name'] = $respondentName;
        }
        if ($appointmentDate) {
            $insert['appointment_date'] = $appointmentDate;
            $insert['appointment_time'] = $appointmentTime ?: null;
        }

        $this->model->insert($insert);

        // Notify secretary/captain about the new blotter
        $db = \Config\Database::connect();
        $officials = $db->table('users')
            ->whereIn('role', ['secretary', 'captain'])
            ->where('status', 'active')
            ->get()->getResultArray();
        foreach ($officials as $off) {
            \App\Models\NotificationModel::push(
                (int) $off['id'],
                'new_blotter',
                'New Blotter Report',
                $cName . ' filed a blotter report: ' . $incidentType . '.',
                '/' . $off['role'] . '/blotter'
            );
        }

        $redirectBase = $role === 'sk' ? '/sk/blotter' : '/resident/dashboard';
        return redirect()->to($redirectBase)->with('success', 'Blotter report submitted successfully. The barangay will contact you shortly.');
    }

    // ── Admin: list all blotter reports ──────────────────────────────────────

    public function adminIndex(string $role)
    {
        $statusFilter = $_GET['status'] ?? '';
        $search       = $_GET['search'] ?? '';

        $db      = \Config\Database::connect();
        $builder = $db->table('blotter_reports b')
            ->select("b.*, CONCAT(TRIM(COALESCE(u.first_name,'')), ' ', TRIM(COALESCE(u.last_name,''))) AS complainant_full_name, u.email AS complainant_email_addr")
            ->join('users u', 'u.id = b.complainant_user_id', 'left')
            ->orderBy('b.created_at', 'DESC');

        if ($statusFilter !== '') {
            $builder->where('b.status', $statusFilter);
        }
        if ($search !== '') {
            $search = trim($search);
            $builder->where(\App\Libraries\RecordSearch::clause([
                'b.complainant_name',
                'u.last_name',
                'u.first_name',
                'b.incident_type',
                'b.persons_involved',
                'b.respondent_name',
            ], ['b.incident_date', 'b.created_at'], $search), null, false);
        }

        $reports = $builder->get()->getResultArray();

        $pending       = $this->model->where('status', 'pending')->countAllResults();
        $investigating = $this->model->where('status', 'under_investigation')->countAllResults();
        $resolved      = $this->model->where('status', 'resolved')->countAllResults();
        $total         = $this->model->countAll();

        $viewFile = ($role === 'captain')
            ? 'dashboard/captain/blotter'
            : 'dashboard/secretary/blotter';

        return view($viewFile, [
            'reports'       => $reports,
            'pending'       => $pending,
            'investigating' => $investigating,
            'resolved'      => $resolved,
            'total'         => $total,
            'statusFilter'  => $statusFilter,
            'search'        => $search,
        ]);
    }

    // ── Admin: view single blotter report ────────────────────────────────────

    public function show(int $id)
    {
        $role   = (string)(session()->get('role') ?? 'captain');
        $db     = \Config\Database::connect();
        $report = $db->table('blotter_reports b')
            ->select("b.*, CONCAT(TRIM(COALESCE(u.first_name,'')), ' ', TRIM(COALESCE(u.last_name,''))) AS complainant_full_name, u.email AS complainant_email_addr")
            ->join('users u', 'u.id = b.complainant_user_id', 'left')
            ->where('b.id', $id)
            ->get()->getRowArray();

        if (! $report) {
            return redirect()->to('/' . $role . '/blotter')->with('error', 'Report not found.');
        }

        // Auto-suggest respondent address from census if not yet set
        // Try to match persons_involved name against household_members
        $suggestedRespondentAddr = $report['respondent_address'] ?? '';
        if (empty($suggestedRespondentAddr) && ! empty($report['persons_involved'])) {
            $nameParts = preg_split('/[\s,]+/', strtolower(trim($report['persons_involved'])));
            if (count($nameParts) >= 1) {
                $matchedMember = null;
                foreach ($nameParts as $part) {
                    if (strlen($part) < 2) continue;
                    $member = $db->table('household_members hm')
                        ->join('households h', 'h.household_no = hm.household_no')
                        ->select('hm.first_name, hm.last_name, h.household_no, h.zone')
                        ->where('LOWER(hm.last_name) LIKE', '%' . strtolower($part) . '%')
                        ->orWhere('LOWER(hm.first_name) LIKE', '%' . strtolower($part) . '%')
                        ->limit(1)
                        ->get()->getRowArray();
                    if ($member) {
                        $matchedMember = $member;
                        break;
                    }
                }
                if ($matchedMember) {
                    $suggestedRespondentAddr = ($matchedMember['zone'] ?? '') . ', Barangay Bacolod, Bato, Camarines Sur';
                }
            }
        }

        return view('dashboard/captain/blotter_detail', [
            'report'                 => $report,
            'role'                   => $role,
            'suggestedRespondentAddr' => $suggestedRespondentAddr,
        ]);
    }

    // ── Admin: update status ──────────────────────────────────────────────────

    public function updateStatus(int $id)
    {
        $role    = (string)(session()->get('role') ?? 'captain');
        $status  = $this->request->getPost('status');
        $remarks = $this->request->getPost('remarks') ?? '';

        $allowedStatuses = ['pending', 'under_investigation', 'file_to_action', 'resolved', 'dismissed'];
        if (! in_array($status, $allowedStatuses, true)) {
            return redirect()->back()->with('error', 'Invalid blotter status.');
        }

        $this->model->update($id, [
            'status'       => $status,
            'remarks'      => $remarks,
            'processed_by' => session()->get('user_id'),
        ]);

        return redirect()->to('/' . $role . '/blotter/' . $id)->with('success', 'Status updated.');
    }

    public function updateNarrative(int $id)
    {
        if (! can_role('secretary')) {
            return redirect()->back()->with('error', 'Only the secretary can edit blotter narratives.');
        }

        $report = $this->model->find($id);
        if (! $report) {
            return redirect()->to('/secretary/blotter')->with('error', 'Report not found.');
        }

        $complainantNarrative = trim($this->request->getPost('complainant_narrative') ?? '');
        $respondentNarrative  = trim($this->request->getPost('respondent_narrative') ?? '');
        if ($complainantNarrative === '' || $respondentNarrative === '') {
            return redirect()->back()->with('error', 'Both narratives are required.')->withInput();
        }

        $this->model->update($id, [
            'complainant_narrative' => $complainantNarrative,
            'respondent_narrative'  => $respondentNarrative,
            'narrative'             => "Complainant's Narrative:\n" . $complainantNarrative . "\n\nRespondent's Narrative:\n" . $respondentNarrative,
            'processed_by'          => session()->get('user_id'),
        ]);

        return redirect()->to('/secretary/blotter/' . $id)->with('success', 'Narratives updated successfully.');
    }

    public function updateHearingNarrative(int $id)
    {
        if (! can_role('secretary')) {
            return redirect()->back()->with('error', 'Only the secretary can record hearing narratives.');
        }

        $report = $this->model->find($id);
        if (! $report) {
            return redirect()->to('/secretary/blotter')->with('error', 'Report not found.');
        }

        $complainantNarrative = trim($this->request->getPost('hearing_complainant_narrative') ?? '');
        $respondentNarrative  = trim($this->request->getPost('hearing_respondent_narrative') ?? '');
        if ($complainantNarrative === '' && $respondentNarrative === '') {
            return redirect()->back()->with('error', 'Record at least one hearing narrative.')->withInput();
        }

        $this->model->update($id, [
            'hearing_complainant_narrative' => $complainantNarrative ?: null,
            'hearing_respondent_narrative'  => $respondentNarrative ?: null,
            'processed_by'                  => session()->get('user_id'),
        ]);

        return redirect()->to('/secretary/blotter/' . $id)->with('success', 'Hearing narratives recorded successfully.');
    }

    public function sendSummons(int $id)
    {
        $role   = (string)(session()->get('role') ?? 'captain');

        // Use JOIN query so we have complainant_full_name, complainant_email_addr,
        // captain and secretary names for the letter
        $db     = \Config\Database::connect();
        $report = $db->table('blotter_reports b')
            ->select("b.*, CONCAT(TRIM(COALESCE(u.first_name,'')), ' ', TRIM(COALESCE(u.last_name,''))) AS complainant_full_name, u.email AS complainant_email_addr")
            ->join('users u', 'u.id = b.complainant_user_id', 'left')
            ->where('b.id', $id)
            ->get()->getRowArray();

        if (! $report) {
            return redirect()->back()->with('error', 'Report not found.');
        }

        $hearingDate     = $this->request->getPost('hearing_date');
        $hearingTime     = $this->request->getPost('hearing_time');
        $respondentName  = trim($this->request->getPost('respondent_name')    ?? '');
        $respondentEmail = trim($this->request->getPost('respondent_email')   ?? '');
        $respondentAddr  = trim($this->request->getPost('respondent_address') ?? '');

        if (empty($hearingDate) || empty($hearingTime)) {
            return redirect()->back()->with('error', 'Please set a hearing date and time.');
        }

        // Save respondent info + hearing schedule
        $this->model->update($id, [
            'respondent_name'    => $respondentName,
            'respondent_email'   => $respondentEmail,
            'respondent_address' => $respondentAddr,
            'hearing_date'       => $hearingDate,
            'hearing_time'       => $hearingTime,
            'status'             => 'under_investigation',
            'summons_sent_at'    => date('Y-m-d H:i:s'),
            'processed_by'       => session()->get('user_id'),
        ]);

        // Resolve captain & secretary names for the letter signature block
        $userModel     = new \App\Models\UserModel();
        $captainRow    = $userModel->getActiveByRole('captain');
        $captainName   = $captainRow
            ? strtoupper(preg_replace('/\s+/', ' ', trim(
                ($captainRow['first_name'] ?? '') . ' ' .
                    ($captainRow['middle_name'] ?? '') . ' ' .
                    ($captainRow['last_name']   ?? '')
            )))
            : 'PUNONG BARANGAY';

        $secretaryRow  = $userModel->getAppointedSecretary();
        $secretaryName = $secretaryRow
            ? strtoupper(preg_replace('/\s+/', ' ', trim(
                ($secretaryRow['first_name'] ?? '') . ' ' .
                    ($secretaryRow['middle_name'] ?? '') . ' ' .
                    ($secretaryRow['last_name']   ?? '')
            )))
            : 'BARANGAY SECRETARY';

        $caseNo       = str_pad($id, 2, '0', STR_PAD_LEFT);
        $incidentType = $report['incident_type'];
        $hDate        = date('F d, Y', strtotime($hearingDate));
        $hTime        = date('h:i A', strtotime($hearingTime));
        $incidentDate = ! empty($report['incident_date'])
            ? date('F d, Y', strtotime($report['incident_date']))
            : '';
        $location     = $report['location'] ?? 'Barangay Bacolod';
        $hearingNotes = $report['hearing_notes'] ?? '';

        // Complainant name: prefer JOIN alias, fall back to stored name
        $complainantName  = trim($report['complainant_full_name'] ?? $report['complainant_name'] ?? '');
        $complainantEmail = trim($report['complainant_email_addr'] ?? $report['complainant_email'] ?? '');

        $emailService = new EmailService();
        $errors       = [];

        // ── Send to complainant (full letter) ─────────────────────────────────
        if (! empty($complainantEmail)) {
            try {
                $emailService->sendSummonsWithLetter(
                    $complainantEmail,
                    $complainantName ?: 'Complainant',
                    $caseNo,
                    $incidentType,
                    $hDate,
                    $hTime,
                    $complainantName,
                    $respondentName ?: ($report['persons_involved'] ?? ''),
                    $respondentAddr,
                    $incidentDate,
                    $location,
                    $hearingNotes,
                    $captainName,
                    $secretaryName,
                    'complainant'
                );
            } catch (\Throwable $e) {
                $errors[] = 'Could not send to complainant: ' . $e->getMessage();
                log_message('error', 'Summons to complainant failed: ' . $e->getMessage());
            }
        }

        // ── Send to respondent (full letter) ─────────────────────────────────
        if (! empty($respondentEmail)) {
            try {
                $emailService->sendSummonsWithLetter(
                    $respondentEmail,
                    $respondentName ?: 'Respondent',
                    $caseNo,
                    $incidentType,
                    $hDate,
                    $hTime,
                    $complainantName,
                    $respondentName,
                    $respondentAddr,
                    $incidentDate,
                    $location,
                    $hearingNotes,
                    $captainName,
                    $secretaryName,
                    'respondent'
                );
            } catch (\Throwable $e) {
                $errors[] = 'Could not send to respondent: ' . $e->getMessage();
                log_message('error', 'Summons to respondent failed: ' . $e->getMessage());
            }
        }

        if (! empty($errors)) {
            return redirect()->to('/' . $role . '/blotter/' . $id)
                ->with('error', implode(' | ', $errors));
        }

        $sent = [];
        if (! empty($complainantEmail)) $sent[] = 'complainant';
        if (! empty($respondentEmail))  $sent[] = 'respondent';
        $sentLabel = count($sent) === 2
            ? 'Summons sent to both complainant and respondent.'
            : (count($sent) === 1
                ? 'Summons sent to ' . $sent[0] . '.'
                : 'Hearing schedule saved. No email addresses available to send summons.');

        return redirect()->to('/' . $role . '/blotter/' . $id)
            ->with('success', 'Hearing schedule saved. ' . $sentLabel);
    }

    // ── Admin: reschedule hearing ─────────────────────────────────────────────

    public function reschedule(int $id)
    {
        $role        = (string)(session()->get('role') ?? 'captain');
        $hearingDate = $this->request->getPost('hearing_date');
        $hearingTime = $this->request->getPost('hearing_time');
        $notes       = trim($this->request->getPost('hearing_notes') ?? '');

        if (empty($hearingDate) || empty($hearingTime)) {
            return redirect()->back()->with('error', 'Please provide both a date and time for the hearing.');
        }

        $this->model->update($id, [
            'hearing_date'  => $hearingDate,
            'hearing_time'  => $hearingTime,
            'hearing_notes' => $notes ?: null,
            'scheduled_by'  => session()->get('user_id'),
            'status'        => 'under_investigation',
        ]);

        return redirect()->to('/' . $role . '/blotter/' . $id)
            ->with('success', 'Hearing schedule updated successfully.');
    }

    // ── Admin: view/print certificate to file action ─────────────────────────

    public function viewCertificate(int $id)
    {
        $payload = $this->certificatePayload($id);
        if ($payload === null) {
            $role = (string) (session()->get('role') ?? 'captain');
            return redirect()->to('/' . $role . '/blotter')->with('error', 'Report not found.');
        }

        return view('blotter_certificate', $payload + ['mode' => 'screen']);
    }

    public function downloadCertificate(int $id)
    {
        $payload = $this->certificatePayload($id);
        if ($payload === null) {
            $role = (string) (session()->get('role') ?? 'captain');
            return redirect()->to('/' . $role . '/blotter')->with('error', 'Report not found.');
        }

        $html = view('blotter_certificate', $payload + ['mode' => 'pdf']);
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->setChroot(FCPATH);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'certificate-to-file-action-BL-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * @return array<string, mixed>|null
     */
    private function certificatePayload(int $id): ?array
    {
        $role = (string) (session()->get('role') ?? 'captain');
        $report = \Config\Database::connect()->table('blotter_reports b')
            ->select("b.*, CONCAT(TRIM(COALESCE(u.first_name,'')), ' ', TRIM(COALESCE(u.last_name,''))) AS complainant_full_name, u.email AS complainant_email_addr")
            ->join('users u', 'u.id = b.complainant_user_id', 'left')
            ->where('b.id', $id)
            ->get()->getRowArray();

        if (! $report) {
            return null;
        }

        $userModel = new UserModel();
        $officialName = static function (?array $row, string $fallback): string {
            if (! $row) {
                return $fallback;
            }
            $name = strtoupper(preg_replace('/\s+/', ' ', trim(
                ($row['first_name'] ?? '') . ' ' .
                ($row['middle_name'] ?? '') . ' ' .
                ($row['last_name'] ?? '')
            )) ?? '');

            return $name !== '' ? $name : $fallback;
        };

        return [
            'report'        => $report,
            'role'          => $role,
            'captainName'   => $officialName($userModel->getActiveByRole('captain'), 'PUNONG BARANGAY'),
            'secretaryName' => $officialName($userModel->getAppointedSecretary(), 'BARANGAY SECRETARY'),
            'downloadUrl'   => '/' . $role . '/blotter/certificate/' . $id . '/download',
            'viewUrl'       => '/' . $role . '/blotter/certificate/' . $id,
            'barangaySeal'  => $this->certificateAssetUri('bacolod.png'),
            'municipalitySeal' => $this->certificateAssetUri('Picture1.png'),
        ];
    }

    private function certificateAssetUri(string $filename): string
    {
        $path = FCPATH . ltrim($filename, '/\\');
        if (! is_file($path)) {
            return '/' . ltrim($filename, '/');
        }

        return 'data:image/png;base64,' . base64_encode((string) file_get_contents($path));
    }

    public function evidence(int $id, int $index)
    {
        $role = (string) (session()->get('role') ?? '');
        if (! in_array($role, ['secretary', 'captain', 'admin'], true)) {
            return $this->response->setStatusCode(403);
        }

        $report = $this->model->find($id);
        if (! is_array($report)) {
            return $this->response->setStatusCode(404);
        }
        $photos = json_decode((string) ($report['evidence_photos'] ?? ''), true);
        $path = is_array($photos) ? ($photos[$index] ?? '') : '';
        $path = \App\Controllers\HouseholdUploadController::normalizeUploadPath(is_string($path) ? $path : '');
        if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'uploads/blotter_evidence/')) {
            return $this->response->setStatusCode(404);
        }

        foreach ([FCPATH . $path, WRITEPATH . $path] as $localPath) {
            if (is_file($localPath)) {
                $mimeType = mime_content_type($localPath) ?: 'application/octet-stream';

                return $this->response->setContentType($mimeType)->setBody((string) file_get_contents($localPath));
            }
        }

        $storage = new \App\Libraries\HouseholdUploadStorage();
        $object = $storage->download($path);
        if ($object !== null) {
            return $this->response->setContentType($object['mime'])->setBody($object['body']);
        }

        $signedUrl = $storage->signedUrl($path);

        return $signedUrl === null
            ? $this->response->setStatusCode(404)
            : redirect()->to($signedUrl);
    }

    // ── Admin: view/print summons letter ─────────────────────────────────────

    public function viewLetter(int $id)
    {
        $role   = (string)(session()->get('role') ?? 'captain');
        $db     = \Config\Database::connect();
        $report = $db->table('blotter_reports b')
            ->select("b.*, CONCAT(TRIM(COALESCE(u.first_name,'')), ' ', TRIM(COALESCE(u.last_name,''))) AS complainant_full_name, u.email AS complainant_email_addr")
            ->join('users u', 'u.id = b.complainant_user_id', 'left')
            ->where('b.id', $id)
            ->get()->getRowArray();

        if (! $report) {
            return redirect()->to('/' . $role . '/blotter')->with('error', 'Report not found.');
        }

        // Fetch live captain and secretary names from users table
        $userModel = new \App\Models\UserModel();

        $captainRow   = $userModel->getActiveByRole('captain');
        $captainName  = $captainRow
            ? strtoupper(trim(
                ($captainRow['first_name']  ?? '') . ' ' .
                    ($captainRow['middle_name'] ?? '') . ' ' .
                    ($captainRow['last_name']   ?? '')
            ))
            : 'PUNONG BARANGAY';

        $secretaryRow  = $userModel->getAppointedSecretary();
        $secretaryName = $secretaryRow
            ? strtoupper(trim(
                ($secretaryRow['first_name']  ?? '') . ' ' .
                    ($secretaryRow['middle_name'] ?? '') . ' ' .
                    ($secretaryRow['last_name']   ?? '')
            ))
            : 'BARANGAY SECRETARY';

        // Collapse multiple spaces from empty middle names
        $captainName   = preg_replace('/\s+/', ' ', $captainName);
        $secretaryName = preg_replace('/\s+/', ' ', $secretaryName);

        // Mark letter as issued
        $this->model->update($id, ['letter_issued_at' => date('Y-m-d H:i:s')]);

        return view('blotter_letter', [
            'report'        => $report,
            'role'          => $role,
            'captainName'   => $captainName,
            'secretaryName' => $secretaryName,
        ]);
    }
}
