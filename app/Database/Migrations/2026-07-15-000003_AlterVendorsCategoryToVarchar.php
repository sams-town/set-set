<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterVendorsCategoryToVarchar extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('vendors', [
            'category' => [
                'name'       => 'category',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'default'    => 'supplier',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('vendors', [
            'category' => [
                'name'       => 'category',
                'type'       => 'ENUM',
                'constraint' => ['supplier', 'service', 'both'],
                'default'    => 'supplier',
            ],
        ]);
    }
}
