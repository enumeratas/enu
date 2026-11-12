<?php

if (! function_exists('session_role')) {
    function session_role(): string
    {
        return strtolower(trim((string) (session()->get('role') ?? '')));
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return session_role() === 'admin';
    }
}

if (! function_exists('can_role')) {
    function can_role(string ...$roles): bool
    {
        $current = session_role();
        if ($current === 'admin') {
            return true;
        }

        $allowed = array_map(static fn(string $role): string => strtolower(trim($role)), $roles);

        return $current !== '' && in_array($current, $allowed, true);
    }
}

if (! function_exists('route_prefix')) {
    function route_prefix(): string
    {
        $role = session_role();

        return $role !== '' ? $role : 'resident';
    }
}

if (! function_exists('other_relationship_options')) {
    function other_relationship_options(): array
    {
        return [
            'father'           => 'Father',
            'mother'           => 'Mother',
            'sibling'          => 'Sibling',
            'grandparent'      => 'Grandparent',
            'grandchild'       => 'Grandchild',
            'aunt'             => 'Aunt',
            'uncle'            => 'Uncle',
            'father_in_law'    => 'Father-in-Law',
            'mother_in_law'    => 'Mother-in-Law',
            'son_in_law'       => 'Son-in-Law',
            'daughter_in_law'  => 'Daughter-in-Law',
            'cousin'           => 'Cousin',
            'niece'            => 'Niece',
            'nephew'           => 'Nephew',
            'other_relative'   => 'Other Relative',
            'non_relative'     => 'Non-relative',
        ];
    }
}

if (! function_exists('other_relationship_select')) {
    function other_relationship_select(string $class = 'pf-ctrl'): string
    {
        $html = '<select class="' . esc($class, 'attr') . '" name="other_relationship[]">';
        $html .= '<option value="">— Select —</option>';
        foreach (other_relationship_options() as $value => $label) {
            $html .= '<option value="' . esc($value, 'attr') . '">' . esc($label) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }
}

if (! function_exists('education_attainment_options')) {
    function education_attainment_options(): array
    {
        return [
            'No Formal Education',
            'Elementary Level',
            'Elementary Graduate',
            'High School Level',
            'High School Graduate',
            'College Level',
            'College Graduate',
            'Vocational / Tech-Voc',
            'Post Graduate',
        ];
    }
}

if (! function_exists('education_attainment_select')) {
    function education_attainment_select(string $name, string $class = 'pf-ctrl'): string
    {
        $html = '<select class="' . esc($class, 'attr') . '" name="' . esc($name, 'attr') . '">';
        $html .= '<option value="">— Select —</option>';
        foreach (education_attainment_options() as $option) {
            $html .= '<option>' . esc($option) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }
}

if (! function_exists('staff_view_folder')) {
    function staff_view_folder(?string $role = null): string
    {
        $role = strtolower(trim((string) ($role ?? session_role())));

        return $role === 'admin' ? 'secretary' : $role;
    }
}
