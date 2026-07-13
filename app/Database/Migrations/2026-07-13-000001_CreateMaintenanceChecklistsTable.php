<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel maintenance_checklists — Checklist pemeliharaan aset
 */
class CreateMaintenanceChecklistsTable extends Migration
{
    public function up(): void
    {
        // Tabel checklist template (opsional, bisa diisi per kategori aset)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'comment'    => 'Nama checklist template (misal: Checklist Infusion Pump)',
            ],
            'asset_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Kategori aset yang cocok dengan template ini',
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
        $this->forge->addKey('id', true);
        $this->forge->addKey('asset_category');
        $this->forge->createTable('maintenance_checklist_templates');
        
        // Tabel item checklist
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'checklist_template_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK ke checklist template',
            ],
            'item_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Teks item checklist (misal: Body / Chasing)',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Urutan item',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('checklist_template_id');
        $this->forge->addForeignKey('checklist_template_id', 'maintenance_checklist_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('maintenance_checklist_items');
        
        // Tabel instance checklist yang diisi oleh teknisi
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
                'comment'    => 'FK ke assets',
            ],
            'checklist_template_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK ke checklist template yang dipakai',
            ],
            'work_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK ke work_order (opsional)',
            ],
            'technician_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Teknisi yang mengisi checklist',
            ],
            'checklist_date' => [
                'type'    => 'DATE',
                'comment' => 'Tanggal checklist dilakukan',
            ],
            'notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Catatan dan kesimpulan',
            ],
            'technician_signature' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Tanda tangan teknisi (opsional, base64)',
            ],
            'supervisor_signature' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Tanda tangan supervisor (opsional, base64)',
            ],
            'user_signature' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Tanda tangan pengguna alat (opsional, base64)',
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
        $this->forge->addKey('id', true);
        $this->forge->addKey('asset_id');
        $this->forge->addKey('checklist_date');
        $this->forge->addForeignKey('asset_id', 'assets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('checklist_template_id', 'maintenance_checklist_templates', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('work_order_id', 'work_orders', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('technician_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('maintenance_checklist_instances');
        
        // Tabel jawaban item checklist instance
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'checklist_instance_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'FK ke checklist instance',
            ],
            'checklist_item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'FK ke checklist item',
            ],
            'item_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Salinan teks item checklist pada saat itu',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['baik', 'tidak', 'n/a'],
                'default'    => 'n/a',
                'comment'    => 'Status item checklist (baik/tidak/n/a)',
            ],
            'notes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Catatan untuk item ini',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('checklist_instance_id');
        $this->forge->addForeignKey('checklist_instance_id', 'maintenance_checklist_instances', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('checklist_item_id', 'maintenance_checklist_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('maintenance_checklist_answers');
    }

    public function down(): void
    {
        $this->forge->dropTable('maintenance_checklist_answers');
        $this->forge->dropTable('maintenance_checklist_instances');
        $this->forge->dropTable('maintenance_checklist_items');
        $this->forge->dropTable('maintenance_checklist_templates');
    }
}
