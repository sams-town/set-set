<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPmSchedulesAddType extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('pm_schedules', [
            'schedule_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => 'pm',
                'after'      => 'asset_id',
            ]
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('pm_schedules', 'schedule_type');
    }
}
