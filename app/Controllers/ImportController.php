<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\CategoryModel;

class ImportController extends BaseController
{
    protected TransactionModel $txModel;
    protected CategoryModel    $catModel;

    public function __construct()
    {
        $this->txModel  = new TransactionModel();
        $this->catModel = new CategoryModel();
    }

    // POST /import/csv
    // Expected CSV columns (header row required):
    //   Tanggal, Tipe, Kategori, Catatan, Jumlah
    // Tipe values: pemasukan / income / masuk  OR  pengeluaran / expense / keluar
    public function csv()
    {
        if (!$this->request->is('post')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Method not allowed.']);
        }

        $userId = session()->get('user_id');
        $file   = $this->request->getFile('csv_file');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak valid.']);
        }

        $mime = $file->getMimeType();
        if (!in_array($mime, ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])) {
            // Also allow by extension
            if (strtolower($file->getExtension()) !== 'csv') {
                return $this->response->setJSON(['success' => false, 'message' => 'File harus berformat CSV.']);
            }
        }

        $path    = $file->getTempName();
        $handle  = fopen($path, 'r');

        if (!$handle) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal membaca file.']);
        }

        // Read header row
        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            return $this->response->setJSON(['success' => false, 'message' => 'File CSV kosong atau tidak memiliki header.']);
        }

        // Normalize header keys
        $header = array_map(fn($h) => mb_strtolower(trim($h)), $header);
        $colMap = [];
        $aliases = [
            'tanggal' => ['tanggal', 'date', 'tgl'],
            'tipe'    => ['tipe', 'type', 'jenis'],
            'kategori'=> ['kategori', 'category', 'cat'],
            'catatan' => ['catatan', 'keterangan', 'note', 'notes', 'description'],
            'jumlah'  => ['jumlah', 'amount', 'nominal', 'total'],
        ];

        foreach ($aliases as $field => $names) {
            foreach ($names as $name) {
                $idx = array_search($name, $header);
                if ($idx !== false) { $colMap[$field] = $idx; break; }
            }
        }

        if (!isset($colMap['tanggal'], $colMap['tipe'], $colMap['jumlah'])) {
            fclose($handle);
            return $this->response->setJSON(['success' => false, 'message' => 'Header CSV harus memiliki kolom: Tanggal, Tipe, Jumlah.']);
        }

        // Load user categories for matching
        $categories = $this->catModel->getForUser($userId);
        $catByName  = [];
        foreach ($categories as $cat) {
            $catByName[mb_strtolower($cat['name'])] = $cat;
        }

        $imported = 0;
        $failed   = 0;
        $rows     = [];

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (empty(array_filter($row))) continue; // skip blank lines

            $rawDate   = trim($row[$colMap['tanggal']] ?? '');
            $rawType   = mb_strtolower(trim($row[$colMap['tipe']] ?? ''));
            $rawAmount = trim($row[$colMap['jumlah']] ?? '');
            $rawCat    = isset($colMap['kategori']) ? mb_strtolower(trim($row[$colMap['kategori']] ?? '')) : '';
            $rawNote   = isset($colMap['catatan'])  ? trim($row[$colMap['catatan']] ?? '') : '';

            // Parse date (accept Y-m-d, d/m/Y, d-m-Y)
            $date = null;
            foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $fmt) {
                $dt = \DateTime::createFromFormat($fmt, $rawDate);
                if ($dt && $dt->format($fmt) === $rawDate) {
                    $date = $dt->format('Y-m-d'); break;
                }
            }
            if (!$date) { $failed++; continue; }

            // Parse type
            $type = null;
            if (in_array($rawType, ['pemasukan', 'income', 'masuk', 'in'])) $type = 'income';
            elseif (in_array($rawType, ['pengeluaran', 'expense', 'keluar', 'out', 'belanja'])) $type = 'expense';
            if (!$type) { $failed++; continue; }

            // Parse amount
            $amount = (float)str_replace(['.', ' '], '', str_replace(',', '.', $rawAmount));
            if ($amount <= 0) { $failed++; continue; }

            // Match category
            $catId = null;
            if ($rawCat && isset($catByName[$rawCat])) {
                $catId = $catByName[$rawCat]['id'];
            } else {
                // Find first default category of matching type
                foreach ($categories as $cat) {
                    if ($cat['type'] === $type) { $catId = $cat['id']; break; }
                }
            }
            if (!$catId) { $failed++; continue; }

            $rows[] = [
                'user_id'     => $userId,
                'category_id' => $catId,
                'type'        => $type,
                'amount'      => $amount,
                'note'        => $rawNote,
                'date'        => $date,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
            $imported++;
        }
        fclose($handle);

        if (!empty($rows)) {
            $db = \Config\Database::connect();
            $db->table('transactions')->insertBatch($rows);
        }

        return $this->response->setJSON([
            'success'  => true,
            'imported' => $imported,
            'failed'   => $failed,
        ]);
    }
}
