<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAddItAtemRoles extends Migration
{
    public function up(): void
    {
        // Tambah nilai 'it' dan 'atem' ke ENUM role di tabel users
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','technician','it','atem','user','pembelian') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','technician','user','pembelian') NOT NULL DEFAULT 'user'");
    }
}
