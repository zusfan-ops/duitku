<?php

namespace App\Controllers;

use App\Models\SettingModel;

class BarangController extends BaseController
{
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    private function getItems(int $userId): array
    {
        $raw = $this->settingModel->get($userId, 'barang_storage_list', '[]');
        return json_decode($raw, true) ?: [];
    }

    private function saveItems(int $userId, array $items): void
    {
        $this->settingModel->setPref($userId, 'barang_storage_list', json_encode(array_values($items)));
    }

    // GET /barang
    public function index()
    {
        $userId = session()->get('user_id');
        $items  = $this->getItems($userId);
        $search = trim($this->request->getGet('q') ?? '');

        if ($search !== '') {
            $q = mb_strtolower($search);
            $items = array_values(array_filter($items, function($b) use ($q) {
                return str_contains(mb_strtolower($b['name'] ?? ''), $q) ||
                       str_contains(mb_strtolower($b['location'] ?? ''), $q);
            }));
        }

        if ($this->request->isAJAX() || $this->request->header('Accept')?->getValue() === 'application/json') {
            return $this->response->setJSON(['success' => true, 'items' => $items]);
        }

        return view('barang/index', [
            'pageTitle' => 'Manajemen Barang',
            'items'     => $items,
            'search'    => $search,
        ]);
    }

    // POST /barang/store
    public function store()
    {
        $userId   = session()->get('user_id');
        $id       = trim($this->request->getPost('id') ?? '');
        $name     = trim($this->request->getPost('name') ?? '');
        $location = trim($this->request->getPost('location') ?? '');

        if (!$name || !$location) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama barang dan lokasi penyimpanan wajib diisi.']);
        }

        $items = $this->getItems($userId);
        $uploadDir = FCPATH . 'uploads/barang/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $itemPhoto = null;
        $locationPhoto = null;

        // Existing photos if updating
        if ($id) {
            foreach ($items as $it) {
                if ($it['id'] === $id) {
                    $itemPhoto     = $it['item_photo'] ?? $it['itemPhoto'] ?? null;
                    $locationPhoto = $it['location_photo'] ?? $it['locationPhoto'] ?? null;
                    break;
                }
            }
        }

        // Handle item photo upload
        $itemFile = $this->request->getFile('item_photo');
        if ($itemFile && $itemFile->isValid() && !$itemFile->hasMoved()) {
            $newName = $itemFile->getRandomName();
            $itemFile->move($uploadDir, $newName);
            $itemPhoto = '/uploads/barang/' . $newName;
        } elseif ($this->request->getPost('item_photo_base64')) {
            $b64 = $this->request->getPost('item_photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) {
                $b64 = substr($b64, strpos($b64, ',') + 1);
                $ext = strtolower($type[1]);
                $b64Decoded = base64_decode($b64);
                if ($b64Decoded) {
                    $fileName = 'item_' . uniqid() . '.' . $ext;
                    file_put_contents($uploadDir . $fileName, $b64Decoded);
                    $itemPhoto = '/uploads/barang/' . $fileName;
                }
            }
        }

        // Handle location photo upload
        $locFile = $this->request->getFile('location_photo');
        if ($locFile && $locFile->isValid() && !$locFile->hasMoved()) {
            $newName = $locFile->getRandomName();
            $locFile->move($uploadDir, $newName);
            $locationPhoto = '/uploads/barang/' . $newName;
        } elseif ($this->request->getPost('location_photo_base64')) {
            $b64 = $this->request->getPost('location_photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) {
                $b64 = substr($b64, strpos($b64, ',') + 1);
                $ext = strtolower($type[1]);
                $b64Decoded = base64_decode($b64);
                if ($b64Decoded) {
                    $fileName = 'loc_' . uniqid() . '.' . $ext;
                    file_put_contents($uploadDir . $fileName, $b64Decoded);
                    $locationPhoto = '/uploads/barang/' . $fileName;
                }
            }
        }

        $now = date('c');
        if ($id) {
            $found = false;
            foreach ($items as &$item) {
                if ($item['id'] === $id) {
                    $item['name']           = $name;
                    $item['location']       = $location;
                    $item['item_photo']     = $itemPhoto;
                    $item['itemPhoto']      = $itemPhoto;
                    $item['location_photo'] = $locationPhoto;
                    $item['locationPhoto']  = $locationPhoto;
                    $item['updated_at']     = $now;
                    $item['updatedAt']      = $now;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return $this->response->setJSON(['success' => false, 'message' => 'Barang tidak ditemukan.']);
            }
        } else {
            $id = uniqid('brg_', true);
            $items[] = [
                'id'             => $id,
                'name'           => $name,
                'location'       => $location,
                'item_photo'     => $itemPhoto,
                'itemPhoto'      => $itemPhoto,
                'location_photo' => $locationPhoto,
                'locationPhoto'  => $locationPhoto,
                'created_at'     => $now,
                'createdAt'      => $now,
                'updated_at'     => $now,
                'updatedAt'      => $now,
            ];
        }

        $this->saveItems($userId, $items);
        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // POST /barang/delete/{id}
    public function delete(string $id)
    {
        $userId = session()->get('user_id');
        $items  = $this->getItems($userId);
        $filtered = [];

        foreach ($items as $item) {
            if ($item['id'] === $id) {
                // Delete photo files
                foreach ([$item['item_photo'] ?? null, $item['location_photo'] ?? null] as $photoPath) {
                    if ($photoPath && str_starts_with($photoPath, '/uploads/barang/')) {
                        $full = FCPATH . ltrim($photoPath, '/');
                        if (file_exists($full)) @unlink($full);
                    }
                }
            } else {
                $filtered[] = $item;
            }
        }

        $this->saveItems($userId, $filtered);
        return $this->response->setJSON(['success' => true]);
    }

    // POST /barang/sync
    public function sync()
    {
        $userId = session()->get('user_id');
        $raw = $this->request->getPost('items');
        if ($raw) {
            $clientItems = json_decode($raw, true);
            if (is_array($clientItems)) {
                $this->saveItems($userId, $clientItems);
            }
        }
        return $this->response->setJSON(['success' => true, 'items' => $this->getItems($userId)]);
    }
}
