<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the admin role and the first admin account.
 *
 * Username: admin
 * Password: Admin@2026
 */
class AddAdminAccount extends Migration
{
    public function up()
    {
        $this->db->query(
            "ALTER TABLE `users`
             MODIFY `role` ENUM('admin','captain','secretary','sk','council','resident')
             NOT NULL DEFAULT 'resident'"
        );

        if ($this->db->table('users')->where('username', 'admin')->countAllResults() > 0) {
            return;
        }

        $email = 'admin@barangay.gov.ph';
        if ($this->db->table('users')->where('email', $email)->countAllResults() > 0) {
            $email = 'barangay.admin@barangay.gov.ph';
        }

        $now = date('Y-m-d H:i:s');
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

    public function down()
    {
        $this->db->table('users')->where('role', 'admin')->update(['role' => 'secretary']);

        $this->db->query(
            "ALTER TABLE `users`
             MODIFY `role` ENUM('captain','secretary','sk','council','resident')
             NOT NULL DEFAULT 'resident'"
        );
    }
}
