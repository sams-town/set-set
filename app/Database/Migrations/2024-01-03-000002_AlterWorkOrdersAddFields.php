<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tambah field baru ke work_orders sesuai konsep GA Work Order Module:
 *
 * Alur: User Input Keluhan + Foto → Assessment Teknisi → Pengerjaan + Foto Before
 *       → Testing + Foto After → Close WO
 *
 * Field baru:
 *  - reporter_name    : nama pelapor (user input bebas atau dari session)
 *  - department_id    : departemen pelapor
 *  - location_id      : lokasi kejadian
 *  - damage_type      : jenis kerusakan (mekanik, elektrik, software, dll)
 *  - category_wo      : kategori WO (hardware, jaringan, furniture, dll)
 *  - photo_complaint  : foto dari user saat melapor
 *  - photo_before     : foto kondisi sebelum perbaikan
 *  - photo_after      : foto kondisi sesudah perbaikan/testing
 *  - material_used    : material/spare part yang digunakan (text)
 *  - material_cost    : biaya material
 *  - labor_cost       : biaya jasa/tenaga
 *  - sla_hours        : target SLA dalam jam (dari prioritas)
 *  - response_time    : waktu respon aktual dalam menit
 *  - repair_time      : waktu perbaikan aktual dalam menit
 *  - target_date      : target selesai
 *  - assessment_notes : catatan assessment teknisi
 */
class AlterWorkOrdersAddFields extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('work_orders');

        $new = [];

        if (! in_array('reporter_name', $fields)) {
            $new['reporter_name'] = ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'requested_by'];
        }
        if (! in_array('department_id', $fields)) {
            $new['department_id'] = ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'reporter_name'];
        }
        if (! in_array('location_id', $fields)) {
            $new['location_id'] = ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'department_id'];
        }
        if (! in_array('damage_type', $fields)) {
            $new['damage_type'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'type'];
        }
        if (! in_array('category_wo', $fields)) {
            $new['category_wo'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'damage_type'];
        }
        if (! in_array('photo_complaint', $fields)) {
            $new['photo_complaint'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'problem_desc'];
        }
        if (! in_array('assessment_notes', $fields)) {
            $new['assessment_notes'] = ['type' => 'TEXT', 'null' => true, 'after' => 'photo_complaint'];
        }
        if (! in_array('photo_before', $fields)) {
            $new['photo_before'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'assessment_notes'];
        }
        if (! in_array('photo_after', $fields)) {
            $new['photo_after'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'photo_before'];
        }
        if (! in_array('material_used', $fields)) {
            $new['material_used'] = ['type' => 'TEXT', 'null' => true, 'after' => 'action_taken'];
        }
        if (! in_array('material_cost', $fields)) {
            $new['material_cost'] = ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true, 'after' => 'material_used'];
        }
        if (! in_array('labor_cost', $fields)) {
            $new['labor_cost'] = ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true, 'after' => 'material_cost'];
        }
        if (! in_array('sla_hours', $fields)) {
            $new['sla_hours'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'Target SLA dalam jam', 'after' => 'labor_cost'];
        }
        if (! in_array('response_time', $fields)) {
            $new['response_time'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'Waktu respon aktual dalam menit', 'after' => 'sla_hours'];
        }
        if (! in_array('repair_time', $fields)) {
            $new['repair_time'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'Waktu perbaikan aktual dalam menit', 'after' => 'response_time'];
        }
        if (! in_array('target_date', $fields)) {
            $new['target_date'] = ['type' => 'DATE', 'null' => true, 'after' => 'scheduled_date'];
        }

        if (! empty($new)) {
            $this->forge->addColumn('work_orders', $new);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('work_orders', [
            'reporter_name', 'department_id', 'location_id',
            'damage_type', 'category_wo',
            'photo_complaint', 'assessment_notes', 'photo_before', 'photo_after',
            'material_used', 'material_cost', 'labor_cost',
            'sla_hours', 'response_time', 'repair_time', 'target_date',
        ]);
    }
}
