<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Procurement Module — 3 tabel:
 *
 * 1. procurement_requests — Permintaan aset dari user/departemen
 *    Alur status: draft → pending_atasan → pending_direktur → approved → rfq → po → received → registered
 *
 * 2. purchase_orders — PO yang dibuat setelah approval
 *
 * 3. po_items — Item detail per PO (bisa banyak item dalam 1 PO)
 */
class CreateProcurementTables extends Migration
{
    public function up(): void
    {
        // ── 1. procurement_requests ─────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'request_code'    => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 200],
            'category'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'description'     => ['type' => 'TEXT', 'null' => true],
            'quantity'        => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'unit'            => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unit'],
            'estimated_price' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true, 'comment' => 'Estimasi harga per unit'],
            'total_estimated' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true, 'comment' => 'Total estimasi = qty × estimated_price'],
            'urgency'         => ['type' => 'ENUM', 'constraint' => ['low','normal','high','critical'], 'default' => 'normal'],
            'status'          => ['type' => 'ENUM', 'constraint' => [
                'draft','pending_atasan','pending_direktur',
                'approved','rejected','rfq','po','received','registered','cancelled'
            ], 'default' => 'draft'],
            'department_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'requested_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_atasan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_direktur'=>['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'atasan_note'     => ['type' => 'TEXT', 'null' => true],
            'direktur_note'   => ['type' => 'TEXT', 'null' => true],
            'rejection_reason'=> ['type' => 'TEXT', 'null' => true],
            'approved_at'     => ['type' => 'DATETIME', 'null' => true],
            'target_date'     => ['type' => 'DATE', 'null' => true, 'comment' => 'Target tanggal kebutuhan'],
            'photo'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Foto referensi barang'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('department_id');
        $this->forge->createTable('procurement_requests');

        // ── 2. purchase_orders ──────────────────────────────────────
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'po_code'       => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'request_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'FK ke procurement_requests'],
            'vendor_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['draft','sent','confirmed','partial','completed','cancelled'], 'default' => 'draft'],
            'po_date'       => ['type' => 'DATE', 'null' => true],
            'expected_date' => ['type' => 'DATE', 'null' => true, 'comment' => 'Estimasi barang datang'],
            'received_date' => ['type' => 'DATE', 'null' => true, 'comment' => 'Tanggal barang diterima'],
            'subtotal'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'tax'           => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'shipping'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'notes'         => ['type' => 'TEXT', 'null' => true],
            'terms'         => ['type' => 'TEXT', 'null' => true, 'comment' => 'Syarat & ketentuan PO'],
            'created_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'lead_time_days'=> ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'Lead time aktual dalam hari'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('vendor_id');
        $this->forge->createTable('purchase_orders');

        // ── 3. po_items ─────────────────────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'po_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 200],
            'category'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'quantity'    => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'unit'        => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unit'],
            'unit_price'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_price' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'qty_received'=> ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'comment' => 'Jumlah yang sudah diterima'],
            'asset_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'Terisi setelah di-register ke inventory'],
            'notes'       => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('po_id');
        $this->forge->addForeignKey('po_id', 'purchase_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('po_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('po_items',            true);
        $this->forge->dropTable('purchase_orders',     true);
        $this->forge->dropTable('procurement_requests', true);
    }
}
