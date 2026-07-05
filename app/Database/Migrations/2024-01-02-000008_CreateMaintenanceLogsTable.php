<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaintenanceLogsTable extends Migration
{
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `maintenance_logs` (
                `id`             INT(11) UNSIGNED  NOT NULL AUTO_INCREMENT,
                `asset_id`       INT(11) UNSIGNED  NOT NULL,
                `work_order_id`  INT(11) UNSIGNED  NULL,
                `user_id`        INT(11) UNSIGNED  NULL,
                `action`         ENUM(
                                     'ditambah','diubah','dipinjam','dikembalikan',
                                     'perbaikan_mulai','perbaikan_selesai',
                                     'inspeksi','dihapus','dipindah'
                                 ) NOT NULL,
                `description`    TEXT              NULL,
                `old_value`      TEXT              NULL,
                `new_value`      TEXT              NULL,
                `cost`           DECIMAL(15,2)     NULL,
                `created_at`     DATETIME          NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_mlog_asset`       (`asset_id`),
                INDEX `idx_mlog_work_order`  (`work_order_id`),
                INDEX `idx_mlog_user`        (`user_id`),
                INDEX `idx_mlog_action`      (`action`),
                INDEX `idx_mlog_created_at`  (`created_at`),
                INDEX `idx_mlog_asset_date`  (`asset_id`, `created_at`),
                CONSTRAINT `fk_mlog_asset`
                    FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`)
                    ON UPDATE CASCADE ON DELETE CASCADE,
                CONSTRAINT `fk_mlog_work_order`
                    FOREIGN KEY (`work_order_id`) REFERENCES `work_orders`(`id`)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT `fk_mlog_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
                    ON UPDATE CASCADE ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS `maintenance_logs`');
    }
}
