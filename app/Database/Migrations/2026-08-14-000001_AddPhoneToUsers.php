<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhoneToUsers extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('phone', 'users')) {
            $this->forge->addColumn('users', [
                'phone' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'email',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('phone', 'users')) {
            $this->forge->dropColumn('users', 'phone');
        }
    }
}
