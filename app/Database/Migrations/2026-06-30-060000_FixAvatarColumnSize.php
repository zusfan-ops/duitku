<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixAvatarColumnSize extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `avatar` VARCHAR(255) DEFAULT NULL");

        // Fix any truncated avatar values by regenerating from name
        $users = $this->db->query("SELECT id, name, avatar FROM users")->getResultArray();
        foreach ($users as $u) {
            $decoded = json_decode($u['avatar'] ?? '', true);
            if (!$decoded || !isset($decoded['initials'])) {
                $colors = ['#2D5A27', '#1E40AF', '#7C3AED', '#B45309', '#BE185D', '#0F766E'];
                $initials   = strtoupper(substr($u['name'], 0, 2));
                $colorIndex = ord($u['name'][0]) % count($colors);
                $avatar     = json_encode(['initials' => $initials, 'color' => $colors[$colorIndex]]);
                $this->db->query("UPDATE users SET avatar = ? WHERE id = ?", [$avatar, $u['id']]);
            }
        }
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `avatar` VARCHAR(10) DEFAULT NULL");
    }
}
