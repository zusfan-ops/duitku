<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageToTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'date',
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'image');
    }
}
