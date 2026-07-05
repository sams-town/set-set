<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tambah 20 field sesuai Modul 2 - Inventory Asset
 *
 * Field baru:
 *  1. Nama asset Merk → sudah ada (brand)
 *  2. Departemen → sudah ada (department_id)
 *  3. Kode Aset → sudah ada (asset_code)
 *  4. Nama Aset → sudah ada (name)
 *  5. Type → tambah (type)
 *  6. Serial Number → sudah ada (serial_number)
 *  7. Harga → sudah ada (purchase_price)
 *  8. Perhitungan garis lurus (depreciation_years, depreciation_value)
 *  9. Vendor → sudah ada (vendor_id)
 *  10. Garansi → sudah ada (warranty_expiry)
 *  11. Status (2nd/baru) → tambah (status_condition)
 *  12. Lokasi → sudah ada (location_id)
 *  13. Quantity Item → tambah (quantity)
 *  14. Satuan → tambah (unit)
 *  15. Tanggal Perolehan → sudah ada (purchase_date)
 *  16. Umur Asset → otomatis dari purchase_date
 *  17. Kondisi → sudah ada (condition)
 *  18. Status → sudah ada (status)
 *  19. Interval Pemeliharaan Rutin (Hari) → tambah (pm_interval_days)
 *  20. Qrcode → sudah ada (qr_code)
 *  21. Foto → sudah ada (photo)
 */
class AlterAssetsAdd20Fields extends Migration
{
    public function up(): void
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('assets');

        $newFields = [];

        // Field 5: Type / Tipe Aset
        if (! in_array('type', $fields)) {
            $newFields['type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'category',
            ];
        }

        // Field 11: Status Kondisi (baru / 2nd / bekas)
        if (! in_array('status_condition', $fields)) {
            $newFields['status_condition'] = [
                'type'       => 'ENUM',
                'constraint' => ['baru', '2nd', 'bekas'],
                'default'    => 'baru',
                'after'      => 'purchase_price',
            ];
        }

        // Field 13: Quantity (jumlah item)
        if (! in_array('quantity', $fields)) {
            $newFields['quantity'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'after'      => 'status_condition',
            ];
        }

        // Field 14: Unit / Satuan
        if (! in_array('unit', $fields)) {
            $newFields['unit'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'unit',
                'after'      => 'quantity',
            ];
        }

        // Field 8a: Depreciation Years (masa pakai untuk depresiasi garis lurus)
        if (! in_array('depreciation_years', $fields)) {
            $newFields['depreciation_years'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Masa pakai aset dalam tahun (untuk perhitungan depresiasi)',
                'after'      => 'unit',
            ];
        }

        // Field 8b: Depreciation Value (nilai depresiasi per tahun - otomatis)
        if (! in_array('depreciation_value', $fields)) {
            $newFields['depreciation_value'] = [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
                'comment'    => 'Nilai depresiasi per tahun (purchase_price / depreciation_years)',
                'after'      => 'depreciation_years',
            ];
        }

        // Field 19: PM Interval Days (interval pemeliharaan preventif dalam hari)
        if (! in_array('pm_interval_days', $fields)) {
            $newFields['pm_interval_days'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Interval pemeliharaan rutin dalam hari (misal: 30, 90, 365)',
                'after'      => 'depreciation_value',
            ];
        }

        if (! empty($newFields)) {
            $this->forge->addColumn('assets', $newFields);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('assets', [
            'type',
            'status_condition',
            'quantity',
            'unit',
            'depreciation_years',
            'depreciation_value',
            'pm_interval_days',
        ]);
    }
}
