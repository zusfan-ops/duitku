<?php

namespace App\Models;

use CodeIgniter\Model;

class PosRecipeModel extends Model
{
    protected $table            = 'pos_recipes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id', 'product_id', 'ingredient_id', 'amount_needed'
    ];
    protected $useTimestamps    = true;

    /**
     * Get recipe (ingredients list) for a specific product
     */
    public function getForProduct(int $productId): array
    {
        $db = \Config\Database::connect();
        return $db->table('pos_recipes r')
            ->select('r.*, i.name AS ingredient_name, i.unit, i.stock, i.cost_per_unit, (r.amount_needed * i.cost_per_unit) AS subtotal_cost')
            ->join('pos_ingredients i', 'i.id = r.ingredient_id', 'left')
            ->where('r.product_id', $productId)
            ->get()
            ->getResultArray();
    }

    /**
     * Save/update recipe composition for a product
     * $recipes = [ ['ingredient_id' => 1, 'amount_needed' => 18], ... ]
     */
    public function saveProductRecipes(int $userId, int $productId, array $recipes): void
    {
        $this->where('user_id', $userId)->where('product_id', $productId)->delete();

        foreach ($recipes as $r) {
            $ingId = (int)($r['ingredient_id'] ?? 0);
            $amount = (float)($r['amount_needed'] ?? 0);
            if ($ingId > 0 && $amount > 0) {
                $this->insert([
                    'user_id'       => $userId,
                    'product_id'    => $productId,
                    'ingredient_id' => $ingId,
                    'amount_needed' => $amount,
                ]);
            }
        }
    }

    /**
     * Deduct raw ingredients automatically when an order is placed/completed
     */
    public function deductForOrder(int $orderId, int $userId): void
    {
        $db = \Config\Database::connect();
        $items = $db->table('pos_order_items')
            ->where('order_id', $orderId)
            ->get()
            ->getResultArray();

        $ingredientModel = new PosIngredientModel();

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $qty = (int)$item['qty'];

            $recipes = $this->where('product_id', $productId)->findAll();
            foreach ($recipes as $rec) {
                $totalToDeduct = (float)$rec['amount_needed'] * $qty;
                $ingredientModel->deductStock((int)$rec['ingredient_id'], $totalToDeduct);
            }
        }
    }
}
