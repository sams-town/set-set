<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAddPhone extends Migration
{
    public function up(): void
    {
        // Tambah kolom phone dan department_id ke tabel users yang sudah ada
        $fields = [
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'role',
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'phone',
            ],
            'last_login' => [
                'type' => 'DATETIME',
                'null' => true,
                'after'=> 'department_id',
            ],
        ];

        // Cek dulu apakah kolom sudah ada
        $db = \Config\Database::connect();
        $columns = $db->getFieldNames('users');

        if (! in_array('phone', $columns)) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['phone', 'department_id', 'last_login']);
    }
}
