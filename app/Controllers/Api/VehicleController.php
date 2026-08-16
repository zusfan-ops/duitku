<?php

namespace App\Controllers\Api;

use App\Models\VehicleModel;
use App\Models\VehicleLogModel;

class VehicleController extends ApiController
{
    protected VehicleModel    $vehicleModel;
    protected VehicleLogModel $logModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
        $this->logModel     = new VehicleLogModel();
    }

    // GET api/vehicles
    public function index()
    {
        $userId   = $this->uid();
        $vehicles = $this->vehicleModel->getForUser($userId);
        return $this->ok(['vehicles' => $vehicles]);
    }

    // GET api/vehicles/(:num)
    public function show(int $id)
    {
        $userId  = $this->uid();
        $vehicle = $this->vehicleModel->getDetail($id, $userId);
        if (!$vehicle) return $this->fail('Kendaraan tidak ditemukan.');

        $logs = $this->logModel->getForVehicle($id, $userId);
        return $this->ok([
            'vehicle' => $vehicle,
            'logs'    => $logs,
        ]);
    }

    // POST api/vehicles/store
    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];

        $name = trim($json['name'] ?? '');
        if (!$name) return $this->fail('Nama kendaraan wajib diisi.');

        $editId = (int)($json['id'] ?? 0) ?: null;

        $data = [
            'user_id'         => $userId,
            'name'            => $name,
            'type'            => $json['type'] ?? 'motor',
            'license_plate'   => strtoupper(trim($json['license_plate'] ?? '')) ?: null,
            'brand'           => trim($json['brand'] ?? '') ?: null,
            'model_year'      => trim($json['model_year'] ?? '') ?: null,
            'odometer'        => (int)($json['odometer'] ?? 0),
            'tax_annual_date' => $json['tax_annual_date'] ?? null,
            'tax_5year_date'  => $json['tax_5year_date'] ?? null,
        ];

        // Base64 photo upload
        if (!empty($json['photo_base64'])) {
            $uploaded = $this->saveBase64Photo($json['photo_base64']);
            if ($uploaded) $data['photo'] = $uploaded;
        }

        if ($editId) {
            $existing = $this->vehicleModel->where('id', $editId)->where('user_id', $userId)->first();
            if (!$existing) return $this->fail('Kendaraan tidak ditemukan.');
            if (!empty($data['photo']) && !empty($existing['photo']) && file_exists(FCPATH . 'uploads/vehicles/' . $existing['photo'])) {
                unlink(FCPATH . 'uploads/vehicles/' . $existing['photo']);
            }
            $this->vehicleModel->update($editId, $data);
            return $this->ok(['id' => $editId]);
        }

        $id = $this->vehicleModel->insert($data);
        if (!$id) return $this->fail('Gagal menyimpan.');
        return $this->ok(['id' => $id]);
    }

    // POST api/vehicles/delete/(:num)
    public function delete(int $id)
    {
        $userId   = $this->uid();
        $existing = $this->vehicleModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$existing) return $this->fail('Tidak ditemukan.');

        if (!empty($existing['photo']) && file_exists(FCPATH . 'uploads/vehicles/' . $existing['photo'])) {
            unlink(FCPATH . 'uploads/vehicles/' . $existing['photo']);
        }

        $this->vehicleModel->delete($id);
        return $this->ok();
    }

    // GET api/vehicles/logs?vehicle_id=X
    public function logs()
    {
        $userId    = $this->uid();
        $vehicleId = (int)($this->request->getGet('vehicle_id') ?? 0);

        if ($vehicleId > 0) {
            $logs = $this->logModel->getForVehicle($vehicleId, $userId);
        } else {
            $logs = $this->logModel->getRecentForUser($userId, 30);
        }

        return $this->ok(['logs' => $logs]);
    }

    // POST api/vehicles/logs/store
    public function storeLog()
    {
        $userId    = $this->uid();
        $json      = $this->request->getJSON(true) ?? [];
        $vehicleId = (int)($json['vehicle_id'] ?? 0);

        $vehicle = $this->vehicleModel->where('id', $vehicleId)->where('user_id', $userId)->first();
        if (!$vehicle) return $this->fail('Kendaraan tidak ditemukan.');

        $title = trim($json['title'] ?? '');
        if (!$title) return $this->fail('Judul kegiatan wajib diisi.');

        $cost = $this->amount($json['cost'] ?? 0);
        $km   = (int)($json['km'] ?? 0) ?: null;

        $data = [
            'vehicle_id' => $vehicleId,
            'user_id'    => $userId,
            'type'       => $json['type'] ?? 'service_rutin',
            'title'      => $title,
            'cost'       => $cost,
            'km'         => $km,
            'next_km'    => (int)($json['next_km'] ?? 0) ?: null,
            'next_date'  => $json['next_date'] ?? null,
            'date'       => $json['date'] ?? date('Y-m-d'),
            'workshop'   => trim($json['workshop'] ?? '') ?: null,
            'notes'      => trim($json['notes'] ?? '') ?: null,
        ];

        $id = $this->logModel->insert($data);
        if (!$id) return $this->fail('Gagal menyimpan log.');

        if ($km && $km > (int)$vehicle['odometer']) {
            $this->vehicleModel->update($vehicleId, ['odometer' => $km]);
        }

        return $this->ok(['id' => $id]);
    }

    // POST api/vehicles/logs/delete/(:num)
    public function deleteLog(int $id)
    {
        $userId = $this->uid();
        $log    = $this->logModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$log) return $this->fail('Log tidak ditemukan.');
        $this->logModel->delete($id);
        return $this->ok();
    }

    private function saveBase64Photo(string $base64): ?string
    {
        if (!preg_match('/^data:image\/(png|jpe?g|webp|gif);base64,(.+)$/i', $base64, $m)) {
            return null;
        }
        $mime = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        $data = base64_decode($m[2], true);
        if ($data === false || strlen($data) > (5 * 1024 * 1024)) return null;

        $dir = FCPATH . 'uploads/vehicles';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $name = 'veh_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $mime;
        file_put_contents($dir . '/' . $name, $data);
        return $name;
    }
}
