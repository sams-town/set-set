<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAddPembelianRole extends Migration
{
    public function up(): void
    {
        $this->db->query("
            ALTER TABLE `users`
            MODIFY COLUMN `role`
            ENUM('admin','technician','user','pembelian')
            NOT NULL DEFAULT 'user'
        ");
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE `users`
            MODIFY COLUMN `role`
            ENUM('admin','technician','user')
            NOT NULL DEFAULT 'user'
        ");
    }
}
