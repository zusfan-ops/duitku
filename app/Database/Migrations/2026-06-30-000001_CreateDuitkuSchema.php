<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDuitkuSchema extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // USERS
        // =====================================================================
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `users` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name`       VARCHAR(100) NOT NULL,
                `email`      VARCHAR(150) NOT NULL UNIQUE,
                `password`   VARCHAR(255) NOT NULL,
                `avatar`     VARCHAR(10)  DEFAULT NULL COMMENT 'emoji atau inisial warna',
                `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // =====================================================================
        // SETTINGS (per user)
        // =====================================================================
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `settings` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`    INT UNSIGNED NOT NULL,
                `key`        VARCHAR(100) NOT NULL,
                `value`      TEXT         DEFAULT NULL,
                `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `user_key` (`user_id`, `key`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // =====================================================================
        // CATEGORIES
        // =====================================================================
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `categories` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`    INT UNSIGNED DEFAULT NULL COMMENT 'NULL = sistem default',
                `name`       VARCHAR(100) NOT NULL,
                `type`       ENUM('income','expense') NOT NULL,
                `icon`       VARCHAR(50)  DEFAULT 'circle',
                `color`      VARCHAR(20)  DEFAULT '#6B7280',
                `is_default` TINYINT(1)   DEFAULT 0,
                `sort_order` INT          DEFAULT 0,
                `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // =====================================================================
        // TRANSACTIONS
        // =====================================================================
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `transactions` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`     INT UNSIGNED NOT NULL,
                `category_id` INT UNSIGNED DEFAULT NULL,
                `type`        ENUM('income','expense') NOT NULL,
                `amount`      DECIMAL(15,2) NOT NULL DEFAULT 0,
                `note`        TEXT          DEFAULT NULL,
                `date`        DATE          NOT NULL,
                `created_at`  DATETIME      DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
                FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
                INDEX idx_user_date (`user_id`, `date`),
                INDEX idx_user_type (`user_id`, `type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // =====================================================================
        // SEED: Default categories (is_default=1, user_id=NULL)
        // =====================================================================
        $this->db->query("
            INSERT INTO `categories` (`user_id`, `name`, `type`, `icon`, `color`, `is_default`, `sort_order`) VALUES
            (NULL, 'Makanan',      'expense', 'food',       '#EF4444', 1, 1),
            (NULL, 'Transport',    'expense', 'transport',  '#3B82F6', 1, 2),
            (NULL, 'Listrik/Air',  'expense', 'utilities',  '#F59E0B', 1, 3),
            (NULL, 'Belanja',      'expense', 'shopping',   '#8B5CF6', 1, 4),
            (NULL, 'Hiburan',      'expense', 'fun',        '#EC4899', 1, 5),
            (NULL, 'Kesehatan',    'expense', 'health',     '#10B981', 1, 6),
            (NULL, 'Sewa/Kontrakan','expense','home',       '#6366F1', 1, 7),
            (NULL, 'Lainnya',      'expense', 'other',      '#6B7280', 1, 8),
            (NULL, 'Gaji',         'income',  'salary',     '#059669', 1, 9),
            (NULL, 'Freelance',    'income',  'freelance',  '#0891B2', 1, 10),
            (NULL, 'Hadiah',       'income',  'gift',       '#D97706', 1, 11),
            (NULL, 'Lainnya',      'income',  'other',      '#64748B', 1, 12)
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `transactions`");
        $this->db->query("DROP TABLE IF EXISTS `categories`");
        $this->db->query("DROP TABLE IF EXISTS `settings`");
        $this->db->query("DROP TABLE IF EXISTS `users`");
    }
}
