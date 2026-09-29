<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table         = 'schedules';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    public static function isOfficialSchedulingRole(string $role): bool
    {
        return in_array($role, ['admin', 'secretary', 'captain'], true);
    }

    protected $allowedFields = [
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'event_type',
        'color',
        'location',
        'blotter_id',
        'created_by',
        'visibility',
        'shared_with',
    ];

    /**
     * Get all events for a specific date visible to a user.
     */
    public function getVisibleByDate(string $dateStr, int $userId, string $role): array
    {
        $db = \Config\Database::connect();

        if (in_array($role, ['admin', 'secretary', 'captain'], true)) {
            return $db->table('schedules')
                ->where('event_date', $dateStr)
                ->orderBy('start_time', 'ASC')
                ->get()->getResultArray();
        }

        return [];
    }

    /**
     * Get events visible to a given user.
     * Captain: sees own events + events shared_with them.
     * Secretary: sees only own events.
     */
    public function getVisibleByMonth(int $year, int $month, int $userId, string $role): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

        $db = \Config\Database::connect();

        if (in_array($role, ['admin', 'secretary', 'captain'], true)) {
            // Official calendar: all appointed secretary/captain events are visible.
            $rows = $db->table('schedules')
                ->where('event_date >=', $start)
                ->where('event_date <=', $end)
                ->orderBy('event_date', 'ASC')
                ->orderBy('start_time', 'ASC')
                ->get()->getResultArray();
        } else {
            $rows = [];
        }

        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row['event_date']][] = $row;
        }
        return $byDate;
    }

    /**
     * Get upcoming events visible to a user.
     */
    public function getUpcomingVisible(int $userId, string $role, int $daysAhead = 2, int $limit = 8): array
    {
        $db = \Config\Database::connect();

        if (in_array($role, ['admin', 'secretary', 'captain'], true)) {
            return $db->table('schedules')
                ->where('event_date >=', date('Y-m-d'))
                ->where('event_date <=', date('Y-m-d', strtotime('+' . $daysAhead . ' days')))
                ->orderBy('event_date', 'ASC')
                ->orderBy('start_time', 'ASC')
                ->limit($limit)
                ->get()->getResultArray();
        }

        return [];
    }

    /**
     * Get all events visible to a user (for list view).
     */
    public function getAllVisible(int $userId, string $role, string $search = '', string $type = ''): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('schedules');

        if (! in_array($role, ['admin', 'secretary', 'captain'], true)) {
            return [];
        }

        if ($search !== '') {
            $s = $db->escapeLikeString($search);
            $builder->groupStart()
                ->like('title', $search)
                ->orLike('description', $search)
                ->orLike('location', $search)
                ->groupEnd();
        }
        if ($type !== '') {
            $builder->where('event_type', $type);
        }

        return $builder->orderBy('event_date', 'DESC')->orderBy('start_time', 'ASC')->get()->getResultArray();
    }

    /**
     * Community calendar entries that can be shown without logging in.
     * Blotter hearings and concern appointments are excluded.
     *
     * @return list<array<string, mixed>>
     */
    public function getPublicEvents(string $start, string $end, int $limit = 100): array
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('schedules')) {
            return [];
        }

        $builder = $db->table('schedules')
            ->select('id, title, description, event_date, start_time, end_time, event_type, location')
            ->where('event_date >=', $start)
            ->where('event_date <=', $end)
            ->whereIn('event_type', ['event', 'meeting', 'appointment', 'other'])
            ->groupStart()
                ->where('blotter_id', null)
                ->orWhere('blotter_id', 0)
            ->groupEnd();

        if ($db->fieldExists('concern_id', 'schedules')) {
            $builder->groupStart()
                ->where('concern_id', null)
                ->orWhere('concern_id', 0)
            ->groupEnd();
        }

        return $builder
            ->orderBy('event_date', 'ASC')
            ->orderBy('start_time', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
