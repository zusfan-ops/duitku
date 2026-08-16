<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\VehicleLogModel;
use App\Models\SettingModel;

class VehicleController extends BaseController
{
    protected VehicleModel    $vehicleModel;
    protected VehicleLogModel $logModel;
    protected SettingModel    $settingModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
        $this->logModel     = new VehicleLogModel();
        $this->settingModel = new SettingModel();
    }

    // GET /kendaraan
    public function index()
    {
        $userId   = session()->get('user_id');
        $symbol   = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $vehicles = $this->vehicleModel->getForUser($userId);
        $logs     = $this->logModel->getRecentForUser($userId, 15);

        // Check for upcoming taxes (within 30 days or overdue)
        $taxAlerts = [];
        $today = date('Y-m-d');
        foreach ($vehicles as $v) {
            if (!empty($v['tax_annual_date'])) {
                $days = (int)ceil((strtotime($v['tax_annual_date']) - time()) / 86400);
                if ($days <= 30) {
                    $taxAlerts[] = [
                        'vehicle_name'  => $v['name'],
                        'license_plate' => $v['license_plate'],
                        'type'          => 'Pajak Tahunan (PKB)',
                        'due_date'      => $v['tax_annual_date'],
                        'days_left'     => $days,
                    ];
                }
            }
            if (!empty($v['tax_5year_date'])) {
                $days = (int)ceil((strtotime($v['tax_5year_date']) - time()) / 86400);
                if ($days <= 30) {
                    $taxAlerts[] = [
                        'vehicle_name'  => $v['name'],
                        'license_plate' => $v['license_plate'],
                        'type'          => 'Pajak 5 Tahunan (Ganti Plat)',
                        'due_date'      => $v['tax_5year_date'],
                        'days_left'     => $days,
                    ];
                }
            }
        }

        return view('kendaraan/index', [
            'pageTitle' => 'Data & Servis Kendaraan',
            'vehicles'  => $vehicles,
            'logs'      => $logs,
            'taxAlerts' => $taxAlerts,
            'symbol'    => $symbol,
        ]);
    }

    // GET /kendaraan/(:num)
    public function show(int $id)
    {
        $userId  = session()->get('user_id');
        $vehicle = $this->vehicleModel->getDetail($id, $userId);
        if (!$vehicle) {
            return redirect()->to('/kendaraan')->with('error', 'Kendaraan tidak ditemukan.');
        }

        $logs   = $this->logModel->getForVehicle($id, $userId);
        $symbol = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        return view('kendaraan/show', [
            'pageTitle' => $vehicle['name'] . ' — Detail Servis',
            'vehicle'   => $vehicle,
            'logs'      => $logs,
            'symbol'    => $symbol,
        ]);
    }

    // POST /kendaraan/store
    public function store()
    {
        $userId = session()->get('user_id');
        $editId = (int)($this->request->getPost('id') ?: 0) ?: null;

        $name = trim($this->request->getPost('name') ?? '');
        if (!$name) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama kendaraan wajib diisi.']);
        }

        $data = [
            'user_id'         => $userId,
            'name'            => $name,
            'type'            => $this->request->getPost('type') ?: 'motor',
            'license_plate'   => strtoupper(trim($this->request->getPost('license_plate') ?? '')) ?: null,
            'brand'           => trim($this->request->getPost('brand') ?? '') ?: null,
            'model_year'      => trim($this->request->getPost('model_year') ?? '') ?: null,
            'odometer'        => (int)str_replace(['.', ','], '', $this->request->getPost('odometer') ?? '0'),
            'tax_annual_date' => $this->request->getPost('tax_annual_date') ?: null,
            'tax_5year_date'  => $this->request->getPost('tax_5year_date') ?: null,
        ];

        // Handle Photo Upload
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dir = FCPATH . 'uploads/vehicles';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $newName = $file->getRandomName();
            $file->move($dir, $newName);
            $data['photo'] = $newName;
        }

        if ($editId) {
            $existing = $this->vehicleModel->where('id', $editId)->where('user_id', $userId)->first();
            if (!$existing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Kendaraan tidak ditemukan.']);
            }
            if (!empty($data['photo']) && !empty($existing['photo']) && file_exists(FCPATH . 'uploads/vehicles/' . $existing['photo'])) {
                unlink(FCPATH . 'uploads/vehicles/' . $existing['photo']);
            }
            $this->vehicleModel->update($editId, $data);
            return $this->response->setJSON(['success' => true, 'id' => $editId]);
        }

        $id = $this->vehicleModel->insert($data);
        return $this->response->setJSON(['success' => (bool)$id, 'id' => $id]);
    }

    // POST /kendaraan/delete/{id}
    public function delete(int $id)
    {
        $userId   = session()->get('user_id');
        $existing = $this->vehicleModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);
        }

        if (!empty($existing['photo']) && file_exists(FCPATH . 'uploads/vehicles/' . $existing['photo'])) {
            unlink(FCPATH . 'uploads/vehicles/' . $existing['photo']);
        }

        $this->vehicleModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    // POST /kendaraan/log/store
    public function storeLog()
    {
        $userId    = session()->get('user_id');
        $vehicleId = (int)($this->request->getPost('vehicle_id') ?: 0);
        $vehicle   = $this->vehicleModel->where('id', $vehicleId)->where('user_id', $userId)->first();

        if (!$vehicle) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kendaraan tidak ditemukan.']);
        }

        $title = trim($this->request->getPost('title') ?? '');
        $cost  = (float)str_replace(['.', ','], ['', '.'], $this->request->getPost('cost') ?? '0');
        $km    = (int)str_replace(['.', ','], '', $this->request->getPost('km') ?? '0') ?: null;

        if (!$title) {
            return $this->response->setJSON(['success' => false, 'message' => 'Judul kegiatan wajib diisi.']);
        }

        $data = [
            'vehicle_id' => $vehicleId,
            'user_id'    => $userId,
            'type'       => $this->request->getPost('type') ?: 'service_rutin',
            'title'      => $title,
            'cost'       => $cost,
            'km'         => $km,
            'next_km'    => (int)str_replace(['.', ','], '', $this->request->getPost('next_km') ?? '0') ?: null,
            'next_date'  => $this->request->getPost('next_date') ?: null,
            'date'       => $this->request->getPost('date') ?: date('Y-m-d'),
            'workshop'   => trim($this->request->getPost('workshop') ?? '') ?: null,
            'notes'      => trim($this->request->getPost('notes') ?? '') ?: null,
        ];

        $id = $this->logModel->insert($data);

        // Update vehicle odometer if new km is greater
        if ($km && $km > (int)$vehicle['odometer']) {
            $this->vehicleModel->update($vehicleId, ['odometer' => $km]);
        }

        return $this->response->setJSON(['success' => (bool)$id, 'id' => $id]);
    }

    // POST /kendaraan/log/delete/{id}
    public function deleteLog(int $id)
    {
        $userId = session()->get('user_id');
        $log    = $this->logModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$log) {
            return $this->response->setJSON(['success' => false, 'message' => 'Log tidak ditemukan.']);
        }
        $this->logModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }
}
