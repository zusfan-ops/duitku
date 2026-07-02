<?php

namespace App\Models;

use CodeIgniter\Model;

class BelanjaModel extends Model
{
    protected $table         = 'belanja_sync';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'data_key', 'data_value', 'updated_at'];
    protected $useTimestamps = false;

    public function getAll(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function upsert(int $userId, string $key, ?string $value): void
    {
        $now      = date('Y-m-d H:i:s');
        $existing = $this->where('user_id', $userId)->where('data_key', $key)->first();
        if ($existing) {
            $this->where('user_id', $userId)
                 ->where('data_key', $key)
                 ->set(['data_value' => $value, 'updated_at' => $now])
                 ->update();
        } else {
            $this->insert([
                'user_id'    => $userId,
                'data_key'   => $key,
                'data_value' => $value,
                'updated_at' => $now,
            ]);
        }
    }
}
