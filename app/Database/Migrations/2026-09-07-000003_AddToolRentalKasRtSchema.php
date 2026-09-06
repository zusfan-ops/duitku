<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddToolRentalKasRtSchema extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan pengaturan tarif Kas RT peminjaman alat pada tabel neighborhoods
        if ($this->db->tableExists('neighborhoods')) {
            $fields = [];
            if (!$this->db->fieldExists('tool_rental_fee', 'neighborhoods')) {
                $fields['tool_rental_fee'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 2000.00,
                    'after'      => 'max_borrow_limit_domisili',
                    'comment'    => 'Biaya kas RT per peminjaman alat (default Rp 2.000)',
                ];
            }
            if (!empty($fields)) {
                $this->forge->addColumn('neighborhoods', $fields);
            }
        }

        // 2. Tambahkan kolom rt_fee_amount dan rt_fee_transaction_id pada tabel tool_rentals
        if ($this->db->tableExists('tool_rentals')) {
            $fields = [];
            if (!$this->db->fieldExists('rt_fee_amount', 'tool_rentals')) {
                $fields['rt_fee_amount'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 2000.00,
                    'after'      => 'deposit_amount',
                    'comment'    => 'Nominal biaya kas RT saat peminjaman dibuat',
                ];
            }
            if (!$this->db->fieldExists('rt_fee_transaction_id', 'tool_rentals')) {
                $fields['rt_fee_transaction_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'refund_transaction_id',
                    'comment'    => 'ID transaksi ledger pemasukan kas RT',
                ];
            }
            if (!empty($fields)) {
                $this->forge->addColumn('tool_rentals', $fields);
            }
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('neighborhoods') && $this->db->fieldExists('tool_rental_fee', 'neighborhoods')) {
            $this->forge->dropColumn('neighborhoods', 'tool_rental_fee');
        }

        if ($this->db->tableExists('tool_rentals')) {
            $cols = [];
            if ($this->db->fieldExists('rt_fee_amount', 'tool_rentals')) {
                $cols[] = 'rt_fee_amount';
            }
            if ($this->db->fieldExists('rt_fee_transaction_id', 'tool_rentals')) {
                $cols[] = 'rt_fee_transaction_id';
            }
            if (!empty($cols)) {
                $this->forge->dropColumn('tool_rentals', $cols);
            }
        }
    }
}
