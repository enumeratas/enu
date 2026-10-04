<?php

namespace Config;

use App\Models\BarangaySettingsModel;

class ClearanceDocuments
{
    public const TYPES = [
        'Barangay Clearance',
        'Certificate of Residency',
        'Certificate of Indigency',
        'Certificate of Good Moral',
        'First Time Job Seekers',
        'Solo Parent Certificate',
        'Business Permit Clearance',
        'Medical Assistance',
        'PhilHealth / SSS / GSIS',
        'Scholarship Application',
        'Employment / Job Application',
        'Other Document',
    ];

    /** Documents a minor does not need and cannot request. */
    public const ADULT_ONLY = [
        'First Time Job Seekers',
        'Solo Parent Certificate',
        'Business Permit Clearance',
        'Employment / Job Application',
    ];

    /**
     * Fee setting key, settings label, default display, and sort order.
     *
     * @var array<string, array{key:string,label:string,default:string,sort:int}>
     */
    public const FEES = [
        'Barangay Clearance'            => ['key' => 'clearance_fee',         'label' => 'Barangay Clearance',            'default' => '₱50.00', 'sort' => 20],
        'Certificate of Residency'      => ['key' => 'residency_fee',         'label' => 'Certificate of Residency',      'default' => '₱30.00', 'sort' => 21],
        'Certificate of Indigency'      => ['key' => 'indigency_fee',         'label' => 'Certificate of Indigency',      'default' => 'Free',   'sort' => 22],
        'Certificate of Good Moral'     => ['key' => 'good_moral_fee',        'label' => 'Certificate of Good Moral',     'default' => 'Free',   'sort' => 23],
        'First Time Job Seekers'        => ['key' => 'job_seeker_fee',        'label' => 'First Time Job Seekers',        'default' => 'Free',   'sort' => 24],
        'Solo Parent Certificate'       => ['key' => 'solo_parent_fee',       'label' => 'Solo Parent Certificate',       'default' => 'Free',   'sort' => 25],
        'Business Permit Clearance'     => ['key' => 'business_permit_fee',   'label' => 'Business Permit Clearance',     'default' => '₱75.00', 'sort' => 26],
        'Medical Assistance'            => ['key' => 'medical_fee',           'label' => 'Medical Assistance',            'default' => 'Free',   'sort' => 27],
        'PhilHealth / SSS / GSIS'       => ['key' => 'philhealth_fee',        'label' => 'PhilHealth / SSS / GSIS',       'default' => 'Free',   'sort' => 28],
        'Scholarship Application'       => ['key' => 'scholarship_fee',       'label' => 'Scholarship Application',       'default' => 'Free',   'sort' => 29],
        'Employment / Job Application'  => ['key' => 'employment_fee',        'label' => 'Employment / Job Application',  'default' => 'Free',   'sort' => 30],
        'Other Document'                => ['key' => 'other_document_fee',    'label' => 'Other Document',                'default' => 'Free',   'sort' => 31],
    ];

    public static function isAdultOnly(string $documentType): bool
    {
        return in_array($documentType, self::ADULT_ONLY, true);
    }

    public static function displayFee(string $raw): string
    {
        $value = trim($raw);
        if ($value === '') {
            return 'Free';
        }

        $plain = str_replace(['₱', ',', ' '], '', $value);
        if (strcasecmp($plain, 'free') === 0) {
            return 'Free';
        }
        if (is_numeric($plain)) {
            $amount = (float) $plain;
            if ($amount <= 0) {
                return 'Free';
            }

            return '₱' . number_format($amount, 2);
        }

        return $value;
    }

    /** @return array<string, string> */
    public static function feeMap(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }

        $settings = (new BarangaySettingsModel())->getAll();
        $map = [];
        foreach (self::FEES as $type => $meta) {
            $map[$type] = self::displayFee((string) ($settings[$meta['key']] ?? $meta['default']));
        }
        $map['First Time Job Seeker'] = $map['First Time Job Seekers'];

        return $map;
    }

    public static function fee(string $documentType): string
    {
        $map = self::feeMap();

        return $map[$documentType] ?? '—';
    }

    /** @return array<string, string> */
    public static function feeDefaults(): array
    {
        $defaults = [];
        foreach (self::FEES as $meta) {
            $defaults[$meta['key']] = $meta['default'];
        }

        return $defaults;
    }
}
