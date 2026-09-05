<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatConversationSettings extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `chat_conversation_settings` (
                `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`        INT UNSIGNED NOT NULL,
                `chat_type`      ENUM('direct', 'marketplace') NOT NULL,
                `target_id`      INT UNSIGNED NOT NULL,
                `target_sub_id`  INT UNSIGNED DEFAULT 0,
                `is_pinned`      TINYINT(1) DEFAULT 0,
                `pinned_at`      DATETIME NULL,
                `is_archived`    TINYINT(1) DEFAULT 0,
                `archived_at`    DATETIME NULL,
                `cleared_at`     DATETIME NULL,
                `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at`     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_user_chat_thread` (`user_id`, `chat_type`, `target_id`, `target_sub_id`),
                KEY `idx_ucs_user` (`user_id`, `is_pinned`, `is_archived`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        $this->forge->dropTable('chat_conversation_settings', true);
    }
}
