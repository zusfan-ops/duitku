<?php

namespace App\Controllers\Api;

use App\Controllers\BarangController as WebBarangController;
use App\Models\SettingModel;

class BarangController extends ApiController
{
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $userId = $this->uid();
        $items  = WebBarangController::getItemsForUser($userId);
        $summary = WebBarangController::getSummaryForUser($userId);

        return $this->ok([
            'items'   => $items,
            'summary' => $summary,
        ]);
    }

    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?: [];
        $id     = trim($json['id'] ?? $this->request->getPost('id') ?? '');
        $name   = trim($json['name'] ?? $this->request->getPost('name') ?? '');
        $room   = trim($json['room'] ?? $json['location'] ?? $this->request->getPost('room') ?? $this->request->getPost('location') ?? '');
        $cat    = trim($json['category'] ?? $this->request->getPost('category') ?? 'Perlengkapan');
        $brand  = trim($json['brand'] ?? $this->request->getPost('brand') ?? '');
        $date   = trim($json['purchase_date'] ?? $this->request->getPost('purchase_date') ?? '');
        $price  = (float)($json['purchase_price'] ?? $this->request->getPost('purchase_price') ?? 0);
        $maint  = $json['maintenance'] ?? [];
        $warr   = $json['warranties'] ?? [];

        if (!$name || !$room) {
            return $this->fail('Nama aset dan ruangan wajib diisi.');
        }

        $items = WebBarangController::getItemsForUser($userId);
        $now = date('c');

        if ($id) {
            $found = false;
            foreach ($items as &$item) {
                if ($item['id'] === $id) {
                    $item['name']           = $name;
                    $item['room']           = $room;
                    $item['location']       = $room;
                    $item['category']       = $cat;
                    $item['brand']          = $brand;
                    $item['purchase_date']  = $date;
                    $item['purchase_price'] = $price;
                    $item['maintenance']    = is_array($maint) ? $maint : [];
                    $item['warranties']     = is_array($warr) ? $warr : [];
                    $item['updated_at']     = $now;
                    $item['updatedAt']      = $now;
                    $found = true;
                    break;
                }
            }
            if (!$found) return $this->fail('Aset tidak ditemukan.');
        } else {
            $id = uniqid('brg_', true);
            $items[] = [
                'id'             => $id,
                'name'           => $name,
                'room'           => $room,
                'location'       => $room,
                'category'       => $cat,
                'brand'          => $brand,
                'purchase_date'  => $date,
                'purchase_price' => $price,
                'item_photo'     => $json['item_photo'] ?? null,
                'itemPhoto'      => $json['itemPhoto'] ?? null,
                'location_photo' => $json['location_photo'] ?? null,
                'locationPhoto'  => $json['locationPhoto'] ?? null,
                'maintenance'    => is_array($maint) ? $maint : [],
                'warranties'     => is_array($warr) ? $warr : [],
                'created_at'     => $now,
                'createdAt'      => $now,
                'updated_at'     => $now,
                'updatedAt'      => $now,
            ];
        }

        $this->settingModel->setPref($userId, 'barang_storage_list', json_encode(array_values($items)));

        return $this->ok([
            'id'      => $id,
            'summary' => WebBarangController::getSummaryForUser($userId),
        ]);
    }

    public function delete(string $id)
    {
        $userId = $this->uid();
        $items  = WebBarangController::getItemsForUser($userId);
        $filtered = array_values(array_filter($items, fn($it) => ($it['id'] ?? '') !== $id));

        $this->settingModel->setPref($userId, 'barang_storage_list', json_encode($filtered));

        return $this->ok([
            'summary' => WebBarangController::getSummaryForUser($userId),
        ]);
    }

    public function sync()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?: [];
        $items  = $json['items'] ?? null;
        if (is_array($items)) {
            $this->settingModel->setPref($userId, 'barang_storage_list', json_encode($items));
        }

        return $this->ok([
            'items'   => WebBarangController::getItemsForUser($userId),
            'summary' => WebBarangController::getSummaryForUser($userId),
        ]);
    }
}
