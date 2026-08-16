<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarketplaceDeliveryOrderFlow extends Migration
{
    public function up()
    {
        // Add delivery & marketplace fields to pos_orders
        $fields = [
            'order_type' => [
                'type'       => "ENUM('dine_in', 'takeaway', 'delivery')",
                'default'    => 'dine_in',
                'after'      => 'order_source',
            ],
            'delivery_address' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'customer_phone',
            ],
            'delivery_notes' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'delivery_address',
            ],
            'delivery_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'delivery_notes',
            ],
            'pickup_time' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'delivery_fee',
            ],
        ];

        $this->forge->addColumn('pos_orders', $fields);

        // Update status column enum values to include delivering and delivered_unpaid
        $this->db->query("ALTER TABLE `pos_orders` MODIFY `status` ENUM('pending', 'processing', 'delivering', 'served_unpaid', 'delivered_unpaid', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        $this->forge->dropColumn('pos_orders', [
            'order_type',
            'delivery_address',
            'delivery_notes',
            'delivery_fee',
            'pickup_time',
        ]);
        $this->db->query("ALTER TABLE `pos_orders` MODIFY `status` ENUM('pending', 'processing', 'served_unpaid', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
}
