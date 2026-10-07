<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ScheduleModel;

class ScheduleController extends BaseController
{
    protected ScheduleModel $model;

    public function __construct()
    {
        $this->model = new ScheduleModel();
    }

    private function colorForEventType(string $eventType): string
    {
        return [
            'hearing'     => '#c0392b',
            'meeting'     => '#2980b9',
            'appointment' => '#1d2448',
            'event'       => '#16a085',
            'other'       => '#7f8c8d',
        ][strtolower(trim($eventType))] ?? '#7f8c8d';
    }

    public function day(string $dateStr)
    {
        $role   = session()->get('role');
        $userId = (int) session()->get('user_id');

        if (! ScheduleModel::isOfficialSchedulingRole((string) $role)) {
            return redirect()->to('/login')->with('error', 'You do not have permission to access the calendar.');
        }

        // Validate date format
        $parsed = date_create_from_format('Y-m-d', $dateStr);
        if (! $parsed || date_format($parsed, 'Y-m-d') !== $dateStr) {
            return redirect()->to('/' . $role . '/calendar')->with('cal_error', 'Invalid date.');
        }

        // Regular schedule events for this day
        $events = $this->model->getVisibleByDate($dateStr, $userId, $role);

        // Inject blotter hearings that fall on this date
        $db       = \Config\Database::connect();
        $hearings = $db->table('blotter_reports')
            ->select('id, incident_type, hearing_date, hearing_time, respondent_name, complainant_name')
            ->where('hearing_date', $dateStr)
            ->where('hearing_date IS NOT NULL')
            ->get()->getResultArray();

        foreach ($hearings as $h) {
            $events[] = [
                'id'          => 'bl-' . $h['id'],
                'title'       => 'Hearing: ' . $h['incident_type'],
                'description' => 'Complainant: ' . $h['complainant_name'] . ' vs. ' . ($h['respondent_name'] ?: 'Unknown'),
                'event_date'  => $h['hearing_date'],
                'start_time'  => $h['hearing_time'],
                'end_time'    => null,
                'event_type'  => 'hearing',
                'color'       => '#c0392b',
                'location'    => 'Barangay Hall',
                'blotter_id'  => $h['id'],
                'is_blotter'  => true,
                'created_by'  => null,
            ];
        }

        // Sort by start_time
        usort($events, fn($a, $b) => strcmp($a['start_time'] ?? '', $b['start_time'] ?? ''));

        $year  = (int) date('Y', strtotime($dateStr));
        $month = (int) date('n', strtotime($dateStr));

        return view('dashboard/captain/calendar_day', [
            'role'      => $role,
            'dateStr'   => $dateStr,
            'events'    => $events,
            'year'      => $year,
            'month'     => $month,
            'pageTitle' => 'Events — ' . date('F d, Y', strtotime($dateStr)),
        ]);
    }

    public function view(int $id)
    {
        $role   = session()->get('role');
        $userId = (int) session()->get('user_id');

        if (! ScheduleModel::isOfficialSchedulingRole((string) $role)) {
            return redirect()->to('/login')->with('error', 'You do not have permission to access the calendar.');
        }

        $event  = $this->model->find($id);

        if (! $event) {
            return redirect()->to('/' . $role . '/calendar')->with('cal_error', 'Event not found.');
        }

        $canEdit     = ((int) $event['created_by'] === $userId);
        $isSharedToMe = in_array($role, ['admin', 'secretary', 'captain'], true);

        if (! $canEdit && ! $isSharedToMe) {
            return redirect()->to('/' . $role . '/calendar')->with('cal_error', 'You do not have access to this event.');
        }

        $captainUser = null;
        if ($role === 'secretary') {
            $userModel   = new \App\Models\UserModel();
            $captainUser = $userModel->getActiveByRole('captain');
        }

        return view('dashboard/captain/event_detail', [
            'event'       => $event,
            'role'        => $role,
            'canEdit'     => $canEdit,
            'captainUser' => $captainUser,
        ]);
    }

