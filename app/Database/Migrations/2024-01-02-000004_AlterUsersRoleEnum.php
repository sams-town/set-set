<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersRoleEnum extends Migration
{
    public function up(): void
    {
        // Perluas ENUM role dari (admin,user) menjadi (admin,technician,user)
        $this->db->query("
            ALTER TABLE `users`
            MODIFY COLUMN `role`
            ENUM('admin','technician','user') NOT NULL DEFAULT 'user'
        ");
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE `users`
            MODIFY COLUMN `role`
            ENUM('admin','user') NOT NULL DEFAULT 'user'
        ");
    }
}
