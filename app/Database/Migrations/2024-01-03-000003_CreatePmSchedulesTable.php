<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel pm_schedules — Preventive Maintenance Recurring Schedule
 *
 * Setiap record = 1 jadwal PM recurring untuk 1 aset.
 * System otomatis hitung next_due dari recurring + last_done.
 */
class CreatePmSchedulesTable extends Migration
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
                'comment'    => 'FK ke assets',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'comment'    => 'Judul schedule PM, misal: Ganti Oli Genset',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Instruksi PM detail',
            ],
            'recurring' => [
                'type'       => 'ENUM',
                'constraint' => ['daily', 'weekly', 'monthly', 'quarterly', 'biannual', 'yearly'],
                'default'    => 'monthly',
                'comment'    => 'Frekuensi: harian/mingguan/bulanan/3bln/6bln/tahunan',
            ],
            'interval_days' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Interval dalam hari (calculated from recurring)',
            ],
            'assigned_to' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Teknisi yang ditugaskan',
            ],
            'last_done' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Tanggal terakhir PM dilakukan',
            ],
            'next_due' => [
                'type'    => 'DATE',
                'null'    => false,
                'comment' => 'Tanggal PM selanjutnya (auto-calculated)',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1=aktif, 0=dinonaktifkan',
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['rendah', 'sedang', 'tinggi', 'kritis'],
                'default'    => 'sedang',
            ],
            'estimated_duration' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Estimasi durasi PM dalam menit',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('asset_id');
        $this->forge->addKey('next_due');
        $this->forge->addKey('is_active');

        $this->forge->addForeignKey('asset_id', 'assets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_to', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('pm_schedules');
    }

    public function down(): void
    {
        $this->forge->dropTable('pm_schedules');
    }
}
