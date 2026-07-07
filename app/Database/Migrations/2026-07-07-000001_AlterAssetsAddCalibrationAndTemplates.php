<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAssetsAddCalibrationAndTemplates extends Migration
{
    public function up(): void
    {
        // 1. Create asset_templates table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'brand' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'model' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('asset_templates', true);

        // 2. Modify assets table: alter status column, add calibration columns
        // First change ENUM status to VARCHAR(50)
        $this->db->query("ALTER TABLE `assets` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'Aktif'");

        // Then add calibration columns if they don't exist
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('assets');

        $newFields = [];
        if (! in_array('requires_calibration', $fields)) {
            $newFields['requires_calibration'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'pm_interval_days',
            ];
        }
        if (! in_array('last_calibration_date', $fields)) {
            $newFields['last_calibration_date'] = [
                'type' => 'DATE',
                'null' => true,
                'after'=> 'requires_calibration',
            ];
        }
        if (! in_array('next_calibration_date', $fields)) {
            $newFields['next_calibration_date'] = [
                'type' => 'DATE',
                'null' => true,
                'after'=> 'last_calibration_date',
            ];
        }
        if (! in_array('calibration_certificate', $fields)) {
            $newFields['calibration_certificate'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'next_calibration_date',
            ];
        }
        if (! in_array('calibration_vendor', $fields)) {
            $newFields['calibration_vendor'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'calibration_certificate',
            ];
        }

        if (! empty($newFields)) {
            $this->forge->addColumn('assets', $newFields);
        }

        // Migrate existing statuses to 'Aktif' for safety
        $this->db->query("UPDATE `assets` SET `status` = 'Aktif' WHERE `status` = 'tersedia'");
        $this->db->query("UPDATE `assets` SET `status` = 'Corrective Maintenance' WHERE `status` = 'dalam_perbaikan' OR `status` = 'diperbaiki'");
        $this->db->query("UPDATE `assets` SET `status` = 'Penghapusan' WHERE `status` = 'dihapus'");
    }

    public function down(): void
    {
        // 1. Drop asset_templates table
        $this->forge->dropTable('asset_templates', true);

        // 2. Revert assets table modifications
        $this->forge->dropColumn('assets', [
            'requires_calibration',
            'last_calibration_date',
            'next_calibration_date',
            'calibration_certificate',
            'calibration_vendor',
        ]);

        // Revert status to ENUM
        $this->db->query("UPDATE `assets` SET `status` = 'tersedia' WHERE `status` = 'Aktif' OR `status` = 'Standby' OR `status` = 'Terpasang' OR `status` = 'Siap Operasi'");
        $this->db->query("UPDATE `assets` SET `status` = 'dalam_perbaikan' WHERE `status` = 'Corrective Maintenance' OR `status` = 'Jadwal PM' OR `status` = 'Kalibrasi'");
        $this->db->query("UPDATE `assets` SET `status` = 'dihapus' WHERE `status` = 'Penghapusan'");
        
        $this->db->query("
            ALTER TABLE `assets`
            MODIFY COLUMN `status`
            ENUM('tersedia','dipinjam','dalam_perbaikan','diperbaiki','dihapus')
            NOT NULL DEFAULT 'tersedia'
        ");
    }
}
