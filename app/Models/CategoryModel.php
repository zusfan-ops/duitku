<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id', 'name', 'type', 'icon', 'color', 'is_default', 'sort_order'
    ];

    protected $useTimestamps = false;

    /**
     * Get all categories visible to a user:
     * - system defaults (user_id IS NULL)
     * - categories created by the user
     */
    public function getForUser(int $userId, ?string $type = null): array
    {
        $builder = $this->db->table('categories')
            ->groupStart()
                ->where('user_id', null)
                ->orWhere('user_id', $userId)
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC');

        if ($type) {
            $builder->where('type', $type);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * User can only delete non-default categories they own
     */
    public function deleteForUser(int $id, int $userId): bool
    {
        return $this->where('id', $id)
                    ->where('user_id', $userId)
                    ->where('is_default', 0)
                    ->delete();
    }
}
