<?php

namespace App\Models;

use CodeIgniter\Model;

class PosIngredientModel extends Model
{
    protected $table            = 'pos_ingredients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id', 'name', 'unit', 'stock', 'min_stock', 'cost_per_unit'
    ];
    protected $useTimestamps    = true;

    /**
     * Get all ingredients for user with low stock flag
     */
    public function getForUser(int $userId): array
    {
        $rows = $this->where('user_id', $userId)
                     ->orderBy('name', 'ASC')
                     ->findAll();

        foreach ($rows as &$r) {
            $r['is_low_stock'] = (float)$r['stock'] <= (float)$r['min_stock'];
        }
        return $rows;
    }

    /**
     * Get low stock ingredients count
     */
    public function getLowStockCount(int $userId): int
    {
        return $this->where('user_id', $userId)
                    ->where('stock <= min_stock', null, false)
                    ->countAllResults();
    }

    /**
     * Deduct stock for an ingredient
     */
    public function deductStock(int $ingredientId, float $amount): bool
    {
        $ing = $this->find($ingredientId);
        if (!$ing) return false;

        $newStock = max(0, (float)$ing['stock'] - $amount);
        return $this->update($ingredientId, ['stock' => $newStock]);
    }

    /**
     * Restock an ingredient
     */
    public function restock(int $ingredientId, int $userId, float $addStock, ?float $costPerUnit = null): bool
    {
        $ing = $this->where('id', $ingredientId)->where('user_id', $userId)->first();
        if (!$ing) return false;

        $data = ['stock' => (float)$ing['stock'] + $addStock];
        if ($costPerUnit !== null && $costPerUnit > 0) {
            $data['cost_per_unit'] = $costPerUnit;
        }

        return $this->update($ingredientId, $data);
    }
}
