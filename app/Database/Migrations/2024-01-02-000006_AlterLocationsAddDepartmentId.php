<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterLocationsAddDepartmentId extends Migration
{
    public function up(): void
    {
        $db      = \Config\Database::connect();
        $columns = $db->getFieldNames('locations');

        if (! in_array('department_id', $columns)) {
            $this->forge->addColumn('locations', [
                'department_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'description',
                ],
                'code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('locations', ['department_id', 'code']);
    }
}
