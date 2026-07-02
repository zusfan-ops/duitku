<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'key', 'value'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function get(int $userId, string $key, mixed $default = null): mixed
    {
        $row = $this->where('user_id', $userId)->where('key', $key)->first();
        return $row ? $row['value'] : $default;
    }

    public function setPref(int $userId, string $key, mixed $value): void
    {
        $existing = $this->where('user_id', $userId)->where('key', $key)->first();
        if ($existing) {
            $this->update($existing['id'], ['value' => $value]);
        } else {
            $this->insert(['user_id' => $userId, 'key' => $key, 'value' => $value]);
        }
    }

    public function getAll(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }
}
