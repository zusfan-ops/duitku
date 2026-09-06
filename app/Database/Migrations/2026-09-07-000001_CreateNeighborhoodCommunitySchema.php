<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNeighborhoodCommunitySchema extends Migration
{
    public function up(): void
    {
        // 1. TABEL NEIGHBORHOODS (Wilayah RT/RW & Komunitas)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `neighborhoods` (
                `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name`             VARCHAR(150) NOT NULL COMMENT 'Nama Lingkungan/Komunitas, misal: RT 04 Griya Asri',
                `province`         VARCHAR(100) NOT NULL,
                `city`             VARCHAR(100) NOT NULL,
                `district`         VARCHAR(100) NOT NULL COMMENT 'Kecamatan',
                `subdistrict`      VARCHAR(100) NOT NULL COMMENT 'Kelurahan / Desa',
                `rw`               VARCHAR(10)  NOT NULL COMMENT 'Nomor RW',
                `rt`               VARCHAR(10)  NOT NULL COMMENT 'Nomor RT',
                `unique_code`      VARCHAR(50)  NOT NULL UNIQUE COMMENT 'Kode unik bergabung ke RT, misal: RT04-GRIYA-2026',
                `qr_join_token`    VARCHAR(100) DEFAULT NULL COMMENT 'Token QR Code untuk scan cepat gabung RT',
                `admin_user_id`    INT UNSIGNED DEFAULT NULL COMMENT 'Ketua RT / Pengelola Utama',
                `bank_wallet_id`   INT UNSIGNED DEFAULT NULL COMMENT 'ID Dompet/Kas RT untuk pencatatan keuangan bersama',
                `address_note`     TEXT         DEFAULT NULL COMMENT 'Keterangan alamat sekretariat / balai warga',
                `auto_approval`    TINYINT(1)   DEFAULT 0 COMMENT '1: Otomatis disetujui jika input kode unik, 0: Butuh persetujuan Ketua RT',
                `max_borrow_limit_domisili` DECIMAL(15,2) DEFAULT 250000.00 COMMENT 'Batas nilai barang pinjam untuk warga domisili',
                `created_at`       DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at`       DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_neighborhood_region` (`province`, `city`, `district`, `subdistrict`, `rw`, `rt`),
                INDEX `idx_neighborhood_code` (`unique_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. PENYESUAIAN TABEL USERS (Tambahkan kolom keanggotaan RT & status tempat tinggal)
        if ($this->db->tableExists('users')) {
            $userColumns = [];

            if (!$this->db->fieldExists('neighborhood_id', 'users')) {
                $userColumns['neighborhood_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'role',
                ];
            }

            if (!$this->db->fieldExists('residence_status', 'users')) {
                $userColumns['residence_status'] = [
                    'type'       => 'ENUM',
                    'constraint' => ['permanent', 'temporary'],
                    'default'    => 'permanent',
                    'after'      => 'neighborhood_id',
                    'comment'    => 'permanent: Warga Tetap (KTP RT sini), temporary: Domisili/Kontrak/Kost',
                ];
            }

            if (!$this->db->fieldExists('rt_verification_status', 'users')) {
                $userColumns['rt_verification_status'] = [
                    'type'       => 'ENUM',
                    'constraint' => ['unregistered', 'pending', 'verified', 'rejected'],
                    'default'    => 'unregistered',
                    'after'      => 'residence_status',
                    'comment'    => 'Status persetujuan masuk lingkungan RT',
                ];
            }

            if (!$this->db->fieldExists('house_number', 'users')) {
                $userColumns['house_number'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'rt_verification_status',
                    'comment'    => 'Nomor Rumah / Blok / Kamar Kost',
                ];
            }

            if (!$this->db->fieldExists('rt_verified_at', 'users')) {
                $userColumns['rt_verified_at'] = [
                    'type'       => 'DATETIME',
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'house_number',
                ];
            }

            if (!$this->db->fieldExists('rt_verified_by', 'users')) {
                $userColumns['rt_verified_by'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'rt_verified_at',
                ];
            }

            if (!empty($userColumns)) {
                $this->forge->addColumn('users', $userColumns);
            }
        }

        // 3. TABEL NEIGHBORHOOD VOUCHING / PENJAMIN WARGA
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `neighborhood_vouches` (
                `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `neighborhood_id`  INT UNSIGNED NOT NULL,
                `target_user_id`   INT UNSIGNED NOT NULL COMMENT 'Warga yang butuh penjamin',
                `voucher_user_id`  INT UNSIGNED NOT NULL COMMENT 'Warga terverifikasi yang menjamin',
                `status`           ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
                `notes`            TEXT DEFAULT NULL,
                `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_vouch` (`target_user_id`, `voucher_user_id`),
                FOREIGN KEY (`neighborhood_id`) REFERENCES `neighborhoods`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`target_user_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`voucher_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. TABEL COMMUNITY TOOLS (Inventaris Alat Bersama & Warga)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `community_tools` (
                `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `neighborhood_id`    INT UNSIGNED NOT NULL,
                `owner_user_id`      INT UNSIGNED DEFAULT NULL COMMENT 'NULL jika milik kas/inventaris RT, atau ID user jika milik pribadi warga yg dishare',
                `name`               VARCHAR(150) NOT NULL COMMENT 'Misal: Mesin Rumput, Bor Listrik, Tenda, Tangga Lipat',
                `category`           VARCHAR(80)  NOT NULL DEFAULT 'Pertukangan' COMMENT 'Pertukangan, Kebersihan, Acara/Tenda, Pertamanan, Otomotif, Elektronik, Medis/Darurat',
                `description`        TEXT         DEFAULT NULL,
                `photo`              TEXT         DEFAULT NULL,
                `status`             ENUM('available', 'borrowed', 'maintenance', 'retired') DEFAULT 'available',
                `rental_fee`         DECIMAL(15,2) DEFAULT 0.00 COMMENT '0 jika gratis untuk warga RT',
                `deposit_amount`     DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Uang jaminan sementara selama barang dipinjam',
                `max_rent_days`      INT          DEFAULT 3 COMMENT 'Maksimal durasi pinjam per sesi (hari)',
                `condition_note`     VARCHAR(255) DEFAULT 'Baik & Berfungsi Normal',
                `created_at`         DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at`         DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`neighborhood_id`) REFERENCES `neighborhoods`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`owner_user_id`)   REFERENCES `users`(`id`) ON DELETE SET NULL,
                INDEX `idx_tool_neighbor_status` (`neighborhood_id`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 5. TABEL TOOL RENTALS (Peminjaman Alat, Token QR, & Ledger Integrasi)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tool_rentals` (
                `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `neighborhood_id`         INT UNSIGNED NOT NULL,
                `tool_id`                 INT UNSIGNED NOT NULL,
                `borrower_user_id`        INT UNSIGNED NOT NULL,
                `status`                  ENUM('requested', 'approved', 'borrowed', 'returned', 'disputed', 'cancelled') DEFAULT 'requested',
                `rental_fee`              DECIMAL(15,2) DEFAULT 0.00,
                `deposit_amount`          DECIMAL(15,2) DEFAULT 0.00,
                `start_date`              DATE          NOT NULL,
                `due_date`                DATE          NOT NULL,
                `actual_return_date`      DATETIME      DEFAULT NULL,
                `handover_token`          VARCHAR(32)   NOT NULL COMMENT 'Kode/Token rahasia saat serah terima barang',
                `return_token`            VARCHAR(32)   NOT NULL COMMENT 'Kode/Token rahasia saat pengembalian barang',
                `handover_confirmed_by`   INT UNSIGNED  DEFAULT NULL,
                `return_confirmed_by`     INT UNSIGNED  DEFAULT NULL,
                `borrower_note`           TEXT          DEFAULT NULL,
                `admin_note`              TEXT          DEFAULT NULL,
                `dispute_reason`          TEXT          DEFAULT NULL,
                `fee_transaction_id`      INT UNSIGNED  DEFAULT NULL COMMENT 'Relasi ke tabel transactions (catatan pengeluaran sewa)',
                `deposit_transaction_id`  INT UNSIGNED  DEFAULT NULL COMMENT 'Relasi ke tabel transactions (catatan deposit/escrow)',
                `refund_transaction_id`   INT UNSIGNED  DEFAULT NULL COMMENT 'Relasi ke tabel transactions (catatan pengembalian deposit)',
                `created_at`              DATETIME      DEFAULT CURRENT_TIMESTAMP,
                `updated_at`              DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`neighborhood_id`)      REFERENCES `neighborhoods`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`tool_id`)              REFERENCES `community_tools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`borrower_user_id`)     REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`handover_confirmed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`return_confirmed_by`)   REFERENCES `users`(`id`) ON DELETE SET NULL,
                INDEX `idx_rental_tokens` (`handover_token`, `return_token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 6. TABEL ERRANDS (Titip Belanja / Jastip Antar-Warga Lingkungan)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `errands` (
                `id`                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `neighborhood_id`        INT UNSIGNED NOT NULL,
                `organizer_user_id`      INT UNSIGNED NOT NULL COMMENT 'Warga yang pergi belanja',
                `destination_store`      VARCHAR(150) NOT NULL COMMENT 'Misal: Pasar Pagi RW 02, Superindo, Apotek 24 Jam',
                `description`            TEXT         DEFAULT NULL COMMENT 'Rencana rute/kendaraan/catatan barang yg bisa dititip',
                `cutoff_time`            DATETIME     NOT NULL COMMENT 'Batas akhir warga menitip pesanan',
                `est_delivery_time`      DATETIME     DEFAULT NULL COMMENT 'Perkiraan waktu sampai/antar ke rumah warga',
                `max_requesters`         INT          DEFAULT 5 COMMENT 'Batas jumlah warga yang boleh menitip',
                `status`                 ENUM('open', 'shopping', 'delivering', 'completed', 'cancelled') DEFAULT 'open',
                `created_at`             DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at`             DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`neighborhood_id`)   REFERENCES `neighborhoods`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`organizer_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX `idx_errand_status` (`neighborhood_id`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 7. TABEL ERRAND ITEMS (Item Titipan Belanja & Integrasi Keuangan)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `errand_items` (
                `id`                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `errand_id`              INT UNSIGNED NOT NULL,
                `requester_user_id`      INT UNSIGNED NOT NULL COMMENT 'Warga yang menitip barang',
                `item_name`              VARCHAR(200) NOT NULL,
                `quantity`               DECIMAL(10,2) DEFAULT 1.00,
                `unit`                   VARCHAR(30)  DEFAULT 'pcs' COMMENT 'kg, ikat, bungkus, botol, pack',
                `estimated_price`        DECIMAL(15,2) DEFAULT 0.00,
                `actual_price`           DECIMAL(15,2) DEFAULT 0.00,
                `service_fee`            DECIMAL(15,2) DEFAULT 5000.00 COMMENT 'Uang lelah / tip jasa belanja ke organizer',
                `status`                 ENUM('pending', 'accepted', 'purchased', 'delivered', 'cancelled') DEFAULT 'pending',
                `handover_token`         VARCHAR(32)  NOT NULL COMMENT 'Token rahasia konfirmasi serah terima belanjaan',
                `notes`                  TEXT         DEFAULT NULL COMMENT 'Merek pengganti, instruksi khusus, dll',
                `receipt_photo`          TEXT         DEFAULT NULL COMMENT 'Foto struk belanja asli',
                `transaction_id`         INT UNSIGNED DEFAULT NULL COMMENT 'ID transaksi pengeluaran tercatat di requester',
                `organizer_tx_id`        INT UNSIGNED DEFAULT NULL COMMENT 'ID transaksi pemasukan jasa tercatat di organizer',
                `delivered_at`           DATETIME     DEFAULT NULL,
                `created_at`             DATETIME     DEFAULT CURRENT_TIMESTAMP,
                `updated_at`             DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`errand_id`)          REFERENCES `errands`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`requester_user_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX `idx_errand_item_token` (`handover_token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `errand_items`");
        $this->db->query("DROP TABLE IF EXISTS `errands`");
        $this->db->query("DROP TABLE IF EXISTS `tool_rentals`");
        $this->db->query("DROP TABLE IF EXISTS `community_tools`");
        $this->db->query("DROP TABLE IF EXISTS `neighborhood_vouches`");
        $this->db->query("DROP TABLE IF EXISTS `neighborhoods`");

        if ($this->db->tableExists('users')) {
            $colsToDrop = [];
            foreach (['rt_verified_by', 'rt_verified_at', 'house_number', 'rt_verification_status', 'residence_status', 'neighborhood_id'] as $col) {
                if ($this->db->fieldExists($col, 'users')) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $this->forge->dropColumn('users', $colsToDrop);
            }
        }
    }
}
