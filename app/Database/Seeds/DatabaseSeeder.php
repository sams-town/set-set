<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder — Master seeder, jalankan semua sekaligus:
 *   php spark db:seed DatabaseSeeder
 *
 * Urutan penting karena ada FK:
 *   1. departments
 *   2. locations  (FK → departments)
 *   3. users      (FK → departments)
 *   4. vendors
 *   5. categories (tidak FK ke tabel lain)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        echo "=== Menjalankan semua seeder ===\n\n";

        $this->call('DepartmentSeeder');
        $this->call('LocationSeeder');
        $this->call('UserSeeder');
        $this->call('VendorSeeder');
        $this->call('CategorySeeder');

        echo "\n✓ Semua seeder selesai.\n";
        echo "\nAkun default:\n";
        echo "  Admin : admin@siaset.com / admin123\n";
        echo "  User  : user@siaset.com  / user123\n";
    }
}
