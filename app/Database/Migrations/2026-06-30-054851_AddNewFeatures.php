<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNewFeatures extends Migration
{
    public function up()
    {
        // 1. Add avatar to users (commented out because it already exists)
        // $this->forge->addColumn('users', [
        //     'avatar' => [
        //         'type'       => 'VARCHAR',
        //         'constraint' => 255,
        //         'null'       => true,
        //         'after'      => 'password',
        //     ]
        // ]);

        // 2. Create recurring_transactions table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'category_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['income', 'expense'],
                'default'    => 'expense',
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'next_date' => [
                'type' => 'DATE',
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        // If category is deleted, set to null
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('recurring_transactions', true);
    }

    public function down()
    {
        $this->forge->dropTable('recurring_transactions', true);
        $this->forge->dropColumn('users', 'avatar');
    }
}
