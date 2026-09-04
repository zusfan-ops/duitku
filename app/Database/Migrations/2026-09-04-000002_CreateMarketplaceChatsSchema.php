<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMarketplaceChatsSchema extends Migration
{
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_chats` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `listing_id`  INT UNSIGNED NOT NULL,
                `buyer_id`    INT UNSIGNED NOT NULL,
                `seller_id`   INT UNSIGNED NOT NULL,
                `sender_id`   INT UNSIGNED NOT NULL,
                `message`     TEXT NOT NULL,
                `is_read`     TINYINT(1) DEFAULT 0,
                `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_chat_conv` (`listing_id`, `buyer_id`),
                KEY `idx_chat_seller` (`seller_id`),
                KEY `idx_chat_buyer` (`buyer_id`),
                KEY `idx_chat_sender` (`sender_id`),
                FOREIGN KEY (`listing_id`) REFERENCES `marketplace_listings`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`buyer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('marketplace_chats', true);
    }
}
