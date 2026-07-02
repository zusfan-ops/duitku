<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBelanjaSync extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'data_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'data_value' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'data_key']);
        $this->forge->createTable('belanja_sync');
    }

    public function down()
    {
        $this->forge->dropTable('belanja_sync');
    }
}
