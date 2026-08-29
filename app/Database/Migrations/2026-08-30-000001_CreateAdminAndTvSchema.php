<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminAndTvSchema extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom role ke users jika belum ada
        if (!$this->db->fieldExists('role', 'users')) {
            $this->forge->addColumn('users', [
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'user',
                    'after'      => 'password',
                ],
            ]);
        }

        // 2. Tabel APP NOTIFICATIONS (Broadcast & Push Notification)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `app_notifications` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `title`      VARCHAR(255) NOT NULL,
                `message`    TEXT         NOT NULL,
                `type`       ENUM('info', 'announcement', 'promo', 'warning', 'system') DEFAULT 'info',
                `target`     ENUM('all', 'user') DEFAULT 'all',
                `user_id`    INT UNSIGNED DEFAULT NULL COMMENT 'NULL = untuk semua user',
                `action_url` VARCHAR(255) DEFAULT NULL,
                `is_pinned`  TINYINT(1)   DEFAULT 0,
                `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Tabel NOTIFICATION READS (Status Dibaca per User)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `notification_reads` (
                `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `notification_id` INT UNSIGNED NOT NULL,
                `user_id`         INT UNSIGNED NOT NULL,
                `read_at`         DATETIME     DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `user_notif_read` (`notification_id`, `user_id`),
                FOREIGN KEY (`notification_id`) REFERENCES `app_notifications`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. Tabel TV & LIVE STREAMING CHANNELS
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tv_channels` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name`        VARCHAR(150) NOT NULL,
                `category`    VARCHAR(100) DEFAULT 'Nasional',
                `stream_url`  TEXT         NOT NULL,
                `logo_url`    TEXT         DEFAULT NULL,
                `description` TEXT         DEFAULT NULL,
                `is_active`   TINYINT(1)   DEFAULT 1,
                `sort_order`  INT          DEFAULT 0,
                `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('notification_reads', true);
        $this->forge->dropTable('app_notifications', true);
        $this->forge->dropTable('tv_channels', true);

        if ($this->db->fieldExists('role', 'users')) {
            $this->forge->dropColumn('users', 'role');
        }
    }
}
