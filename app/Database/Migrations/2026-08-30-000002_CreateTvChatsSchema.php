<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTvChatsSchema extends Migration
{
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tv_chats` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`    INT UNSIGNED NOT NULL,
                `user_name`  VARCHAR(150) NOT NULL,
                `message`    TEXT         NOT NULL,
                `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_tv_chats_created` (`created_at`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('tv_chats', true);
    }
}
