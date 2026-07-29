<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterChecklistAddFollowUp extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('maintenance_checklist_instances', [
            'unit_ruangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'checklist_date',
                'comment'    => 'Unit/Ruangan tempat alat berada',
            ],
            'petugas_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'unit_ruangan',
                'comment'    => 'Nama petugas pemeriksa',
            ],
            'follow_up' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'notes',
                'comment' => 'Tindak lanjut (JSON array: tidak_ada_perbaikan, pemeliharaan_ringan, dll)',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('maintenance_checklist_instances', ['unit_ruangan', 'petugas_nama', 'follow_up']);
    }
}
