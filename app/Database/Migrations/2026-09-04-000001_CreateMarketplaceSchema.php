<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMarketplaceSchema extends Migration
{
    public function up(): void
    {
        // 1. Kolom unique username pada users (jika belum ada)
        if (!$this->db->fieldExists('username', 'users')) {
            $this->forge->addColumn('users', [
                'username' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'unique'     => true,
                    'after'      => 'name',
                ],
            ]);
        }

        // 2. Tabel MARKETPLACE LISTINGS
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_listings` (
                `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`         INT UNSIGNED NOT NULL,
                `title`           VARCHAR(255) NOT NULL,
                `slug`            VARCHAR(255) NOT NULL,
                `type`            ENUM('sale', 'rent') DEFAULT 'sale' COMMENT 'sale=jual, rent=sewa',
                `category`        VARCHAR(100) NOT NULL COMMENT 'Motor, Mobil, Properti, Elektronik, Gadget, dll',
                `condition`       ENUM('new', 'like_new', 'used_good', 'used_fair') DEFAULT 'used_good',
                `price`           DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
                `rent_period`     VARCHAR(50) DEFAULT NULL COMMENT 'hari, bulan, tahun jika rent',
                `description`     TEXT DEFAULT NULL,
                `location`        VARCHAR(200) NOT NULL COMMENT 'Area / Kota COD',
                `whatsapp`        VARCHAR(50) DEFAULT NULL,
                `third_party_url` VARCHAR(500) DEFAULT NULL COMMENT 'Shopee, Tokopedia, Bukalapak link',
                `status`          ENUM('active', 'sold', 'rented', 'inactive') DEFAULT 'active',
                `views_count`     INT UNSIGNED DEFAULT 0,
                `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_market_status` (`status`),
                KEY `idx_market_type` (`type`),
                KEY `idx_market_cat` (`category`),
                KEY `idx_market_user` (`user_id`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Tabel MARKETPLACE IMAGES (Multi Foto)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_images` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `listing_id` INT UNSIGNED NOT NULL,
                `image_url`  VARCHAR(500) NOT NULL,
                `is_primary` TINYINT(1) DEFAULT 0,
                `sort_order` INT DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_img_listing` (`listing_id`),
                FOREIGN KEY (`listing_id`) REFERENCES `marketplace_listings`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. Tabel MARKETPLACE COMMENTS (Diskusi / Tanya Jawab)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_comments` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `listing_id` INT UNSIGNED NOT NULL,
                `user_id`    INT UNSIGNED NOT NULL,
                `comment`    TEXT NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_comment_listing` (`listing_id`),
                FOREIGN KEY (`listing_id`) REFERENCES `marketplace_listings`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 5. Tabel MARKETPLACE ORDERS (Pengajuan Minat / Order via App)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `marketplace_orders` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `listing_id` INT UNSIGNED NOT NULL,
                `buyer_id`   INT UNSIGNED NOT NULL,
                `seller_id`  INT UNSIGNED NOT NULL,
                `order_type` ENUM('buy', 'rent') DEFAULT 'buy',
                `price`      DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
                `notes`      TEXT DEFAULT NULL,
                `status`     ENUM('pending', 'contacted', 'completed', 'cancelled') DEFAULT 'pending',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_order_buyer` (`buyer_id`),
                KEY `idx_order_seller` (`seller_id`),
                FOREIGN KEY (`listing_id`) REFERENCES `marketplace_listings`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`buyer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('marketplace_orders', true);
        $this->forge->dropTable('marketplace_comments', true);
        $this->forge->dropTable('marketplace_images', true);
        $this->forge->dropTable('marketplace_listings', true);

        if ($this->db->fieldExists('username', 'users')) {
            $this->forge->dropColumn('users', 'username');
        }
    }
}
