<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWallets extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'type'            => ['type' => 'ENUM', 'constraint' => ['bank','e-wallet','cash','savings_home'], 'default' => 'cash'],
            'icon'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '💵'],
            'color'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '#0AA956'],
            'initial_balance' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'is_default'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sort_order'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wallets');

        // Create default wallet for every existing user
        $db    = \Config\Database::connect();
        $users = $db->table('users')->get()->getResultArray();
        $now   = date('Y-m-d H:i:s');
        foreach ($users as $user) {
            $db->table('wallets')->insert([
                'user_id'         => $user['id'],
                'name'            => 'Kas / Dompet',
                'type'            => 'cash',
                'icon'            => '💵',
                'color'           => '#0AA956',
                'initial_balance' => 0,
                'is_default'      => 1,
                'sort_order'      => 0,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('wallets', true);
    }
}
