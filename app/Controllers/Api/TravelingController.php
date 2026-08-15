<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;
use App\Models\SettingModel;
use App\Models\TransactionModel;
use App\Models\WalletModel;

class TravelingController extends ApiController
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

    // GET /api/traveling
    public function index()
    {
        $userId  = $this->uid();
        $trips   = $this->getTrips($userId);
        $items   = $this->getItems($userId);
        $tickets = $this->getTickets($userId);

        // Fetch all transactions tagged with [Trip:
        $transactions = $this->txModel
            ->select('transactions.*, categories.name as category_name, categories.icon as category_icon, categories.color as category_color, wallets.name as wallet_name')
            ->join('categories', 'categories.id = transactions.category_id', 'left')
            ->join('wallets', 'wallets.id = transactions.wallet_id', 'left')
            ->where('transactions.user_id', $userId)
            ->like('transactions.note', '[Trip:', 'after')
            ->orderBy('transactions.date', 'DESC')
            ->orderBy('transactions.id', 'DESC')
            ->findAll();

        // Calculate total cost for each trip
        foreach ($trips as &$t) {
            $tag = '[Trip:' . $t['id'] . ']';
            $totalCost = 0.0;
            foreach ($transactions as $tx) {
                if ($tx['type'] === 'expense' && str_starts_with($tx['note'] ?? '', $tag)) {
                    $totalCost += (float)$tx['amount'];
                }
            }
            $t['total_cost'] = $totalCost;
        }
        unset($t);

        return $this->ok([
            'trips'        => $trips,
            'items'        => $items,
            'tickets'      => $tickets,
            'transactions' => $transactions,
        ]);
    }

    // POST /api/traveling/sync
    public function sync()
    {
        $userId = $this->uid();
        $data   = $this->request->getJSON(true) ?? $this->request->getPost();
        $action = trim($data['action'] ?? '');

        // ── Trip CRUD ─────────────────────────────────────────────
        if ($action === 'save_trip') {
            $id          = trim($data['id'] ?? '');
            $destination = trim($data['destination'] ?? '');
            $description = trim($data['description'] ?? '');
            $startDate   = trim($data['start_date'] ?? '');
            $endDate     = trim($data['end_date'] ?? '');
            $budget      = (float)($data['budget'] ?? 0);

            if (!$destination || !$startDate) {
                return $this->fail('Destinasi dan tanggal mulai wajib diisi.');
            }

            $trips = $this->getTrips($userId);
            $savedTrip = null;
            if ($id) {
                $found = false;
                foreach ($trips as &$t) {
                    if ($t['id'] === $id) {
                        $t['destination'] = $destination;
                        $t['description'] = $description;
                        $t['start_date']  = $startDate;
                        $t['end_date']    = $endDate;
                        $t['budget']      = $budget;
                        $savedTrip        = $t;
                        $found            = true;
                        break;
                    }
                }
                unset($t);
                if (!$found) {
                    $savedTrip = [
                        'id'          => $id,
                        'destination' => $destination,
                        'description' => $description,
                        'start_date'  => $startDate,
                        'end_date'    => $endDate,
                        'budget'      => $budget,
                        'created_at'  => date('c'),
                    ];
                    $trips[] = $savedTrip;
                }
            } else {
                $id = uniqid('trip_', true);
                $savedTrip = [
                    'id'          => $id,
                    'destination' => $destination,
                    'description' => $description,
                    'start_date'  => $startDate,
                    'end_date'    => $endDate,
                    'budget'      => $budget,
                    'created_at'  => date('c'),
                ];
                $trips[] = $savedTrip;
            }
            $this->saveTrips($userId, $trips);
            return $this->ok(['id' => $id, 'trip' => $savedTrip]);
        }

        if ($action === 'delete_trip') {
            $id = trim($data['id'] ?? '');
            $trips = array_values(array_filter($this->getTrips($userId), fn($t) => $t['id'] !== $id));
            $this->saveTrips($userId, $trips);

            // Clean associated items and tickets
            $items = array_values(array_filter($this->getItems($userId), fn($i) => ($i['trip_id'] ?? '') !== $id));
            $this->saveItems($userId, $items);

            $tickets = array_values(array_filter($this->getTickets($userId), fn($tk) => ($tk['trip_id'] ?? '') !== $id));
            $this->saveTickets($userId, $tickets);

            return $this->ok();
        }

        // ── Item CRUD ─────────────────────────────────────────────
        if ($action === 'save_item') {
            $id       = trim($data['id'] ?? '');
            $tripId   = trim($data['trip_id'] ?? '');
            $name     = trim($data['name'] ?? '');
            $isPacked = !empty($data['is_packed']) ? 1 : 0;

            if (!$tripId || !$name) {
                return $this->fail('Nama barang wajib diisi.');
            }

            $items = $this->getItems($userId);
            $savedItem = null;
            if ($id) {
                $found = false;
                foreach ($items as &$it) {
                    if ($it['id'] === $id) {
                        $it['name']      = $name;
                        $it['is_packed'] = $isPacked;
                        $savedItem       = $it;
                        $found           = true;
                        break;
                    }
                }
                unset($it);
                if (!$found) {
                    $savedItem = [
                        'id'        => $id,
                        'trip_id'   => $tripId,
                        'name'      => $name,
                        'is_packed' => $isPacked,
                    ];
                    $items[] = $savedItem;
                }
            } else {
                $id = uniqid('item_', true);
                $savedItem = [
                    'id'        => $id,
                    'trip_id'   => $tripId,
                    'name'      => $name,
                    'is_packed' => $isPacked,
                ];
                $items[] = $savedItem;
            }
            $this->saveItems($userId, $items);
            return $this->ok(['id' => $id, 'item' => $savedItem]);
        }

        if ($action === 'toggle_item') {
            $id    = trim($data['id'] ?? '');
            $items = $this->getItems($userId);
            foreach ($items as &$it) {
                if ($it['id'] === $id) {
                    $it['is_packed'] = empty($it['is_packed']) ? 1 : 0;
                    break;
                }
            }
            unset($it);
            $this->saveItems($userId, $items);
            return $this->ok();
        }

        if ($action === 'delete_item') {
            $id    = trim($data['id'] ?? '');
            $items = array_values(array_filter($this->getItems($userId), fn($i) => $i['id'] !== $id));
            $this->saveItems($userId, $items);
            return $this->ok();
        }

        // ── Ticket CRUD ───────────────────────────────────────────
        if ($action === 'save_ticket') {
            $id            = trim($data['id'] ?? '');
            $tripId        = trim($data['trip_id'] ?? '');
            $type          = trim($data['type'] ?? 'flight');
            $code          = trim($data['code'] ?? '');
            $qrData        = trim($data['qr_data'] ?? '');
            $passengerName = trim($data['passenger_name'] ?? '');
            $departure     = trim($data['departure'] ?? '');
            $arrival       = trim($data['arrival'] ?? '');
            $departureTime = trim($data['departure_time'] ?? '');
            $seat          = trim($data['seat'] ?? '');
            $notes         = trim($data['notes'] ?? '');

            if (!$tripId || !$departure) {
                return $this->fail('Data tiket tidak lengkap.');
            }

            $tickets = $this->getTickets($userId);
            $savedTicket = null;
            if ($id) {
                $found = false;
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
                        $savedTicket = $tk;
                        $found       = true;
                        break;
                    }
                }
                unset($tk);
                if (!$found) {
                    $savedTicket = [
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
                    $tickets[] = $savedTicket;
                }
            } else {
                $id = uniqid('tkt_', true);
                $savedTicket = [
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
                $tickets[] = $savedTicket;
            }
            $this->saveTickets($userId, $tickets);
            return $this->ok(['id' => $id, 'ticket' => $savedTicket]);
        }

        if ($action === 'delete_ticket') {
            $id      = trim($data['id'] ?? '');
            $tickets = array_values(array_filter($this->getTickets($userId), fn($t) => $t['id'] !== $id));
            $this->saveTickets($userId, $tickets);
            return $this->ok();
        }

        return $this->fail('Aksi tidak dikenali.');
    }
}
