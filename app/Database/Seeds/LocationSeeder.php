<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * LocationSeeder
 * Isi tabel locations — FK ke departments
 * Jalankan SETELAH DepartmentSeeder.
 * Jalankan: php spark db:seed LocationSeeder
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Ambil ID departemen yang sudah ada
        $deptIt  = $this->db->table('departments')->where('code', 'IT')->get()->getRowArray();
        $deptOps = $this->db->table('departments')->where('code', 'OPS')->get()->getRowArray();
        $deptMgt = $this->db->table('departments')->where('code', 'MGT')->get()->getRowArray();

        $itId  = $deptIt['id']  ?? null;
        $opsId = $deptOps['id'] ?? null;
        $mgtId = $deptMgt['id'] ?? null;

        $rows = [
            ['name' => 'Ruang Server',      'building' => 'Gedung A', 'floor' => 'Lantai 1', 'department_id' => $itId,  'description' => 'Server room utama'],
            ['name' => 'Ruang IT',          'building' => 'Gedung A', 'floor' => 'Lantai 2', 'department_id' => $itId,  'description' => 'Divisi Teknologi Informasi'],
            ['name' => 'Ruang Rapat Utama', 'building' => 'Gedung B', 'floor' => 'Lantai 1', 'department_id' => $mgtId, 'description' => 'Meeting room kapasitas 20 orang'],
            ['name' => 'Ruang Direktur',    'building' => 'Gedung B', 'floor' => 'Lantai 3', 'department_id' => $mgtId, 'description' => 'Ruang pimpinan'],
            ['name' => 'Gudang Umum',       'building' => 'Gedung C', 'floor' => 'Lantai 1', 'department_id' => $opsId, 'description' => 'Penyimpanan aset & logistik'],
            ['name' => 'Lobby Utama',       'building' => 'Gedung A', 'floor' => 'Lantai 1', 'department_id' => null,   'description' => 'Area resepsionis'],
            ['name' => 'Ruang Training',    'building' => 'Gedung B', 'floor' => 'Lantai 2', 'department_id' => null,   'description' => 'Ruang pelatihan karyawan'],
        ];

        foreach ($rows as &$r) {
            $r['created_at'] = $now;
            $r['updated_at'] = $now;
        }

        $this->db->table('locations')->insertBatch($rows);
        echo "LocationSeeder: " . count($rows) . " lokasi berhasil dibuat.\n";
    }
}
