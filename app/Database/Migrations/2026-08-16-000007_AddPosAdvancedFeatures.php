<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPosAdvancedFeatures extends Migration
{
    public function up()
    {
        // 1. Add variants_json to pos_products
        if (!$this->db->fieldExists('variants_json', 'pos_products')) {
            $this->forge->addColumn('pos_products', [
                'variants_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'image',
                ],
            ]);
        }

        // 2. Add selected_variants to pos_order_items
        if (!$this->db->fieldExists('selected_variants', 'pos_order_items')) {
            $this->forge->addColumn('pos_order_items', [
                'selected_variants' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'notes',
                ],
            ]);
        }

        // 3. Add voucher_code & discount_amount to pos_orders
        $ordersCols = [];
        if (!$this->db->fieldExists('voucher_code', 'pos_orders')) {
            $ordersCols['voucher_code'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'delivery_fee',
            ];
        }
        if (!$this->db->fieldExists('discount_amount', 'pos_orders')) {
            $ordersCols['discount_amount'] = [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'voucher_code',
            ];
        }
        if (!empty($ordersCols)) {
            $this->forge->addColumn('pos_orders', $ordersCols);
        }

        // 4. Create pos_vouchers table
        if (!$this->db->tableExists('pos_vouchers')) {
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
                'code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['percent', 'nominal', 'free_shipping'],
                    'default'    => 'nominal',
                ],
                'value' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'min_order' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'max_discount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'usage_limit' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0, // 0 = unlimited
                ],
                'used_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'expires_at' => [
                    'type' => 'DATE',
                    'null' => true,
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
            $this->forge->addKey(['user_id', 'code']);
            $this->forge->createTable('pos_vouchers');
        }

        // 5. Create pos_loyalty_stamps table
        if (!$this->db->tableExists('pos_loyalty_stamps')) {
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
                'customer_phone' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                ],
                'customer_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'stamps_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'total_claimed' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'last_order_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
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
            $this->forge->addKey(['user_id', 'customer_phone']);
            $this->forge->createTable('pos_loyalty_stamps');
        }
    }

    public function down()
    {
        $this->forge->dropTable('pos_loyalty_stamps', true);
        $this->forge->dropTable('pos_vouchers', true);
        if ($this->db->fieldExists('variants_json', 'pos_products')) {
            $this->forge->dropColumn('pos_products', 'variants_json');
        }
        if ($this->db->fieldExists('selected_variants', 'pos_order_items')) {
            $this->forge->dropColumn('pos_order_items', 'selected_variants');
        }
        if ($this->db->fieldExists('voucher_code', 'pos_orders')) {
            $this->forge->dropColumn('pos_orders', 'voucher_code');
        }
        if ($this->db->fieldExists('discount_amount', 'pos_orders')) {
            $this->forge->dropColumn('pos_orders', 'discount_amount');
        }
    }
}
