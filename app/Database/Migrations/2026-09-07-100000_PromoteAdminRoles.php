<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PromoteAdminRoles extends Migration
{
    public function up(): void
    {
        // Pastikan kolom role ada sebelum mengupdate (aman jika belum ada).
        if (!$this->db->fieldExists('role', 'users')) {
            return;
        }

        // Berikan akses Admin Panel ke akun admin utama dan akun superadmin test.
        // Peran 'administrator' mencakup hak Ketua RT (semua pengecekan RT menerima
        // 'administrator'), jadi aman untuk mempromosikan meskipun saat ini 'rt_admin'.
        // Kami tidak pernah menurunkan peran yang sudah berupa 'administrator'.
        $promoteSql = "
            UPDATE users
            SET role = 'administrator'
            WHERE role NOT IN ('administrator', 'admin')
              AND (
                    email = 'zusfan@gmail.com'
                    OR email LIKE 'superadmin\\_%@duitku.test'
              )
        ";
        $this->db->query($promoteSql);
    }

    public function down(): void
    {
        // Tidak ada rollback data yang aman; cukup tidak melakukan apa-apa.
    }
}
