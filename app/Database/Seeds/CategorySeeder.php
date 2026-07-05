<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama agar tidak duplicate
        $this->db->table('categories')->emptyTable();

        $categories = [
            ['name' => 'Komputer & Laptop',          'code' => 'KOMP', 'description' => 'PC, Laptop, Notebook, Workstation'],
            ['name' => 'Printer & Scanner',           'code' => 'PRNT', 'description' => 'Printer, Scanner, Fotokopi'],
            ['name' => 'Jaringan & Telekomunikasi',   'code' => 'NETW', 'description' => 'Switch, Router, Access Point, Telepon'],
            ['name' => 'Perabot Kantor',              'code' => 'PRBT', 'description' => 'Meja, Kursi, Lemari, Rak'],
            ['name' => 'Proyektor & AV',              'code' => 'PROY', 'description' => 'Proyektor, Layar, Speaker, TV'],
            ['name' => 'Kendaraan Dinas',             'code' => 'KEND', 'description' => 'Mobil, Motor dinas'],
            ['name' => 'Elektronik & Listrik',        'code' => 'ELEK', 'description' => 'AC, Kulkas, Dispenser, UPS'],
            ['name' => 'Alat Medis & Kesehatan',      'code' => 'MED',  'description' => 'Peralatan medis, alat kesehatan RS'],
            ['name' => 'Alat Ukur & Laboratorium',    'code' => 'LAB',  'description' => 'Multimeter, Kaliper, Alat Lab'],
            ['name' => 'Lainnya',                     'code' => 'LNY',  'description' => 'Kategori umum / tidak terklasifikasi'],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($categories as &$cat) {
            $cat['created_at'] = $now;
            $cat['updated_at'] = $now;
        }

        $this->db->table('categories')->insertBatch($categories);

        echo "CategorySeeder: " . count($categories) . " kategori berhasil dibuat.\n";
        foreach ($categories as $cat) {
            echo "  - [{$cat['code']}] {$cat['name']}\n";
        }
    }
}
