<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DepartmentSeeder
 * Isi tabel departments untuk db_asset_management
 * Jalankan: php spark db:seed DepartmentSeeder
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $rows = [
            ['name' => 'Teknologi Informasi', 'code' => 'IT',  'manager' => 'Admin IT',    'is_active' => 1],
            ['name' => 'Keuangan',             'code' => 'FIN', 'manager' => 'Admin Keu',   'is_active' => 1],
            ['name' => 'Operasional',          'code' => 'OPS', 'manager' => 'Admin Ops',   'is_active' => 1],
            ['name' => 'Sumber Daya Manusia',  'code' => 'HR',  'manager' => 'Admin HR',    'is_active' => 1],
            ['name' => 'Umum & Logistik',      'code' => 'LOG', 'manager' => 'Admin Log',   'is_active' => 1],
            ['name' => 'Manajemen',            'code' => 'MGT', 'manager' => 'Direktur',    'is_active' => 1],
        ];

        foreach ($rows as &$r) {
            $r['created_at'] = $now;
            $r['updated_at'] = $now;
        }

        $this->db->table('departments')->insertBatch($rows);
        echo "DepartmentSeeder: " . count($rows) . " departemen berhasil dibuat.\n";
    }
}
