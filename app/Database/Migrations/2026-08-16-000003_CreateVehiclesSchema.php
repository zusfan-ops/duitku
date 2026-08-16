<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVehiclesSchema extends Migration
{
    public function up(): void
    {
        // 1. VEHICLES TABLE
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `vehicles` (
                `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`         INT UNSIGNED NOT NULL,
                `name`            VARCHAR(100) NOT NULL COMMENT 'cth: Vario 160 Harian',
                `type`            ENUM('motor', 'mobil', 'truk', 'lainnya') NOT NULL DEFAULT 'motor',
                `license_plate`   VARCHAR(20)  DEFAULT NULL COMMENT 'Plat Nomor cth: H 1234 AB',
                `brand`           VARCHAR(50)  DEFAULT NULL COMMENT 'Merk cth: Honda, Toyota',
                `model_year`      VARCHAR(10)  DEFAULT NULL COMMENT 'Tahun Pembuatan',
                `odometer`        INT UNSIGNED DEFAULT 0 COMMENT 'KM saat ini',
                `tax_annual_date` DATE         DEFAULT NULL COMMENT 'Tenggat Pajak 1 Tahunan (PKB)',
                `tax_5year_date`  DATE         DEFAULT NULL COMMENT 'Tenggat Pajak 5 Tahunan (Plat)',
                `photo`           VARCHAR(255) DEFAULT NULL,
                `created_at`      DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at`      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX idx_vehicle_user (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. VEHICLE LOGS TABLE (Riwayat Servis, Ganti Oli, Pajak, dll)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `vehicle_logs` (
                `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `vehicle_id`      INT UNSIGNED NOT NULL,
                `user_id`         INT UNSIGNED NOT NULL,
                `type`            ENUM('ganti_oli', 'service_rutin', 'pajak_tahunan', 'pajak_5tahunan', 'ganti_ban', 'bbm', 'perbaikan', 'lainnya') NOT NULL DEFAULT 'service_rutin',
                `title`           VARCHAR(150) NOT NULL COMMENT 'cth: Service 10.000 KM + Oli Shell',
                `cost`            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `km`              INT UNSIGNED DEFAULT NULL COMMENT 'Odometer saat servis',
                `next_km`         INT UNSIGNED DEFAULT NULL COMMENT 'Target KM berikutnya',
                `next_date`       DATE         DEFAULT NULL COMMENT 'Target tanggal servis/oli berikutnya',
                `date`            DATE         NOT NULL COMMENT 'Tanggal servis',
                `workshop`        VARCHAR(100) DEFAULT NULL COMMENT 'Nama bengkel / tempat servis',
                `notes`           TEXT         DEFAULT NULL,
                `created_at`      DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at`      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
                INDEX idx_log_vehicle (`vehicle_id`),
                INDEX idx_log_user (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `vehicle_logs`");
        $this->db->query("DROP TABLE IF EXISTS `vehicles`");
    }
}
