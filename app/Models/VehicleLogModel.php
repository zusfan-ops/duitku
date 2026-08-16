<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleLogModel extends Model
{
    protected $table            = 'vehicle_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'vehicle_id',
        'user_id',
        'type',
        'title',
        'cost',
        'km',
        'next_km',
        'next_date',
        'date',
        'workshop',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get all logs for a specific vehicle
     */
    public function getForVehicle(int $vehicleId, int $userId): array
    {
        return $this->where('vehicle_id', $vehicleId)
                    ->where('user_id', $userId)
                    ->orderBy('date', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get recent logs across all vehicles for a user
     */
    public function getRecentForUser(int $userId, int $limit = 20): array
    {
        return $this->db->query("
            SELECT l.*, v.name AS vehicle_name, v.type AS vehicle_type, v.license_plate
            FROM vehicle_logs l
            JOIN vehicles v ON v.id = l.vehicle_id
            WHERE l.user_id = ?
            ORDER BY l.date DESC, l.created_at DESC
            LIMIT ?
        ", [$userId, $limit])->getResultArray();
    }
}
