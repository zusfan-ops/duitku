<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsPastToDebts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('debts', [
            'is_past' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('debts', 'is_past');
    }
}
