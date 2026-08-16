<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class BackupController extends BaseController
{
    public function export(): ResponseInterface
    {
        $userId = $this->request->user['id'] ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();

        $data = [
            'app'        => 'DuitKu',
            'version'    => '2.2.0',
            'exported_at'=> date('c'),
            'user_id'    => $userId,
            'categories' => $db->table('categories')->where('user_id', $userId)->get()->getResultArray(),
            'wallets'    => $db->table('wallets')->where('user_id', $userId)->get()->getResultArray(),
            'transactions' => $db->table('transactions')->where('user_id', $userId)->get()->getResultArray(),
            'debts'      => $db->table('debts')->where('user_id', $userId)->get()->getResultArray(),
            'recurring'  => $db->table('recurring_transactions')->where('user_id', $userId)->get()->getResultArray(),
            'savings_goals'    => $db->table('savings_goals')->where('user_id', $userId)->get()->getResultArray(),
            'savings_deposits' => $db->table('savings_deposits')->where('user_id', $userId)->get()->getResultArray(),
            'vehicles'     => $db->table('vehicles')->where('user_id', $userId)->get()->getResultArray(),
            'vehicle_logs' => $db->table('vehicle_logs')->where('user_id', $userId)->get()->getResultArray(),
            'pos_products' => $db->table('pos_products')->where('user_id', $userId)->get()->getResultArray(),
            'pos_orders'   => $db->table('pos_orders')->where('user_id', $userId)->get()->getResultArray(),
            'pos_order_items' => $db->table('pos_order_items')->where('user_id', $userId)->get()->getResultArray(),
            'settings'   => $db->table('settings')->where('user_id', $userId)->get()->getResultArray(),
        ];

        return $this->response->setJSON([
            'success' => true,
            'backup'  => $data,
        ]);
    }

    public function restore(): ResponseInterface
    {
        $userId = $this->request->user['id'] ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $json = $this->request->getJSON(true);
        if (!$json || !isset($json['backup']) && !isset($json['categories'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Format file backup JSON tidak valid.',
            ]);
        }

        $data = $json['backup'] ?? $json;
        $db   = \Config\Database::connect();
        $db->transStart();

        try {
            // Helper to insert rows safely with user_id override
            $importTable = function(string $table, array $rows) use ($db, $userId) {
                foreach ($rows as $row) {
                    $row['user_id'] = $userId;
                    // Check if primary key exists for conflict avoidance
                    if (isset($row['id'])) {
                        $existing = $db->table($table)->where('id', $row['id'])->where('user_id', $userId)->countAllResults();
                        if ($existing > 0) {
                            $db->table($table)->where('id', $row['id'])->where('user_id', $userId)->update($row);
                            continue;
                        }
                    }
                    $db->table($table)->insert($row);
                }
            };

            if (!empty($data['categories']))   $importTable('categories', $data['categories']);
            if (!empty($data['wallets']))      $importTable('wallets', $data['wallets']);
            if (!empty($data['transactions'])) $importTable('transactions', $data['transactions']);
            if (!empty($data['debts']))        $importTable('debts', $data['debts']);
            if (!empty($data['recurring']))    $importTable('recurring_transactions', $data['recurring']);
            if (!empty($data['savings_goals']))    $importTable('savings_goals', $data['savings_goals']);
            if (!empty($data['savings_deposits'])) $importTable('savings_deposits', $data['savings_deposits']);
            if (!empty($data['vehicles']))     $importTable('vehicles', $data['vehicles']);
            if (!empty($data['vehicle_logs'])) $importTable('vehicle_logs', $data['vehicle_logs']);
            if (!empty($data['pos_products'])) $importTable('pos_products', $data['pos_products']);
            if (!empty($data['pos_orders']))   $importTable('pos_orders', $data['pos_orders']);
            if (!empty($data['pos_order_items']))$importTable('pos_order_items', $data['pos_order_items']);
            if (!empty($data['settings']))     $importTable('settings', $data['settings']);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Gagal memulihkan database (transaksi dibatalkan).',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil dipulihkan dari backup.',
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }
}
