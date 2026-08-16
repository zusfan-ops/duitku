<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSavingsGoals extends Migration
{
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `savings_goals` (
                `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`       INT UNSIGNED NOT NULL,
                `name`          VARCHAR(150) NOT NULL,
                `icon`          VARCHAR(10)  DEFAULT '🎯',
                `color`         VARCHAR(20)  DEFAULT '#0AA956',
                `target_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `saved_amount`  DECIMAL(15,2) NOT NULL DEFAULT 0,
                `deadline`      DATE         DEFAULT NULL,
                `created_at`    DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX idx_user (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Migrate existing single savings goal from settings table to savings_goals
        $this->db->query("
            INSERT INTO savings_goals (user_id, name, target_amount, saved_amount, created_at)
            SELECT 
                s_name.user_id,
                COALESCE(s_name.value, 'Target Menabung') AS name,
                COALESCE(CAST(s_target.value AS DECIMAL(15,2)), 0) AS target_amount,
                COALESCE(CAST(s_saved.value AS DECIMAL(15,2)), 0) AS saved_amount,
                NOW()
            FROM settings s_name
            LEFT JOIN settings s_target ON s_target.user_id = s_name.user_id AND s_target.key = 'savings_target'
            LEFT JOIN settings s_saved  ON s_saved.user_id  = s_name.user_id AND s_saved.key  = 'savings_saved'
            WHERE s_name.key = 'savings_name'
              AND s_name.value IS NOT NULL
              AND s_name.value != ''
              AND COALESCE(CAST(s_target.value AS DECIMAL(15,2)), 0) > 0
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `savings_goals`");
    }
}
