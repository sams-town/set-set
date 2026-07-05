<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fix: category_id di tabel assets dibuat nullable
 * karena InventoryAssetController menggunakan kolom 'category' (VARCHAR)
 * bukan category_id (FK integer).
 *
 * Kedua kolom tetap ada:
 *   - category    : VARCHAR, dipakai oleh InventoryAssetController (string kategori)
 *   - category_id : INT FK, nullable (untuk backward compatibility)
 */
class FixCategoryIdNullable extends Migration
{
    public function up(): void
    {
        // Buat category_id nullable agar tidak wajib diisi
        $this->db->query("
            ALTER TABLE `assets`
            MODIFY COLUMN `category_id`
            INT(11) UNSIGNED NULL DEFAULT NULL
        ");
    }

    public function down(): void
    {
        // Kembalikan ke NOT NULL (hati-hati jika ada data null)
        $this->db->query("
            UPDATE `assets` SET `category_id` = 1 WHERE `category_id` IS NULL
        ");
        $this->db->query("
            ALTER TABLE `assets`
            MODIFY COLUMN `category_id`
            INT(11) UNSIGNED NOT NULL
        ");
    }
}
