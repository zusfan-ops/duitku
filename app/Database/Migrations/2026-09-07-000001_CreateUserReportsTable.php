<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserReportsTable extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `user_reports` (
                `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `reporter_id`   INT UNSIGNED NOT NULL,
                `reported_user_id` INT UNSIGNED NULL,
                `target_type`   ENUM('user', 'listing', 'comment', 'message') NOT NULL DEFAULT 'user',
                `target_id`     INT UNSIGNED NULL,
                `reason`        ENUM('spam', 'scam', 'inappropriate', 'harassment', 'fake', 'illegal', 'other') NOT NULL DEFAULT 'other',
                `description`   TEXT NULL,
                `status`        ENUM('pending', 'reviewed', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
                `admin_note`    TEXT NULL,
                `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_ur_reporter` (`reporter_id`),
                KEY `idx_ur_reported_user` (`reported_user_id`),
                KEY `idx_ur_target` (`target_type`, `target_id`),
                KEY `idx_ur_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('user_reports', true);
    }
}
