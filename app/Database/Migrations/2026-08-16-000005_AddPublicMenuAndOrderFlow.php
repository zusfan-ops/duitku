<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicMenuAndOrderFlow extends Migration
{
    public function up()
    {
        // 1. Add fields to pos_orders (table_no, status, order_source)
        $fieldsOrder = [
            'table_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'customer_phone',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'paid', // 'pending', 'processing', 'served_unpaid', 'paid', 'cancelled'
                'after'      => 'table_no',
            ],
            'order_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'cashier', // 'cashier', 'public_menu'
                'after'      => 'status',
            ],
        ];
        if ($this->db->tableExists('pos_orders')) {
            $this->forge->addColumn('pos_orders', $fieldsOrder);
            $this->db->query("CREATE INDEX idx_pos_orders_status ON pos_orders (user_id, status)");
        }

        // 2. Add fields to pos_order_items (notes)
        $fieldsOrderItem = [
            'notes' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'product_name',
            ],
        ];
        if ($this->db->tableExists('pos_order_items')) {
            $this->forge->addColumn('pos_order_items', $fieldsOrderItem);
        }

        // 3. Add fields to pos_products (description, is_available)
        $fieldsProduct = [
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after'=> 'name',
            ],
            'is_available' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'is_active',
            ],
        ];
        if ($this->db->tableExists('pos_products')) {
            $this->forge->addColumn('pos_products', $fieldsProduct);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('pos_orders')) {
            $this->forge->dropColumn('pos_orders', ['table_no', 'status', 'order_source']);
        }
        if ($this->db->tableExists('pos_order_items')) {
            $this->forge->dropColumn('pos_order_items', ['notes']);
        }
        if ($this->db->tableExists('pos_products')) {
            $this->forge->dropColumn('pos_products', ['description', 'is_available']);
        }
    }
}
