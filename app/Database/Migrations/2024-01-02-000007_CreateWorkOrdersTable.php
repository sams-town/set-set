<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkOrdersTable extends Migration
{
    public function up(): void
    {
        // Gunakan raw SQL karena CI4 Forge salah parse ENUM 'priority'/'status'
        // sebagai PRIMARY KEY jika pakai addPrimaryKey() setelah ENUM.
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `work_orders` (
                `id`             INT(11) UNSIGNED     NOT NULL AUTO_INCREMENT,
                `wo_code`        VARCHAR(50)          NOT NULL,
                `asset_id`       INT(11) UNSIGNED     NOT NULL,
                `requested_by`   INT(11) UNSIGNED     NOT NULL,
                `assigned_to`    INT(11) UNSIGNED     NULL,
                `vendor_id`      INT(11) UNSIGNED     NULL,
                `type`           ENUM('preventive','corrective','inspection') NOT NULL DEFAULT 'corrective',
                `priority`       ENUM('rendah','sedang','tinggi','kritis')    NOT NULL DEFAULT 'sedang',
                `status`         ENUM('open','in_progress','waiting_part','done','cancelled') NOT NULL DEFAULT 'open',
                `problem_desc`   TEXT                 NOT NULL,
                `action_taken`   TEXT                 NULL,
                `scheduled_date` DATE                 NULL,
                `start_date`     DATE                 NULL,
                `finish_date`    DATE                 NULL,
                `cost`           DECIMAL(15,2)        NULL,
                `notes`          TEXT                 NULL,
                `created_at`     DATETIME             NULL,
                `updated_at`     DATETIME             NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_wo_code`          (`wo_code`),
                INDEX `idx_wo_asset`             (`asset_id`),
                INDEX `idx_wo_requested_by`      (`requested_by`),
                INDEX `idx_wo_assigned_to`       (`assigned_to`),
                INDEX `idx_wo_status`            (`status`),
                INDEX `idx_wo_priority`          (`priority`),
                INDEX `idx_wo_status_priority`   (`status`, `priority`),
                CONSTRAINT `fk_wo_asset`
                    FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`)
                    ON UPDATE CASCADE ON DELETE RESTRICT,
                CONSTRAINT `fk_wo_requested_by`
                    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`)
                    ON UPDATE CASCADE ON DELETE RESTRICT,
                CONSTRAINT `fk_wo_assigned_to`
                    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`)
                    ON UPDATE CASCADE ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS `work_orders`');
    }
}
