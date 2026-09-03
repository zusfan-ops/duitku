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

    public static function getItemsForUser(int $userId): array
    {
        $settingModel = new SettingModel();
        $raw = $settingModel->get($userId, 'barang_storage_list', '[]');
        $items = json_decode($raw, true) ?: [];

        // Normalize items so older records have room, category, etc.
        foreach ($items as &$it) {
            if (empty($it['room']) && !empty($it['location'])) {
                $it['room'] = $it['location'];
            }
            if (empty($it['room'])) {
                $it['room'] = 'Lainnya';
            }
            if (empty($it['category'])) {
                $it['category'] = 'Perlengkapan';
            }
            if (!isset($it['maintenance']) || !is_array($it['maintenance'])) {
                $it['maintenance'] = [];
            }
            if (!isset($it['warranties']) || !is_array($it['warranties'])) {
                $it['warranties'] = [];
            }
        }
        unset($it);

        return $items;
    }

    public static function getSummaryForUser(int $userId): array
    {
        $items = self::getItemsForUser($userId);
        $rooms = [];
        $totalMaintenance = 0;
        $dueMaintenance = 0;
        $totalWarranties = 0;
        $activeWarranties = 0;
        $expiringWarranties = 0;
        $attentionList = [];

        $today = date('Y-m-d');
        $in30Days = date('Y-m-d', strtotime('+30 days'));

        foreach ($items as $item) {
            $roomName = trim($item['room'] ?? $item['location'] ?? 'Lainnya');
            if (!isset($rooms[$roomName])) {
                $rooms[$roomName] = [
                    'name'  => $roomName,
                    'count' => 0,
                    'icon'  => self::getRoomIcon($roomName),
                ];
            }
            $rooms[$roomName]['count']++;

            // Maintenance check
            if (!empty($item['maintenance'])) {
                foreach ($item['maintenance'] as $m) {
                    $totalMaintenance++;
                    $dueDate = $m['due_date'] ?? '';
                    $isDone  = !empty($m['is_done']);
                    if (!$isDone && $dueDate) {
                        if ($dueDate <= $today) {
                            $dueMaintenance++;
                            $attentionList[] = [
                                'type'       => 'maintenance',
                                'title'      => ($m['title'] ?? 'Perawatan') . ' (' . ($item['name'] ?? 'Aset') . ')',
                                'subtitle'   => 'Jatuh tempo: ' . date('d M Y', strtotime($dueDate)),
                                'due_date'   => $dueDate,
                                'is_overdue' => true,
                                'item_id'    => $item['id'] ?? '',
                            ];
                        } elseif ($dueDate <= $in30Days) {
                            $attentionList[] = [
                                'type'       => 'maintenance',
                                'title'      => ($m['title'] ?? 'Perawatan') . ' (' . ($item['name'] ?? 'Aset') . ')',
                                'subtitle'   => 'Mendekati tempo: ' . date('d M Y', strtotime($dueDate)),
                                'due_date'   => $dueDate,
                                'is_overdue' => false,
                                'item_id'    => $item['id'] ?? '',
                            ];
                        }
                    }
                }
            }

            // Warranties check
            if (!empty($item['warranties'])) {
                foreach ($item['warranties'] as $w) {
                    $totalWarranties++;
                    $expiry = $w['expiry_date'] ?? '';
                    if ($expiry) {
                        if ($expiry >= $today) {
                            $activeWarranties++;
                            if ($expiry <= $in30Days) {
                                $expiringWarranties++;
                                $attentionList[] = [
                                    'type'       => 'warranty',
                                    'title'      => 'Garansi: ' . ($item['name'] ?? 'Aset') . ' (' . ($w['provider'] ?? 'Resmi') . ')',
                                    'subtitle'   => 'Berakhir ' . date('d M Y', strtotime($expiry)),
                                    'expiry'     => $expiry,
                                    'is_overdue' => false,
                                    'item_id'    => $item['id'] ?? '',
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Calculate Home Health Score (0 - 100)
        if (empty($items)) {
            $healthScore  = 0;
            $healthStatus = 'Belum ada aset terdaftar. Mulai catat aset rumah Anda untuk memantau kondisi.';
        } else {
            $healthScore = 100;
            if ($dueMaintenance > 0) {
                $healthScore -= min($dueMaintenance * 12, 40);
            }
            if ($expiringWarranties > 0) {
                $healthScore -= min($expiringWarranties * 5, 15);
            }
            $healthScore = max(40, min(100, $healthScore));

            $healthStatus = 'Kondisi rumah & seluruh aset sangat prima.';
            if ($healthScore < 70) {
                $healthStatus = 'Beberapa perawatan aset butuh perhatian Anda.';
            } elseif ($healthScore < 85) {
                $healthStatus = 'Kondisi rumah baik, ada beberapa jadwal perawatan mendatang.';
            }
        }

        return [
            'health_score'        => $healthScore,
            'health_status'       => $healthStatus,
            'rooms'               => array_values($rooms),
            'rooms_count'         => count($rooms),
            'assets_count'        => count($items),
            'maintenance_count'   => $totalMaintenance,
            'maintenance_due'     => $dueMaintenance,
            'warranties_count'    => $totalWarranties,
            'warranties_active'   => $activeWarranties,
            'warranties_expiring' => $expiringWarranties,
            'attention'           => $attentionList,
        ];
    }

    public static function getRoomIcon(string $room): string
    {
        $r = strtolower($room);
        if (str_contains($r, 'garasi') || str_contains($r, 'garage')) return '🚗';
        if (str_contains($r, 'tamu') || str_contains($r, 'living')) return '🛋️';
        if (str_contains($r, 'dapur') || str_contains($r, 'kitchen')) return '🍽️';
        if (str_contains($r, 'tidur') || str_contains($r, 'bedroom')) return '🛏️';
        if (str_contains($r, 'luar') || str_contains($r, 'exterior') || str_contains($r, 'taman') || str_contains($r, 'garden')) return '🏡';
        if (str_contains($r, 'kerja') || str_contains($r, 'office')) return '💼';
        if (str_contains($r, 'mandi') || str_contains($r, 'bath')) return '🚿';
        if (str_contains($r, 'gudang') || str_contains($r, 'storage')) return '📦';
        return '🏠';
    }

    private function getItems(int $userId): array
    {
        return self::getItemsForUser($userId);
    }

    private function saveItems(int $userId, array $items): void
    {
        $this->settingModel->setPref($userId, 'barang_storage_list', json_encode(array_values($items)));
    }

    // GET /barang
    public function index()
    {
        $userId = (int)session()->get('user_id');
        $items  = $this->getItems($userId);
        $search = trim($this->request->getGet('q') ?? '');
        $tab    = trim($this->request->getGet('tab') ?? 'home');

        if ($search !== '') {
            $q = mb_strtolower($search);
            $items = array_values(array_filter($items, function($b) use ($q) {
                return str_contains(mb_strtolower($b['name'] ?? ''), $q) ||
                       str_contains(mb_strtolower($b['room'] ?? $b['location'] ?? ''), $q) ||
                       str_contains(mb_strtolower($b['category'] ?? ''), $q) ||
                       str_contains(mb_strtolower($b['brand'] ?? ''), $q);
            }));
        }

        $summary = self::getSummaryForUser($userId);

        if ($this->request->isAJAX() || $this->request->header('Accept')?->getValue() === 'application/json') {
            return $this->response->setJSON([
                'success' => true,
                'items'   => $items,
                'summary' => $summary,
            ]);
        }

        return view('barang/index', [
            'pageTitle' => 'My Home — Inventaris & Aset Rumah',
            'items'     => $items,
            'summary'   => $summary,
            'search'    => $search,
            'activeTab' => $tab,
            'userName'  => session()->get('user_name') ?: 'Keluarga',
        ]);
    }

    // POST /barang/store
    public function store()
    {
        $userId   = (int)session()->get('user_id');
        $id       = trim($this->request->getPost('id') ?? '');
        $name     = trim($this->request->getPost('name') ?? '');
        $room     = trim($this->request->getPost('room') ?? $this->request->getPost('location') ?? '');
        $category = trim($this->request->getPost('category') ?? 'Perlengkapan');
        $brand    = trim($this->request->getPost('brand') ?? '');
        $date     = trim($this->request->getPost('purchase_date') ?? '');
        $price    = (float)($this->request->getPost('purchase_price') ?? 0);

        if (!$name || !$room) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama aset dan ruangan wajib diisi.']);
        }

        $items = $this->getItems($userId);
        $uploadDir = FCPATH . 'uploads/barang/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $itemPhoto = null;
        $locationPhoto = null;
        $existingMaintenance = [];
        $existingWarranties = [];

        if ($id) {
            foreach ($items as $it) {
                if ($it['id'] === $id) {
                    $itemPhoto           = $it['item_photo'] ?? $it['itemPhoto'] ?? null;
                    $locationPhoto       = $it['location_photo'] ?? $it['locationPhoto'] ?? null;
                    $existingMaintenance = $it['maintenance'] ?? [];
                    $existingWarranties  = $it['warranties'] ?? [];
                    break;
                }
            }
        }

        // Photo upload handling
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

        // Maintenance JSON if posted
        $rawMaint = $this->request->getPost('maintenance_json');
        if ($rawMaint) {
            $parsed = json_decode($rawMaint, true);
            if (is_array($parsed)) $existingMaintenance = $parsed;
        }

        // Warranties JSON if posted
        $rawWarr = $this->request->getPost('warranties_json');
        if ($rawWarr) {
            $parsed = json_decode($rawWarr, true);
            if (is_array($parsed)) $existingWarranties = $parsed;
        }

        $now = date('c');
        if ($id) {
            $found = false;
            foreach ($items as &$item) {
                if ($item['id'] === $id) {
                    $item['name']           = $name;
                    $item['room']           = $room;
                    $item['location']       = $room;
                    $item['category']       = $category;
                    $item['brand']          = $brand;
                    $item['purchase_date']  = $date;
                    $item['purchase_price'] = $price;
                    $item['item_photo']     = $itemPhoto;
                    $item['itemPhoto']      = $itemPhoto;
                    $item['location_photo'] = $locationPhoto;
                    $item['locationPhoto']  = $locationPhoto;
                    $item['maintenance']    = $existingMaintenance;
                    $item['warranties']     = $existingWarranties;
                    $item['updated_at']     = $now;
                    $item['updatedAt']      = $now;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return $this->response->setJSON(['success' => false, 'message' => 'Aset tidak ditemukan.']);
            }
        } else {
            $id = uniqid('brg_', true);
            $items[] = [
                'id'             => $id,
                'name'           => $name,
                'room'           => $room,
                'location'       => $room,
                'category'       => $category,
                'brand'          => $brand,
                'purchase_date'  => $date,
                'purchase_price' => $price,
                'item_photo'     => $itemPhoto,
                'itemPhoto'      => $itemPhoto,
                'location_photo' => $locationPhoto,
                'locationPhoto'  => $locationPhoto,
                'maintenance'    => $existingMaintenance,
                'warranties'     => $existingWarranties,
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
        $userId = (int)session()->get('user_id');
        $items  = $this->getItems($userId);
        $filtered = [];

        foreach ($items as $item) {
            if ($item['id'] === $id) {
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
        $userId = (int)session()->get('user_id');
        $raw = $this->request->getPost('items') ?? $this->request->getJSON(true)['items'] ?? null;
        if ($raw) {
            $clientItems = is_array($raw) ? $raw : json_decode($raw, true);
            if (is_array($clientItems)) {
                $this->saveItems($userId, $clientItems);
            }
        }
        return $this->response->setJSON([
            'success' => true,
            'items'   => $this->getItems($userId),
            'summary' => self::getSummaryForUser($userId),
        ]);
    }
}
