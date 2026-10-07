<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangayActivityModel extends Model
{
    protected $table         = 'barangay_activities';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'title',
        'category',
        'description',
        'requirements',
        'notify_residents',
        'activity_date',
        'start_date',
        'end_date',
        'conducted_date',
        'min_age',
        'max_age',
        'eligibility_groups',
        'eligibility_other',
        'target_participants',
        'start_time',
        'end_time',
        'venue',
        'banner_path',
        'status',
        'created_by',
    ];

    public const CATEGORIES = ['Sports', 'Livelihood', 'Health', 'Education', 'Environment', 'Clean-up Drive', 'Cultural', 'Other'];

    public static function isCleanupDrive(string $category, string $title = ''): bool
    {
        if (strcasecmp(trim($category), 'Clean-up Drive') === 0) {
            return true;
        }

        return preg_match('/clean[\s-]*up\s+drive/i', $title) === 1;
    }

    public const REQUIREMENT_TYPES = [
        'document' => 'Document',
        'image'    => 'Image',
    ];

    public const REQUIREMENT_LABEL_SUGGESTIONS = [
        'Birth Certificate',
        'PSA Birth Certificate',
        'Valid ID',
        'Barangay ID',
        'School ID',
        'Medical Certificate',
        'Certificate of Indigency',
        '2x2 ID Picture',
        'Full-body Picture',
    ];

    public const EDUCATION_ELIGIBILITY = [
        'graduating'  => 'Graduating Students',
        'college'     => 'All College Students',
        'high_school' => 'All High School Students',
        'osy'         => 'Out-of-School Youth',
        'other'       => 'Other',
    ];

    /** @return array<int, array{type:string,label:string}> */
    public static function decodeRequirements(?string $requirements): array
    {
        $raw = trim((string) $requirements);
        if ($raw === '') {
            return [];
        }

        if (str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $rows = [];
                foreach ($decoded as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $type  = strtolower(trim((string) ($item['type'] ?? 'document')));
                    $label = trim((string) ($item['label'] ?? ''));
                    if ($label === '') {
                        continue;
                    }
                    $rows[] = [
                        'type'  => in_array($type, ['image', 'photo'], true) ? 'image' : 'document',
                        'label' => $label,
                    ];
                }

                return $rows;
            }
        }

        $parts = str_contains($raw, ' | ')
            ? explode(' | ', $raw)
            : explode(',', $raw);
        $rows  = [];
        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/^(document|photo|image)\s*:\s*(.+)$/i', $part, $match) === 1) {
                $type = strtolower($match[1]) === 'document' ? 'document' : 'image';
                $rows[] = ['type' => $type, 'label' => trim($match[2])];
                continue;
            }
            $rows[] = ['type' => 'document', 'label' => $part];
        }

        return $rows;
    }

    /** @param array<int, array{type?:string,label?:string}> $rows */
    public static function encodeRequirements(array $rows): ?string
    {
        $clean = [];
        foreach ($rows as $row) {
            $type  = strtolower(trim((string) ($row['type'] ?? 'document')));
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            if (mb_strlen($label) > 120) {
                $label = mb_substr($label, 0, 120);
            }
            $clean[] = [
                'type'  => in_array($type, ['image', 'photo'], true) ? 'image' : 'document',
                'label' => $label,
            ];
            if (count($clean) >= 15) {
                break;
            }
        }

        return $clean === [] ? null : json_encode($clean, JSON_UNESCAPED_UNICODE);
    }

    public static function isFileRequirement(string $requirement): bool
    {
        return preg_match('/^(document|photo|image)\s*:/i', $requirement) === 1;
    }

    public static function requirementKind(string $requirement): string
    {
        if (preg_match('/^(photo|image)\s*:/i', $requirement) === 1) {
            return 'image';
        }

        return 'document';
    }

    /** @return string[] */
    public static function parseEligibilityGroups(?string $groups): array
    {
        if ($groups === null || trim($groups) === '') {
            return [];
        }

        $allowed = array_keys(self::EDUCATION_ELIGIBILITY);

        return array_values(array_filter(
            array_map('trim', explode(',', $groups)),
            static fn (string $group): bool => in_array($group, $allowed, true)
        ));
    }

    /**
     * @return array{eligibility_groups:?string,eligibility_other:?string}|null
     */
    public static function collectPostedEligibility($request, ?string &$error = null): ?array
    {
        $allowed = array_keys(self::EDUCATION_ELIGIBILITY);
        $groups  = $request->getPost('eligibility_groups') ?? [];
        if (! is_array($groups)) {
            $groups = [];
        }
        $groups = array_values(array_unique(array_filter(
            array_map('strval', $groups),
            static fn (string $group): bool => in_array($group, $allowed, true)
        )));
        $other = trim((string) $request->getPost('eligibility_other'));
        if (! in_array('other', $groups, true)) {
            $other = '';
        } elseif ($other === '') {
            $error = 'Please specify the other eligibility group.';

            return null;
        }
        if (mb_strlen($other) > 200) {
            $other = mb_substr($other, 0, 200);
        }

        return [
            'eligibility_groups' => $groups !== [] ? implode(',', $groups) : null,
            'eligibility_other'  => $other !== '' ? $other : null,
        ];
    }

    /** @return array<int, array{type:string,label:string}> */
    public static function collectPostedRequirements($request): array
    {
        $types  = $request->getPost('req_type') ?? [];
        $labels = $request->getPost('req_label') ?? [];
        if (! is_array($types) || ! is_array($labels)) {
            return [];
        }

        $rows = [];
        foreach ($types as $index => $type) {
            $rows[] = [
                'type'  => (string) $type,
                'label' => (string) ($labels[$index] ?? ''),
            ];
        }

        return $rows;
    }

    /** @return string[] */
    public static function eligibilitySummary(array $row): array
    {
        $labels = [];
        $minAge = $row['min_age'] ?? null;
        $maxAge = $row['max_age'] ?? null;
        if (($minAge !== null && $minAge !== '') || ($maxAge !== null && $maxAge !== '')) {
            $labels[] = (($minAge !== null && $minAge !== '') ? (int) $minAge : '0')
                . '–'
                . (($maxAge !== null && $maxAge !== '') ? (int) $maxAge : 'any')
                . ' years';
        }

        foreach (self::parseEligibilityGroups($row['eligibility_groups'] ?? null) as $group) {
            if ($group === 'other') {
                $other = trim((string) ($row['eligibility_other'] ?? ''));
                $labels[] = $other !== '' ? $other : 'Other';
                continue;
            }
            $labels[] = self::EDUCATION_ELIGIBILITY[$group] ?? $group;
        }

        return $labels;
    }

    public static function educationEligibilityError(?array $user, array $activity): ?string
    {
        $groups = self::parseEligibilityGroups($activity['eligibility_groups'] ?? null);
        $checkable = array_values(array_filter($groups, static fn (string $group): bool => $group !== 'other'));
        if ($checkable === []) {
            return null;
        }

        $profile = resident_education_profile($user);
        foreach ($checkable as $group) {
            if (resident_matches_education_group($profile, $group)) {
                return null;
            }
        }

        $labels = [];
        foreach ($checkable as $group) {
            $labels[] = self::EDUCATION_ELIGIBILITY[$group] ?? $group;
        }

        return 'Your census or SK youth record does not match the educational eligibility for this activity (' . implode(', ', $labels) . ').';
    }

    /** Posted activities, newest date first, with the official who posted them. */
    public function visibleToResidents(int $limit = 0): array
    {
        $builder = $this->listBuilder()->whereIn('a.status', ['Upcoming', 'Active', 'Posted']);
        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    /** Every activity for the secretary and captain management page. */
    public function forOfficials(): array
    {
        return $this->listBuilder()->get()->getResultArray();
    }

    private function listBuilder()
    {
        return $this->db->table($this->table . ' a')
            ->select('a.*, u.first_name, u.last_name, u.role AS poster_role')
            ->join('users u', 'u.id = a.created_by', 'left')
            ->orderBy('a.activity_date', 'DESC')
            ->orderBy('a.start_time', 'DESC')
            ->orderBy('a.id', 'DESC');
    }

    /** @return string[] */
    public static function parseRequirements(?string $requirements): array
    {
        $labels = [];
        foreach (self::decodeRequirements($requirements) as $row) {
            $type = $row['type'] === 'image' ? 'Image' : 'Document';
            $labels[] = $type . ': ' . $row['label'];
        }

        return $labels;
    }

    public function registrationCount(int $activityId): int
    {
        if (! $this->db->tableExists('barangay_activity_registrations')) {
            return 0;
        }

        return (int) $this->db->table('barangay_activity_registrations')
            ->where('activity_id', $activityId)
            ->countAllResults();
    }

    public function registrationFor(int $activityId, int $userId): ?array
    {
        if (! $this->db->tableExists('barangay_activity_registrations')) {
            return null;
        }

        $row = $this->db->table('barangay_activity_registrations')
            ->where('activity_id', $activityId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function syncDateStatuses(): void
    {
        if (! $this->db->fieldExists('conducted_date', $this->table)) {
            return;
        }

        $today = date('Y-m-d');
        foreach ($this->findAll() as $row) {
            if (($row['status'] ?? '') === 'Cancelled') {
                continue;
            }

            $date = $row['conducted_date'] ?? $row['activity_date'] ?? null;
            if (! $date) {
                continue;
            }

            $status = $date < $today ? 'Completed' : ($date === $today ? 'Active' : 'Upcoming');
            if ($status !== ($row['status'] ?? '')) {
                $this->update((int) $row['id'], ['status' => $status]);
            }
        }
    }
}
