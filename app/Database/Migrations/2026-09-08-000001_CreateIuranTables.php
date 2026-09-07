<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIuranTables extends Migration
{
    public function up()
    {
        // 1. neighborhood_iuran_config table - konfigurasi iuran per periode
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'neighborhood_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'period_type' => [
                'type'       => 'ENUM',
                'constraint' => ['monthly', 'weekly', 'yearly'],
                'default'    => 'monthly',
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'Iuran Warga Bulanan',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['neighborhood_id', 'is_active']);
        $this->forge->createTable('neighborhood_iuran_config', true);

        // 2. neighborhood_iuran_payments table - pembayaran iuran per warga per periode
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'neighborhood_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'config_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'resident_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'period_month' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
            ],
            'amount_expected' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'amount_paid' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['unpaid', 'partial', 'paid', 'overdue'],
                'default'    => 'unpaid',
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'paid_via' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'cash',
            ],
            'kas_ledger_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'reminder_sent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['neighborhood_id', 'status']);
        $this->forge->addKey('period_month');
        $this->forge->addUniqueKey(['config_id', 'resident_user_id', 'period_month']);
        $this->forge->createTable('neighborhood_iuran_payments', true);
    }

    public function down()
    {
        $this->forge->dropTable('neighborhood_iuran_payments', true);
        $this->forge->dropTable('neighborhood_iuran_config', true);
    }
}
