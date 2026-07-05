<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'asset_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'ENUM',
                'constraint' => ['ditambah', 'diubah', 'dipinjam', 'dikembalikan', 'dihapus', 'diperbaiki'],
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'old_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'new_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('asset_id', 'assets', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('asset_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('asset_logs');
    }
}
