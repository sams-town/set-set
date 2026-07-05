<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAssetsForNewSchema extends Migration
{
    public function up(): void
    {
        $db      = \Config\Database::connect();
        $columns = $db->getFieldNames('assets');

        $newFields = [];

        // Kolom untuk skema baru (InventoryAssetModel)
        if (! in_array('category', $columns)) {
            $newFields['category'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => '',
                'after'      => 'name',
            ];
        }

        if (! in_array('department_id', $columns)) {
            $newFields['department_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'category',
            ];
        }

        if (! in_array('vendor_id', $columns)) {
            $newFields['vendor_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'department_id',
            ];
        }

        if (! in_array('warranty_expiry', $columns)) {
            $newFields['warranty_expiry'] = [
                'type' => 'DATE',
                'null' => true,
                'after'=> 'purchase_price',
            ];
        }

        if (! empty($newFields)) {
            $this->forge->addColumn('assets', $newFields);
        }

        // Ubah ENUM status assets agar support 'dalam_perbaikan'
        $this->db->query("
            ALTER TABLE `assets`
            MODIFY COLUMN `status`
            ENUM('tersedia','dipinjam','dalam_perbaikan','diperbaiki','dihapus')
            NOT NULL DEFAULT 'tersedia'
        ");
    }

    public function down(): void
    {
        $this->forge->dropColumn('assets', ['category','department_id','vendor_id','warranty_expiry']);
        $this->db->query("
            ALTER TABLE `assets`
            MODIFY COLUMN `status`
            ENUM('tersedia','dipinjam','diperbaiki','dihapus')
            NOT NULL DEFAULT 'tersedia'
        ");
    }
}
