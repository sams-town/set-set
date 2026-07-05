<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * VendorSeeder
 * Isi tabel vendors untuk db_asset_management
 * Jalankan: php spark db:seed VendorSeeder
 */
class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $rows = [
            [
                'name'     => 'PT. Sumber Teknologi Indonesia',
                'code'     => 'STI',
                'contact'  => 'Budi Santoso',
                'phone'    => '021-55512345',
                'email'    => 'info@sti.co.id',
                'category' => 'supplier',
                'is_active'=> 1,
            ],
            [
                'name'     => 'CV. Mitra Servis Prima',
                'code'     => 'MSP',
                'contact'  => 'Dewi Rahayu',
                'phone'    => '021-77723456',
                'email'    => 'service@msp.co.id',
                'category' => 'service',
                'is_active'=> 1,
            ],
            [
                'name'     => 'PT. Global Komputer Nusantara',
                'code'     => 'GKN',
                'contact'  => 'Hendra Wijaya',
                'phone'    => '022-99934567',
                'email'    => 'sales@gkn.co.id',
                'category' => 'both',
                'is_active'=> 1,
            ],
            [
                'name'     => 'UD. Berkah Elektronik',
                'code'     => 'UBE',
                'contact'  => 'Siti Aminah',
                'phone'    => '031-11145678',
                'email'    => 'order@ube.co.id',
                'category' => 'supplier',
                'is_active'=> 1,
            ],
        ];

        foreach ($rows as &$r) {
            $r['created_at'] = $now;
            $r['updated_at'] = $now;
        }

        $this->db->table('vendors')->insertBatch($rows);
        echo "VendorSeeder: " . count($rows) . " vendor berhasil dibuat.\n";
    }
}
