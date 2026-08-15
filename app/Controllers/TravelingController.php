<?php

namespace App\Controllers;

use App\Models\SettingModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;
use App\Models\CategoryModel;

class TravelingController extends BaseController
{
    protected SettingModel     $settingModel;
    protected TransactionModel $txModel;
    protected WalletModel      $walletModel;
    protected CategoryModel    $catModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
        $this->txModel      = new TransactionModel();
        $this->walletModel  = new WalletModel();
        $this->catModel     = new CategoryModel();
    }

    private function getTrips(int $userId): array
    {
        $raw = $this->settingModel->get($userId, 'travel_trips', '[]');
        return json_decode($raw, true) ?: [];
    }

    private function saveTrips(int $userId, array $trips): void
    {
        $this->settingModel->setPref($userId, 'travel_trips', json_encode(array_values($trips)));
    }

    private function getItems(int $userId): array
    {
        $raw = $this->settingModel->get($userId, 'travel_items', '[]');
        return json_decode($raw, true) ?: [];
    }

    private function saveItems(int $userId, array $items): void
    {
        $this->settingModel->setPref($userId, 'travel_items', json_encode(array_values($items)));
    }

    private function getTickets(int $userId): array
    {
        $raw = $this->settingModel->get($userId, 'travel_tickets', '[]');
        return json_decode($raw, true) ?: [];
    }

    private function saveTickets(int $userId, array $tickets): void
    {
        $this->settingModel->setPref($userId, 'travel_tickets', json_encode(array_values($tickets)));
    }

    // GET /traveling
    public function index()
    {
        $userId  = session()->get('user_id');
        $trips   = $this->getTrips($userId);
        $symbol  = $this->settingModel->get($userId, 'currency_symbol', 'Rp');

        // Calculate total expense for each trip from transactions table
        $db = \Config\Database::connect();
        foreach ($trips as &$t) {
            $tag = '[Trip:' . $t['id'] . ']';
            $row = $db->table('transactions')
                ->selectSum('amount')
                ->where('user_id', $userId)
                ->where('type', 'expense')
                ->like('note', $tag, 'after')
                ->get()
                ->getRowArray();
            $t['total_cost'] = (float)($row['amount'] ?? 0);
        }
        unset($t);

        if ($this->request->isAJAX() || $this->request->header('Accept')?->getValue() === 'application/json') {
            return $this->response->setJSON(['success' => true, 'trips' => $trips]);
        }

        return view('traveling/index', [
            'pageTitle' => 'Traveling & Trip',
            'trips'     => $trips,
            'symbol'    => $symbol,
        ]);
    }

    // GET /traveling/(:segment)
    public function show(string $id)
    {
        $userId  = session()->get('user_id');
        $trips   = $this->getTrips($userId);
        $trip    = null;
        foreach ($trips as $t) {
            if ($t['id'] === $id) {
                $trip = $t;
                break;
            }
        }

        if (!$trip) {
            return redirect()->to('/traveling')->with('error', 'Trip tidak ditemukan.');
        }

        $symbol     = $this->settingModel->get($userId, 'currency_symbol', 'Rp');
        $allItems   = $this->getItems($userId);
        $tripItems  = array_values(array_filter($allItems, fn($i) => ($i['trip_id'] ?? '') === $id));

        $allTickets = $this->getTickets($userId);
        $tripTickets = array_values(array_filter($allTickets, fn($tk) => ($tk['trip_id'] ?? '') === $id));

        // Get transactions with [Trip:id]
        $tag = '[Trip:' . $id . ']';
        $transactions = $this->txModel
            ->select('transactions.*, categories.name as category_name, categories.icon as category_icon, categories.color as category_color, wallets.name as wallet_name')
            ->join('categories', 'categories.id = transactions.category_id', 'left')
            ->join('wallets', 'wallets.id = transactions.wallet_id', 'left')
            ->where('transactions.user_id', $userId)
            ->like('transactions.note', $tag, 'after')
            ->orderBy('transactions.date', 'DESC')
            ->orderBy('transactions.id', 'DESC')
            ->findAll();

        $totalCost = 0;
        foreach ($transactions as $tx) {
            if ($tx['type'] === 'expense') {
                $totalCost += (float)$tx['amount'];
            }
        }
        $trip['total_cost'] = $totalCost;

        $walletData = $this->walletModel->getWithBalances($userId);
        $categories = $this->catModel->getForUser($userId);

        return view('traveling/detail', [
            'pageTitle'    => $trip['destination'] . ' — Traveling',
            'trip'         => $trip,
            'items'        => $tripItems,
            'tickets'      => $tripTickets,
            'transactions' => $transactions,
            'symbol'       => $symbol,
            'wallets'      => $walletData['wallets'],
            'categories'   => $categories,
        ]);
    }

    // POST /traveling/sync (handles create/update/delete of trips, items, tickets)
    public function sync()
    {
        $userId = session()->get('user_id');
        $action = $this->request->getPost('action');

        if ($action === 'save_trip') {
            $id          = trim($this->request->getPost('id') ?? '');
            $destination = trim($this->request->getPost('destination') ?? '');
            $description = trim($this->request->getPost('description') ?? '');
            $startDate   = trim($this->request->getPost('start_date') ?? '');
            $endDate     = trim($this->request->getPost('end_date') ?? '');
            $budget      = (float)($this->request->getPost('budget') ?? 0);

            if (!$destination || !$startDate) {
                return $this->response->setJSON(['success' => false, 'message' => 'Destinasi dan tanggal mulai wajib diisi.']);
            }

            $trips = $this->getTrips($userId);
            if ($id) {
                foreach ($trips as &$t) {
                    if ($t['id'] === $id) {
                        $t['destination'] = $destination;
                        $t['description'] = $description;
                        $t['start_date']  = $startDate;
                        $t['end_date']    = $endDate;
                        $t['budget']      = $budget;
                        break;
                    }
                }
            } else {
                $id = uniqid('trip_', true);
                $trips[] = [
                    'id'          => $id,
                    'destination' => $destination,
                    'description' => $description,
                    'start_date'  => $startDate,
                    'end_date'    => $endDate,
                    'budget'      => $budget,
                    'created_at'  => date('c'),
                ];
            }
            $this->saveTrips($userId, $trips);
            return $this->response->setJSON(['success' => true, 'id' => $id]);
        }

        if ($action === 'delete_trip') {
            $id = trim($this->request->getPost('id') ?? '');
            $trips = array_filter($this->getTrips($userId), fn($t) => $t['id'] !== $id);
            $this->saveTrips($userId, $trips);

            // Clean associated items and tickets
            $items = array_filter($this->getItems($userId), fn($i) => ($i['trip_id'] ?? '') !== $id);
            $this->saveItems($userId, $items);

            $tickets = array_filter($this->getTickets($userId), fn($tk) => ($tk['trip_id'] ?? '') !== $id);
            $this->saveTickets($userId, $tickets);

            return $this->response->setJSON(['success' => true]);
        }

        // Checklist Items
        if ($action === 'save_item') {
            $id       = trim($this->request->getPost('id') ?? '');
            $tripId   = trim($this->request->getPost('trip_id') ?? '');
            $name     = trim($this->request->getPost('name') ?? '');
            $isPacked = (int)($this->request->getPost('is_packed') ?? 0);

            if (!$tripId || !$name) {
                return $this->response->setJSON(['success' => false, 'message' => 'Nama barang wajib diisi.']);
            }

            $items = $this->getItems($userId);
            if ($id) {
                foreach ($items as &$it) {
                    if ($it['id'] === $id) {
                        $it['name']      = $name;
                        $it['is_packed'] = $isPacked;
                        break;
                    }
                }
            } else {
                $id = uniqid('item_', true);
                $items[] = [
                    'id'        => $id,
                    'trip_id'   => $tripId,
                    'name'      => $name,
                    'is_packed' => $isPacked,
                ];
            }
            $this->saveItems($userId, $items);
            return $this->response->setJSON(['success' => true, 'id' => $id]);
        }

        if ($action === 'toggle_item') {
            $id    = trim($this->request->getPost('id') ?? '');
            $items = $this->getItems($userId);
            foreach ($items as &$it) {
                if ($it['id'] === $id) {
                    $it['is_packed'] = empty($it['is_packed']) ? 1 : 0;
                    break;
                }
            }
            $this->saveItems($userId, $items);
            return $this->response->setJSON(['success' => true]);
        }

        if ($action === 'delete_item') {
            $id    = trim($this->request->getPost('id') ?? '');
            $items = array_filter($this->getItems($userId), fn($i) => $i['id'] !== $id);
            $this->saveItems($userId, $items);
            return $this->response->setJSON(['success' => true]);
        }

        // Tickets
        if ($action === 'save_ticket') {
            $id            = trim($this->request->getPost('id') ?? '');
            $tripId        = trim($this->request->getPost('trip_id') ?? '');
            $type          = trim($this->request->getPost('type') ?? 'flight');
            $code          = trim($this->request->getPost('code') ?? '');
            $qrData        = trim($this->request->getPost('qr_data') ?? '');
            $passengerName = trim($this->request->getPost('passenger_name') ?? '');
            $departure     = trim($this->request->getPost('departure') ?? '');
            $arrival       = trim($this->request->getPost('arrival') ?? '');
            $departureTime = trim($this->request->getPost('departure_time') ?? '');
            $seat          = trim($this->request->getPost('seat') ?? '');
            $notes         = trim($this->request->getPost('notes') ?? '');

            if (!$tripId || !$departure) {
                return $this->response->setJSON(['success' => false, 'message' => 'Data tiket tidak lengkap.']);
            }

            $tickets = $this->getTickets($userId);
            if ($id) {
                foreach ($tickets as &$tk) {
                    if ($tk['id'] === $id) {
                        $tk = [
                            'id'             => $id,
                            'trip_id'        => $tripId,
                            'type'           => $type,
                            'code'           => $code,
                            'qr_data'        => $qrData ?: $code,
                            'passenger_name' => $passengerName,
                            'departure'      => $departure,
                            'arrival'        => $arrival,
                            'departure_time' => $departureTime,
                            'seat'           => $seat,
                            'notes'          => $notes,
                        ];
                        break;
                    }
                }
            } else {
                $id = uniqid('tkt_', true);
                $tickets[] = [
                    'id'             => $id,
                    'trip_id'        => $tripId,
                    'type'           => $type,
                    'code'           => $code,
                    'qr_data'        => $qrData ?: $code,
                    'passenger_name' => $passengerName,
                    'departure'      => $departure,
                    'arrival'        => $arrival,
                    'departure_time' => $departureTime,
                    'seat'           => $seat,
                    'notes'          => $notes,
                ];
            }
            $this->saveTickets($userId, $tickets);
            return $this->response->setJSON(['success' => true, 'id' => $id]);
        }

        if ($action === 'delete_ticket') {
            $id      = trim($this->request->getPost('id') ?? '');
            $tickets = array_filter($this->getTickets($userId), fn($t) => $t['id'] !== $id);
            $this->saveTickets($userId, $tickets);
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Aksi tidak dikenali.']);
    }
}
