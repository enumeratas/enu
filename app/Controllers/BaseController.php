<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    protected function cleanContactNumber(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return preg_match('/^\d{11}$/', $value) ? $value : null;
    }

    protected function isApiRequest(): bool
    {
        $acceptHeader = strtolower((string) $this->request->getHeaderLine('Accept'));
        $requestedWith = strtolower((string) $this->request->getHeaderLine('X-Requested-With'));

        return $this->request->isAJAX()
            || str_contains($acceptHeader, 'application/json')
            || $requestedWith === 'xmlhttprequest';
    }

    protected function jsonResponse(array $payload, int $statusCode = 200)
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($payload);
    }

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['pii', 'access'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Match a user (by first/last name) to their census record within a household.
     *
     * Tries, in order:
     *   1. Exact first+last match against household_members
     *   2. Exact first+last match against the household head (households row)
     *   3. First-name-only fallback against household_members
     *   4. First-name-only fallback against the household head
     *
     * If a member record matched but some personal fields are empty, they are
     * filled in from the household head record (address fields always come from
     * the head: zone, address, contact_number).
     *
     * @param array $user      User row (needs first_name, last_name at minimum)
     * @param array $household Household head row (HouseholdModel::find result)
     * @param array $members   HouseholdMemberModel rows for the household
     * @return array|null Matched census record with merged personal+household fields,
     *                    or null if nothing matched and household is empty.
     */
    protected static function matchCensusRecord(array $user, ?array $household, array $members): ?array
    {
        if (empty($household) && empty($members)) {
            return null;
        }

        $userFirst = strtoupper(trim($user['first_name'] ?? ''));
        $userLast  = strtoupper(trim($user['last_name']  ?? ''));
        $userFull  = $userFirst . ' ' . $userLast;
        $userFullR = $userLast  . ' ' . $userFirst;

        $memberRecord = null;

        if (! empty($members)) {
            // Pass 1 — exact first+last match against members
            foreach ($members as $m) {
                $mFirst = strtoupper(trim($m['first_name'] ?? ''));
                $mLast  = strtoupper(trim($m['last_name']  ?? ''));
                $mFull  = $mFirst . ' ' . $mLast;
                if (
                    $userFull === $mFull || $userFullR === $mFull ||
                    $userFull === ($mLast . ' ' . $mFirst)
                ) {
                    $memberRecord = $m;
                    break;
                }
            }
        }

        // Pass 2 — exact first+last match against the household head
        if (! $memberRecord && ! empty($household)) {
            $hFirst = strtoupper(trim($household['first_name'] ?? ''));
            $hLast  = strtoupper(trim($household['last_name']  ?? ''));
            $hFull  = $hFirst . ' ' . $hLast;
            if (
                $userFull === $hFull || $userFullR === $hFull ||
                $userFull === ($hLast . ' ' . $hFirst)
            ) {
                $memberRecord = $household;
            }
        }

        // Pass 3 — first-name-only fallback against members
        if (! $memberRecord && $userFirst !== '' && ! empty($members)) {
            foreach ($members as $m) {
                if (strtoupper(trim($m['first_name'] ?? '')) === $userFirst) {
                    $memberRecord = $m;
                    break;
                }
            }
        }

        // Pass 4 — first-name-only fallback against head
        if (! $memberRecord && $userFirst !== '' && ! empty($household)) {
            if (strtoupper(trim($household['first_name'] ?? '')) === $userFirst) {
                $memberRecord = $household;
            }
        }

        // Still no match: fall back to household head so we at least have zone/address
        if (! $memberRecord && ! empty($household)) {
            $memberRecord = $household;
        }

        if (! $memberRecord) {
            return null;
        }

        // Merge: address/zone/contact always come from the household head.
        // Personal fields on a member record that are empty fall back to head.
        if (! empty($household)) {
            $addrFields = [
                'zone',
                'address',
                'contact_number',
                'years_of_residency',
                'house_ownership',
                'is_4ps',
                'is_senior_citizen',
                'is_solo_parent',
                'is_indigenous',
                'registered_voter',
                'num_families',
                'water_source_level',
                'water_safety_managed',
                'sanitation_basic',
                'sanitation_managed',
                'census_year',
                'shared_address_group',
                'family_number'
            ];
            foreach ($addrFields as $f) {
                if (isset($household[$f])) {
                    $memberRecord[$f] = $household[$f];
                }
            }

            if ($memberRecord !== $household) {
                // Only inherit fields that actually exist in the household_members
                // table.  Fields like civil_status / place_of_birth / nationality /
                // religion are NOT stored on member rows and MUST NOT be blindly
                // copied from the head (that's why a 22-yr-old "Child" was
                // incorrectly showing as "Married").
                $fillFields = [
                    'date_of_birth',
                    'occupation',
                    'monthly_income',
                    'educational_attainment',
                    'philhealth_no',
                    'gender',
                    'is_pwd',
                    'pwd_type',
                    'suffix',
                    'middle_name',
                ];
                foreach ($fillFields as $f) {
                    if (empty($memberRecord[$f]) && ! empty($household[$f])) {
                        $memberRecord[$f] = $household[$f];
                    }
                }

                // ── Derive a sensible civil_status for the member ────────────
                if (empty($memberRecord['civil_status'])) {
                    $rel = strtolower(trim($memberRecord['relationship'] ?? ''));
                    $age = null;
                    if (! empty($memberRecord['date_of_birth'])) {
                        try {
                            $dob = new \DateTime($memberRecord['date_of_birth']);
                            $now = new \DateTime();
                            $age = $now->diff($dob)->y;
                        } catch (\Throwable $e) {
                            $age = null;
                        }
                    }
                    if ($rel === 'spouse' || $rel === 'wife' || $rel === 'husband') {
                        $memberRecord['civil_status'] = $household['civil_status'] ?? 'Married';
                    } elseif (
                        $rel === 'child' || $rel === 'son' || $rel === 'daughter' ||
                        $rel === 'sibling' || $rel === 'brother' || $rel === 'sister' ||
                        $rel === 'grandchild' || $rel === 'nephew' || $rel === 'niece' ||
                        ($age !== null && $age < 25)
                    ) {
                        $memberRecord['civil_status'] = 'Single';
                    } else {
                        $memberRecord['civil_status'] = 'Single';
                    }
                }
            }
        }

        return $memberRecord;
    }

    /**
     * Variant of matchCensusRecord that accepts a single full-name string
     * (e.g. "MA. JOSEFA BATOY" as stored in clearance_requests.for_member).
     *
     * Attempts a best-effort split into first/last tokens, then runs the
     * same multi-pass matching against household head + members.
     *
     * Address fields (zone, address, contact_number, …) are always pulled
     * from the household head; personal fields prefer the matched row.
     *
     * @param string $fullName  Full name as stored in the request, e.g. "Juan Dela Cruz"
     * @param array|null $household
     * @param array $members
     * @return array|null
     */
    protected static function matchCensusByName(string $fullName, ?array $household, array $members): ?array
    {
        $name = trim($fullName);
        if ($name === '' || (empty($household) && empty($members))) {
            return null;
        }

        $norm = static fn($s) => strtoupper(preg_replace('/\s+/', ' ', trim($s ?? '')));
        $target = $norm($name);

        // Quick helpers: person = array with first_name/last_name (and optional middle_name/suffix)
        $personFull = static fn($p) => $norm(trim(($p['first_name'] ?? '') . ' ' . ($p['middle_name'] ?? '') . ' ' . ($p['last_name'] ?? '') . ' ' . ($p['suffix'] ?? '')));
        $personFL   = static fn($p) => $norm(trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')));
        $personLF   = static fn($p) => $norm(trim(($p['last_name'] ?? '') . ' ' . ($p['first_name'] ?? '')));

        $candidates = [];
        if (! empty($members))   $candidates = array_merge($candidates, $members);
        if (! empty($household)) $candidates[] = $household;

        $matched = null;

        // 1) exact full-name match (first + middle + last + suffix)
        foreach ($candidates as $c) {
            if ($personFull($c) === $target || $personFL($c) === $target || $personLF($c) === $target) {
                $matched = $c;
                break;
            }
        }

        // 2) substring containment (handles "MA. JOSEFA BATOY" vs census "JOSEFA BATOY")
        if (! $matched) {
            foreach ($candidates as $c) {
                $fl = $personFL($c);
                $full = $personFull($c);
                if (($fl !== '' && (strpos($target, $fl) !== false || strpos($fl, $target) !== false)) ||
                    ($full !== '' && (strpos($target, $full) !== false || strpos($full, $target) !== false))
                ) {
                    $matched = $c;
                    break;
                }
            }
        }

        // 3) split target into tokens and match first + last token against rows
        if (! $matched) {
            $tokens = preg_split('/\s+/', $target, -1, PREG_SPLIT_NO_EMPTY);
            if (count($tokens) >= 2) {
                $firstTok = $tokens[0];
                $lastTok  = $tokens[count($tokens) - 1];
                foreach ($candidates as $c) {
                    $cf = strtoupper(trim($c['first_name'] ?? ''));
                    $cl = strtoupper(trim($c['last_name']  ?? ''));
                    if (($cf === $firstTok && $cl === $lastTok) ||
                        ($cf === $lastTok  && $cl === $firstTok)
                    ) {
                        $matched = $c;
                        break;
                    }
                }
            }
        }

        // 4) first-token-only match (loosest)
        if (! $matched) {
            $tokens = preg_split('/\s+/', $target, -1, PREG_SPLIT_NO_EMPTY);
            if (! empty($tokens)) {
                $firstTok = $tokens[0];
                foreach ($candidates as $c) {
                    if (strtoupper(trim($c['first_name'] ?? '')) === $firstTok) {
                        $matched = $c;
                        break;
                    }
                }
            }
        }

        if (! $matched && ! empty($household)) {
            $matched = $household;
        }
        if (! $matched) {
            return null;
        }

        // Merge address fields from head, fill sparse personal fields from head
        if (! empty($household)) {
            $addrFields = [
                'zone',
                'address',
                'contact_number',
                'years_of_residency',
                'house_ownership',
                'is_4ps',
                'is_senior_citizen',
                'is_solo_parent',
                'is_indigenous',
                'registered_voter',
                'num_families',
                'water_source_level',
                'water_safety_managed',
                'sanitation_basic',
                'sanitation_managed',
                'census_year',
                'shared_address_group',
                'family_number'
            ];
            foreach ($addrFields as $f) {
                if (isset($household[$f])) $matched[$f] = $household[$f];
            }

            if ($matched !== $household) {
                // Only inherit fields that exist in the household_members table.
                // Never copy civil_status / religion / nationality / place_of_birth
                // from the head — those don't exist on member rows and would be
                // wrong (e.g. a "Child" should never inherit "Married").
                $fillFields = [
                    'date_of_birth',
                    'occupation',
                    'monthly_income',
                    'educational_attainment',
                    'philhealth_no',
                    'gender',
                    'is_pwd',
                    'pwd_type',
                    'suffix',
                    'middle_name'
                ];
                foreach ($fillFields as $f) {
                    if (empty($matched[$f]) && ! empty($household[$f])) {
                        $matched[$f] = $household[$f];
                    }
                }

                // ── Derive a sensible civil_status for the member ────────────
                if (empty($matched['civil_status'])) {
                    $rel = strtolower(trim($matched['relationship'] ?? ''));
                    $age = null;
                    if (! empty($matched['date_of_birth'])) {
                        try {
                            $dob = new \DateTime($matched['date_of_birth']);
                            $now = new \DateTime();
                            $age = $now->diff($dob)->y;
                        } catch (\Throwable $e) {
                            $age = null;
                        }
                    }
                    if ($rel === 'spouse' || $rel === 'wife' || $rel === 'husband') {
                        $matched['civil_status'] = $household['civil_status'] ?? 'Married';
                    } elseif (
                        $rel === 'child' || $rel === 'son' || $rel === 'daughter' ||
                        $rel === 'sibling' || $rel === 'brother' || $rel === 'sister' ||
                        $rel === 'grandchild' || $rel === 'nephew' || $rel === 'niece' ||
                        ($age !== null && $age < 25)
                    ) {
                        $matched['civil_status'] = 'Single';
                    } else {
                        $matched['civil_status'] = 'Single';
                    }
                }
            }
        }

        return $matched;
    }
}
