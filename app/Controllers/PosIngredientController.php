<?php

namespace App\Controllers;

use App\Models\PosIngredientModel;
use App\Models\PosRecipeModel;
use App\Models\PosProductModel;
use App\Models\PosStoreProfileModel;

class PosIngredientController extends BaseController
{
    protected PosIngredientModel $ingredientModel;
    protected PosRecipeModel $recipeModel;
    protected PosProductModel $productModel;
    protected PosStoreProfileModel $storeModel;

    public function __construct()
    {
        $this->ingredientModel = new PosIngredientModel();
        $this->recipeModel     = new PosRecipeModel();
        $this->productModel    = new PosProductModel();
        $this->storeModel      = new PosStoreProfileModel();
    }

    private function getUserId(): int
    {
        return (int) (session()->get('user_id') ?? 1);
    }

    public function index()
    {
        $userId = $this->getUserId();
        $ingredients = $this->ingredientModel->getForUser($userId);
        $products    = $this->productModel->getForUser($userId);
        $store       = $this->storeModel->getForUser($userId);

        $lowStockCount = 0;
        foreach ($ingredients as $ing) {
            if ($ing['is_low_stock']) $lowStockCount++;
        }

        return view('pos/ingredients', [
            'ingredients'   => $ingredients,
            'products'      => $products,
            'store'         => $store,
            'lowStockCount' => $lowStockCount,
            'title'         => 'Stok Bahan Baku & Resep Menu (BOM)',
        ]);
    }

    public function saveIngredient()
    {
        $userId = $this->getUserId();
        $id = (int)$this->request->getPost('id');
        $name = trim((string)$this->request->getPost('name'));
        $unit = trim((string)$this->request->getPost('unit')) ?: 'gram';
        $stock = (float)$this->request->getPost('stock');
        $minStock = (float)$this->request->getPost('min_stock');
        $costPerUnit = (float)$this->request->getPost('cost_per_unit');

        if (!$name) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama bahan baku wajib diisi.']);
        }

        $data = [
            'user_id'       => $userId,
            'name'          => $name,
            'unit'          => $unit,
            'stock'         => $stock,
            'min_stock'     => $minStock,
            'cost_per_unit' => $costPerUnit,
        ];

        if ($id > 0) {
            $this->ingredientModel->where('id', $id)->where('user_id', $userId)->set($data)->update();
            $msg = 'Bahan baku berhasil diperbarui!';
        } else {
            $id = $this->ingredientModel->insert($data);
            $msg = 'Bahan baku baru berhasil ditambahkan!';
        }

        return $this->response->setJSON(['success' => true, 'message' => $msg, 'id' => $id]);
    }

    public function restock()
    {
        $userId = $this->getUserId();
        $id = (int)$this->request->getPost('id');
        $addStock = (float)$this->request->getPost('add_stock');
        $costPerUnit = $this->request->getPost('cost_per_unit') !== null ? (float)$this->request->getPost('cost_per_unit') : null;

        if ($addStock <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Jumlah restock harus lebih dari 0.']);
        }

        $ok = $this->ingredientModel->restock($id, $userId, $addStock, $costPerUnit);
        if (!$ok) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bahan baku tidak ditemukan.']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Stok bahan baku berhasil ditambah!']);
    }

    public function deleteIngredient($id = null)
    {
        $userId = $this->getUserId();
        $id = (int)$id;

        $this->ingredientModel->where('id', $id)->where('user_id', $userId)->delete();
        $this->recipeModel->where('ingredient_id', $id)->where('user_id', $userId)->delete();

        return $this->response->setJSON(['success' => true, 'message' => 'Bahan baku berhasil dihapus.']);
    }

    public function getProductRecipe($productId)
    {
        $productId = (int)$productId;
        $recipes = $this->recipeModel->getForProduct($productId);
        return $this->response->setJSON(['success' => true, 'recipes' => $recipes]);
    }

    public function saveProductRecipe($productId)
    {
        $userId = $this->getUserId();
        $productId = (int)$productId;
        $recipesRaw = $this->request->getPost('recipes');

        $recipes = is_array($recipesRaw) ? $recipesRaw : json_decode((string)$recipesRaw, true);
        if (!is_array($recipes)) $recipes = [];

        $this->recipeModel->saveProductRecipes($userId, $productId, $recipes);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Komposisi resep menu berhasil disimpan!',
        ]);
    }
}
