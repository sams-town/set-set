<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterWorkOrdersTypeVarchar extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('work_orders', [
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => 'corrective',
            ]
        ]);
    }

    public function down(): void
    {
        // Revert back to ENUM
        $this->db->query("ALTER TABLE `work_orders` MODIFY `type` ENUM('preventive','corrective','inspection') NOT NULL DEFAULT 'corrective'");
    }
}