    public function index()
    {
        $role   = session()->get('role');
        $userId = (int) session()->get('user_id');

        if (! ScheduleModel::isOfficialSchedulingRole((string) $role)) {
            return redirect()->to('/login')->with('error', 'You do not have permission to access the calendar.');
        }

        $year   = (int) ($_GET['year']  ?? date('Y'));
        $month  = (int) ($_GET['month'] ?? date('n'));

        if ($month < 1) {
            $month = 12;
            $year--;
        }
        if ($month > 12) {
            $month = 1;
            $year++;
        }

        $byDate   = $this->model->getVisibleByMonth($year, $month, $userId, $role);
        $upcoming = $this->model->getUpcomingVisible($userId, $role, 2, 8);

        $db    = \Config\Database::connect();
        $upcomingHearings = $db->table('blotter_reports')
            ->select('id, incident_type, hearing_date, hearing_time, respondent_name, complainant_name')
            ->where('hearing_date IS NOT NULL')
            ->where('hearing_date >=', date('Y-m-d'))
            ->where('hearing_date <=', date('Y-m-d', strtotime('+2 days')))
            ->orderBy('hearing_date', 'ASC')
            ->orderBy('hearing_time', 'ASC')
            ->limit(8)
            ->get()->getResultArray();

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));
        $hearings = $db->table('blotter_reports')
            ->select('id, incident_type, hearing_date, hearing_time, respondent_name, complainant_name')
            ->where('hearing_date IS NOT NULL')
            ->where('hearing_date >=', $start)
            ->where('hearing_date <=', $end)
            ->get()->getResultArray();

        foreach ($hearings as $h) {
            $d = $h['hearing_date'];
            $byDate[$d][] = [
                'id'          => 'bl-' . $h['id'],
                'title'       => 'Hearing: ' . $h['incident_type'],
                'description' => 'Complainant: ' . $h['complainant_name'] . ' vs. ' . ($h['respondent_name'] ?: 'Unknown'),
                'event_date'  => $d,
                'start_time'  => $h['hearing_time'],
                'end_time'    => null,
                'event_type'  => 'hearing',
                'color'       => '#c0392b',
                'location'    => 'Barangay Hall',
                'blotter_id'  => $h['id'],
                'is_blotter'  => true,
            ];
        }

        foreach ($byDate as &$dayEvents) {
            usort($dayEvents, fn($a, $b) => strcmp($a['start_time'] ?? '', $b['start_time'] ?? ''));
        }
        unset($dayEvents);

        $captainUser = null;
        if ($role === 'secretary') {
            $userModel   = new \App\Models\UserModel();
            $captainUser = $userModel->getActiveByRole('captain');
        }

        return view('dashboard/captain/calendar', [
            'role'        => $role,
            'year'        => $year,
            'month'       => $month,
            'byDate'      => $byDate,
            'upcoming'    => $upcoming,
            'upcomingHearings' => $upcomingHearings,
            'captainUser' => $captainUser,
        ]);
    }

    public function store()
    {
        $role   = session()->get('role');
        $userId = (int) session()->get('user_id');
        $post   = $this->request->getPost();

        if (! ScheduleModel::isOfficialSchedulingRole((string) $role)) {
            return redirect()->to('/login')->with('error', 'You do not have permission to create calendar entries.');
        }

        if (empty($post['title']) || empty($post['event_date'])) {
            return redirect()->back()->with('cal_error', 'Title and date are required.');
        }

        // Block adding events to past dates
        if ($post['event_date'] < date('Y-m-d')) {
            return redirect()->back()->with('cal_error', 'Cannot add events to past dates. Please select today or a future date.');
        }

        $shareWithCaptain = ! empty($post['share_with_captain']) && in_array($role, ['secretary', 'admin'], true);
        $visibility = $shareWithCaptain ? 'shared' : 'private';
        $sharedWith = null;

        if ($shareWithCaptain) {
            $userModel   = new \App\Models\UserModel();
            $captainUser = $userModel->getActiveByRole('captain');
            $sharedWith  = $captainUser ? (int) $captainUser['id'] : null;
        }

        $eventType = $post['event_type'] ?? 'appointment';
        $this->model->insert([
            'title'       => trim($post['title']),
            'description' => trim($post['description'] ?? ''),
            'event_date'  => $post['event_date'],
            'start_time'  => $post['start_time']  ?: null,
            'end_time'    => $post['end_time']     ?: null,
            'event_type'  => $eventType,
            'color'       => $this->colorForEventType($eventType),
            'location'    => trim($post['location'] ?? ''),
            'created_by'  => $userId,
            'visibility'  => $visibility,
            'shared_with' => $sharedWith,
        ]);

        $qs  = http_build_query(['year' => date('Y', strtotime($post['event_date'])), 'month' => date('n', strtotime($post['event_date']))]);
        $msg = 'Event added successfully.';
        if ($shareWithCaptain && $sharedWith) {
            $msg .= ' It has also been added to the Captain\'s calendar.';
        }
        return redirect()->to('/' . $role . '/calendar?' . $qs)->with('cal_success', $msg);
    }

    public function update(int $id)
    {
        $role   = session()->get('role');
        $userId = (int) session()->get('user_id');
        $post   = $this->request->getPost();
        $event  = $this->model->find($id);

        if (! $event || (int) $event['created_by'] !== $userId) {
            return redirect()->back()->with('cal_error', 'You can only edit events you created.');
        }

        if (empty($post['title']) || empty($post['event_date'])) {
            return redirect()->back()->with('cal_error', 'Title and date are required.');
        }

        $shareWithCaptain = ! empty($post['share_with_captain']) && in_array($role, ['secretary', 'admin'], true);
        $visibility = $event['visibility'];
        $sharedWith = $event['shared_with'];

        if (in_array($role, ['secretary', 'admin'], true)) {
            $visibility = $shareWithCaptain ? 'shared' : 'private';
            if ($shareWithCaptain) {
                $userModel   = new \App\Models\UserModel();
                $captainUser = $userModel->getActiveByRole('captain');
                $sharedWith  = $captainUser ? (int) $captainUser['id'] : null;
            } else {
                $sharedWith = null;
            }
        }

        $eventType = $post['event_type'] ?? 'appointment';
        $description = \App\Models\ScheduleModel::visibleDescription($post['description'] ?? '');
        $marker = \App\Models\ScheduleModel::markerIn($event['description'] ?? '');
        if ($marker !== '') {
            $description = trim($description . ' ' . $marker);
        }
        $this->model->update($id, [
            'title'       => trim($post['title']),
            'description' => $description,
            'event_date'  => $post['event_date'],
            'start_time'  => $post['start_time']  ?: null,
            'end_time'    => $post['end_time']     ?: null,
            'event_type'  => $eventType,
            'color'       => $this->colorForEventType($eventType),
            'location'    => trim($post['location'] ?? ''),
            'visibility'  => $visibility,
            'shared_with' => $sharedWith,
        ]);

        $qs = http_build_query(['year' => date('Y', strtotime($post['event_date'])), 'month' => date('n', strtotime($post['event_date']))]);
        return redirect()->to('/' . $role . '/calendar?' . $qs)->with('cal_success', 'Event updated.');
    }

    public function delete(int $id)
    {
        $role   = session()->get('role');
        $userId = (int) session()->get('user_id');
        $event  = $this->model->find($id);

        if (! $event || (int) $event['created_by'] !== $userId) {
            return redirect()->back()->with('cal_error', 'You can only delete events you created.');
        }

        $this->model->delete($id);

        $qs = http_build_query(['year' => date('Y', strtotime($event['event_date'])), 'month' => date('n', strtotime($event['event_date']))]);
        return redirect()->to('/' . $role . '/calendar?' . $qs)->with('cal_success', 'Event deleted.');
    }

    public function publicCalendar()
    {
        return $this->renderCalendarPage('public_events', '/events');
    }

    /**
     * Same calendar as `publicCalendar()` but rendered inside the resident
     * dashboard shell so clicking an "Upcoming Event" notification does not
     * drop the resident back onto the public landing page.
     */
    public function residentCalendar()
    {
        return $this->renderCalendarPage('dashboard/resident/events', '/resident/events');
    }

    /**
     * Shared data-prep used by both the public and resident event pages. The
     * only differences are the view template rendered and the base URL used
     * for the prev/next month navigation links.
     */
    private function renderCalendarPage(string $viewName, string $baseUrl)
    {
        $year  = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));

        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }
        if ($month < 1) {
            $month = 12;
            $year--;
        }
        if ($month > 12) {
            $month = 1;
            $year++;
        }

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));
        $rows  = $this->model->getPublicEvents($start, $end, 200);

        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row['event_date']][] = $row;
        }

        $prevMonth = $month - 1;
        $prevYear  = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $nextMonth = $month + 1;
        $nextYear  = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        return view($viewName, [
            'year'      => $year,
            'month'     => $month,
            'monthName' => date('F Y', strtotime($start)),
            'byDate'    => $byDate,
            'events'    => $rows,
            'upcoming'  => $this->model->getPublicEvents(
                date('Y-m-d'),
                date('Y-m-d', strtotime('+90 days')),
                8
            ),
            'prevUrl'   => $baseUrl . '?year=' . $prevYear . '&month=' . $prevMonth,
            'nextUrl'   => $baseUrl . '?year=' . $nextYear . '&month=' . $nextMonth,
            'isLoggedIn' => (bool) session()->get('user_id'),
        ]);
    }

    public function listAll()
    {
        $role   = session()->get('role');
        $userId = (int) session()->get('user_id');

        if (! ScheduleModel::isOfficialSchedulingRole((string) $role)) {
            return redirect()->to('/login')->with('error', 'You do not have permission to access the calendar.');
        }

        $search = trim($_GET['search'] ?? '');
        $type   = trim($_GET['type']   ?? '');

        $events = $this->model->getAllVisible($userId, $role, $search, $type);

        return view('dashboard/captain/events_list', [
            'events' => $events,
            'role'   => $role,
            'search' => $search,
            'type'   => $type,
        ]);
    }
}
