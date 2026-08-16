<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class BackupController extends BaseController
{
    public function export(): ResponseInterface
    {
        $userId = session()->get('user_id');
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

        $jsonStr = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'duitku_backup_' . date('Y-m-d_His') . '.json';

        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($jsonStr);
    }

    public function restore(): ResponseInterface
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $file = $this->request->getFile('backup_file');
        $rawJson = null;

        if ($file && $file->isValid()) {
            $rawJson = file_get_contents($file->getTempName());
        } else {
            $rawJson = $this->request->getPost('json_content');
        }

        if (!$rawJson) {
            return $this->response->setJSON(['success' => false, 'message' => 'File atau data JSON tidak ditemukan.']);
        }

        $data = json_decode($rawJson, true);
        if (!$data || !is_array($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Format file JSON tidak valid.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $importTable = function(string $table, array $rows) use ($db, $userId) {
                foreach ($rows as $row) {
                    $row['user_id'] = $userId;
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
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal memulihkan database.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Data berhasil dipulihkan.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
