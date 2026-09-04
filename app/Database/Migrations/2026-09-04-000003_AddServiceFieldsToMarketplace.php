<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServiceFieldsToMarketplace extends Migration
{
    public function up()
    {
        // 1. Perbarui kolom `type` untuk mendukung 'service' (jasa)
        $this->db->query("
            ALTER TABLE `marketplace_listings` 
            MODIFY COLUMN `type` ENUM('sale', 'rent', 'service') DEFAULT 'sale' COMMENT 'sale=jual, rent=sewa, service=layanan jasa'
        ");

        // 2. Buat kolom `condition` bisa bernilai NULL (karena jasa tidak punya kondisi bekas/baru)
        $this->db->query("
            ALTER TABLE `marketplace_listings` 
            MODIFY COLUMN `condition` ENUM('new', 'like_new', 'used_good', 'used_fair') NULL DEFAULT NULL
        ");

        // 3. Tambahkan kolom-kolom khusus Layanan Jasa jika belum ada
        $fields = [
            'service_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'comment'    => 'panggilan, di_tempat, keduanya, online',
                'after'      => 'rent_period',
            ],
            'service_area' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Jangkauan radius / wilayah panggilan, contoh: Semarang Kota & Sekitarnya',
                'after'      => 'service_type',
            ],
            'service_hours' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Jam kerja / ketersediaan, contoh: 24 Jam, 08:00 - 21:00',
                'after'      => 'service_area',
            ],
            'rate_unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Satuan tarif: per_sesi, per_jam, per_panggilan, per_unit, borongan, nego',
                'after'      => 'service_hours',
            ],
            'experience_years' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Pengalaman, garansi, keahlian, atau info peralatan',
                'after'      => 'rate_unit',
            ],
        ];

        // Cek satu per satu kolom untuk memastikan idempoten
        foreach ($fields as $colName => $colDef) {
            if (!$this->db->fieldExists($colName, 'marketplace_listings')) {
                $this->forge->addColumn('marketplace_listings', [$colName => $colDef]);
            }
        }
    }

    public function down()
    {
        $cols = ['service_type', 'service_area', 'service_hours', 'rate_unit', 'experience_years'];
        foreach ($cols as $col) {
            if ($this->db->fieldExists($col, 'marketplace_listings')) {
                $this->forge->dropColumn('marketplace_listings', $col);
            }
        }

        $this->db->query("
            ALTER TABLE `marketplace_listings` 
            MODIFY COLUMN `type` ENUM('sale', 'rent') DEFAULT 'sale'
        ");

        $this->db->query("
            ALTER TABLE `marketplace_listings` 
            MODIFY COLUMN `condition` ENUM('new', 'like_new', 'used_good', 'used_fair') DEFAULT 'used_good'
        ");
    }
}
