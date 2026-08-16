<?php

namespace App\Models;

use CodeIgniter\Model;

class PosProductModel extends Model
{
    protected $table            = 'pos_products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id', 'name', 'description', 'category', 'sku', 'cost_price',
        'selling_price', 'stock', 'min_stock_alert', 'unit',
        'icon', 'image', 'variants_json', 'is_available', 'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get all active products for POS Cashier
     */
    public function getForCashier(int $userId, ?string $category = null, ?string $search = null): array
    {
        $builder = $this->where('user_id', $userId)
                        ->where('is_active', 1);

        if ($category && $category !== 'Semua') {
            $builder->where('category', $category);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('sku', $search)
                    ->groupEnd();
        }

        return $builder->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get all products for Public Menu catalog (active & is_available = 1)
     */
    public function getForPublicMenu(int $userId, ?string $category = null, ?string $search = null): array
    {
        $builder = $this->where('user_id', $userId)
                        ->where('is_active', 1)
                        ->where('is_available', 1);

        if ($category && $category !== 'Semua') {
            $builder->where('category', $category);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('description', $search)
                    ->groupEnd();
        }

        return $builder->orderBy('category', 'ASC')->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get all categories for a user's products
     */
    public function getCategories(int $userId): array
    {
        $rows = $this->select('category')
                     ->where('user_id', $userId)
                     ->where('is_active', 1)
                     ->groupBy('category')
                     ->orderBy('category', 'ASC')
                     ->findAll();

        return array_column($rows, 'category');
    }

    /**
     * Get low stock products (stock <= min_stock_alert)
     */
    public function getLowStock(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->where('stock <= min_stock_alert')
                    ->orderBy('stock', 'ASC')
                    ->findAll();
    }

    /**
     * Deduct stock upon order checkout
     */
    public function deductStock(int $productId, int $qty): void
    {
        $this->db->query("
            UPDATE pos_products 
            SET stock = GREATEST(0, stock - ?) 
            WHERE id = ?
        ", [$qty, $productId]);
    }

    /**
     * Increase stock (e.g. restock / cancel)
     */
    public function addStock(int $productId, int $qty): void
    {
        $this->db->query("
            UPDATE pos_products 
            SET stock = stock + ? 
            WHERE id = ?
        ", [$qty, $productId]);
    }
}
