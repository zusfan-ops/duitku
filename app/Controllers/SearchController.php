<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class SearchController extends BaseController
{
    public function index(): ResponseInterface
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $query = trim($this->request->getGet('q') ?? '');
        if (strlen($query) < 2) {
            return $this->response->setJSON([
                'query'   => $query,
                'results' => [
                    'transactions' => [],
                    'pos_products' => [],
                    'debts'        => [],
                    'vehicles'     => [],
                    'barang'       => [],
                ],
                'total' => 0,
            ]);
        }

        $db = \Config\Database::connect();
        $like = '%' . $query . '%';

        $transactions = [];
        try {
            $transactions = $db->query("
                SELECT t.id, t.type, t.amount, t.description, t.date, c.name AS category_name, c.icon AS category_icon, c.color AS category_color, w.name AS wallet_name
                FROM transactions t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN wallets w ON w.id = t.wallet_id
                WHERE t.user_id = ? AND (t.description LIKE ? OR c.name LIKE ? OR t.amount LIKE ?)
                ORDER BY t.date DESC
                LIMIT 8
            ", [$userId, $like, $like, $like])->getResultArray();
        } catch (\Throwable $e) {}

        // 2. POS Products
        $posProducts = [];
        try {
            $posProducts = $db->query("
                SELECT id, name, category, selling_price, cost_price, stock, unit, icon
                FROM pos_products
                WHERE user_id = ? AND (name LIKE ? OR category LIKE ?)
                ORDER BY name ASC
                LIMIT 8
            ", [$userId, $like, $like])->getResultArray();
        } catch (\Throwable $e) {}

        // 3. Debts & Kasbon
        $debts = [];
        try {
            $debts = $db->query("
                SELECT id, type, person, amount, phone, due_date, is_settled, description
                FROM debts
                WHERE user_id = ? AND (person LIKE ? OR phone LIKE ? OR description LIKE ?)
                ORDER BY due_date ASC
                LIMIT 8
            ", [$userId, $like, $like, $like])->getResultArray();
        } catch (\Throwable $e) {}

        // 4. Vehicles
        $vehicles = [];
        try {
            $vehicles = $db->query("
                SELECT id, name, type, plate_number, year, odometer_km
                FROM vehicles
                WHERE user_id = ? AND (name LIKE ? OR plate_number LIKE ? OR type LIKE ?)
                ORDER BY name ASC
                LIMIT 8
            ", [$userId, $like, $like, $like])->getResultArray();
        } catch (\Throwable $e) {}

        // 5. Barang
        $barangItems = [];
        $settingModel = new \App\Models\SettingModel();
        $barangJson = $settingModel->get($userId, 'barang_items', '[]');
        $allBarang = json_decode($barangJson, true) ?: [];
        foreach ($allBarang as $b) {
            $name = $b['nama'] ?? $b['name'] ?? '';
            $loc = $b['lokasi'] ?? $b['location'] ?? '';
            $cat = $b['kategori'] ?? $b['category'] ?? '';
            if (stripos($name, $query) !== false || stripos($loc, $query) !== false || stripos($cat, $query) !== false) {
                $barangItems[] = $b;
                if (count($barangItems) >= 8) break;
            }
        }

        $total = count($transactions) + count($posProducts) + count($debts) + count($vehicles) + count($barangItems);

        return $this->response->setJSON([
            'query'   => $query,
            'results' => [
                'transactions' => $transactions,
                'pos_products' => $posProducts,
                'debts'        => $debts,
                'vehicles'     => $vehicles,
                'barang'       => $barangItems,
            ],
            'total' => $total,
        ]);
    }
}
