<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRtApprovalAndDocumentSchema extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('neighborhoods')) {
            $fields = [];

            if (!$this->db->fieldExists('status', 'neighborhoods')) {
                $fields['status'] = [
                    'type'       => 'ENUM',
                    'constraint' => ['pending', 'verified', 'rejected'],
                    'default'    => 'pending',
                    'after'      => 'unique_code',
                    'comment'    => 'Status persetujuan RT oleh Superadmin',
                ];
            }

            if (!$this->db->fieldExists('sk_number', 'neighborhoods')) {
                $fields['sk_number'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'status',
                    'comment'    => 'Nomor Surat Keputusan / Pengesahan RT dari RW/Kelurahan',
                ];
            }

            if (!$this->db->fieldExists('sk_document_path', 'neighborhoods')) {
                $fields['sk_document_path'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'sk_number',
                    'comment'    => 'Path file scan SK / Surat Tugas RT',
                ];
            }

            if (!$this->db->fieldExists('rejection_reason', 'neighborhoods')) {
                $fields['rejection_reason'] = [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'sk_document_path',
                ];
            }

            if (!$this->db->fieldExists('verified_at', 'neighborhoods')) {
                $fields['verified_at'] = [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'rejection_reason',
                ];
            }

            if (!$this->db->fieldExists('verified_by', 'neighborhoods')) {
                $fields['verified_by'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'verified_at',
                ];
            }

            if (!empty($fields)) {
                $this->forge->addColumn('neighborhoods', $fields);
            }
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('neighborhoods')) {
            $cols = ['status', 'sk_number', 'sk_document_path', 'rejection_reason', 'verified_at', 'verified_by'];
            foreach ($cols as $c) {
                if ($this->db->fieldExists($c, 'neighborhoods')) {
                    $this->forge->dropColumn('neighborhoods', $c);
                }
            }
        }
    }
}
