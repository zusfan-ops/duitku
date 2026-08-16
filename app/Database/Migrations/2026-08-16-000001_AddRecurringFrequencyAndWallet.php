<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecurringFrequencyAndWallet extends Migration
{
    public function up(): void
    {
        // Add frequency column
        $this->forge->addColumn('recurring_transactions', [
            'frequency' => [
                'type'       => 'ENUM',
                'constraint' => ['weekly', 'monthly', 'yearly'],
                'default'    => 'monthly',
                'after'      => 'next_date',
            ],
        ]);

        // Add wallet_id column
        $this->forge->addColumn('recurring_transactions', [
            'wallet_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'user_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('recurring_transactions', 'frequency');
        $this->forge->dropColumn('recurring_transactions', 'wallet_id');
    }
}
