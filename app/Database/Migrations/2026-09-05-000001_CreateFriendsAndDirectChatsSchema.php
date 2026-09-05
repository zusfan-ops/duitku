<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFriendsAndDirectChatsSchema extends Migration
{
    public function up()
    {
        // 1. Table user_friends
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `user_friends` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`     INT UNSIGNED NOT NULL,
                `friend_id`   INT UNSIGNED NOT NULL,
                `status`      ENUM('pending', 'accepted', 'rejected', 'blocked') DEFAULT 'pending',
                `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_friend_pair` (`user_id`, `friend_id`),
                KEY `idx_uf_user` (`user_id`),
                KEY `idx_uf_friend` (`friend_id`),
                KEY `idx_uf_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Table direct_chats (WhatsApp-style Direct Messaging)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `direct_chats` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `sender_id`   INT UNSIGNED NOT NULL,
                `receiver_id` INT UNSIGNED NOT NULL,
                `message`     TEXT NOT NULL,
                `is_read`     TINYINT(1) DEFAULT 0,
                `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_dc_pair` (`sender_id`, `receiver_id`),
                KEY `idx_dc_receiver` (`receiver_id`, `is_read`),
                KEY `idx_dc_sender` (`sender_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('direct_chats', true);
        $this->forge->dropTable('user_friends', true);
    }
}
