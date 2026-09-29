<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeds the initial admin and secretary accounts.
 * Run once during first-time setup:
 *   php spark db:seed AdminSeeder
 *
 * Default credentials:
 *   username : admin            password : Admin@2026
 *   username : secretary_admin  password : ChangeMe@2024
 *
 * The admin creates official accounts from /admin/create-account.
 * Change both passwords immediately after first login.
 */
class AdminSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        if ($this->db->table('users')->where('username', 'admin')->countAllResults() === 0) {
            $email = 'admin@barangay.gov.ph';
            if ($this->db->table('users')->where('email', $email)->countAllResults() > 0) {
                $email = 'barangay.admin@barangay.gov.ph';
            }

            $this->db->table('users')->insert([
                'last_name'      => 'Admin',
                'first_name'     => 'Barangay',
                'middle_name'    => null,
                'email'          => $email,
                'username'       => 'admin',
                'password'       => password_hash('Admin@2026', PASSWORD_BCRYPT),
                'role'           => 'admin',
                'status'         => 'active',
                'email_verified' => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        if ($this->db->table('users')->where('username', 'secretary_admin')->countAllResults() === 0) {
            $this->db->table('users')->insert([
                'last_name'      => 'Secretary',
                'first_name'     => 'Barangay',
                'middle_name'    => null,
                'email'          => 'secretary@barangay.gov.ph',
                'username'       => 'secretary_admin',
                'password'       => password_hash('ChangeMe@2024', PASSWORD_BCRYPT),
                'role'           => 'secretary',
                'status'         => 'active',
                'email_verified' => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
