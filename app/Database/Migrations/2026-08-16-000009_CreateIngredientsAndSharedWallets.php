<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIngredientsAndSharedWallets extends Migration
{
    public function up()
    {
        // 1. pos_ingredients (Raw material inventory)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'gram', // gram, ml, pcs, kg, liter, sachet, lembar
            ],
            'stock' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'min_stock' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '10.00',
            ],
            'cost_per_unit' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'name']);
        $this->forge->createTable('pos_ingredients', true);

        // 2. pos_recipes (Bill of Materials linking products to ingredients)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'ingredient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'amount_needed' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '1.00',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'product_id', 'ingredient_id']);
        $this->forge->createTable('pos_recipes', true);

        // 3. wallet_members (Shared Wallets & Multi-user collaboration)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'wallet_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'owner_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'member_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'member_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'member_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['editor', 'viewer'],
                'default'    => 'editor',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'pending'],
                'default'    => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['wallet_id', 'member_email']);
        $this->forge->createTable('wallet_members', true);

        // 4. Add created_by_name to transactions table if not present
        if ($this->db->tableExists('transactions') && !$this->db->fieldExists('created_by_name', 'transactions')) {
            $this->forge->addColumn('transactions', [
                'created_by_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'user_id',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('pos_ingredients', true);
        $this->forge->dropTable('pos_recipes', true);
        $this->forge->dropTable('wallet_members', true);
        if ($this->db->tableExists('transactions') && $this->db->fieldExists('created_by_name', 'transactions')) {
            $this->forge->dropColumn('transactions', 'created_by_name');
        }
    }
}
