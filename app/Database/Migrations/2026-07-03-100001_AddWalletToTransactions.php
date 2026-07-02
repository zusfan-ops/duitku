<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWalletToTransactions extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('transactions', [
            'wallet_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'user_id',
            ],
        ]);

        // Assign all existing transactions to the user's default wallet
        $db = \Config\Database::connect();
        $wallets = $db->table('wallets')->where('is_default', 1)->get()->getResultArray();
        foreach ($wallets as $w) {
            $db->query(
                "UPDATE transactions SET wallet_id = ? WHERE user_id = ? AND wallet_id IS NULL",
                [$w['id'], $w['user_id']]
            );
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('transactions', 'wallet_id');
    }
}
