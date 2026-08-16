<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleModel extends Model
{
    protected $table            = 'vehicles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'name',
        'type',
        'license_plate',
        'brand',
        'model_year',
        'odometer',
        'tax_annual_date',
        'tax_5year_date',
        'photo',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get all vehicles for a user with total logs count & total expense
     */
    public function getForUser(int $userId): array
    {
        return $this->db->query("
            SELECT v.*,
                   COALESCE(SUM(l.cost), 0) AS total_expense,
                   COUNT(l.id) AS total_logs,
                   MAX(CASE WHEN l.type = 'ganti_oli' THEN l.date END) AS last_oil_date,
                   MAX(CASE WHEN l.type = 'ganti_oli' THEN l.next_km END) AS next_oil_km,
                   MAX(CASE WHEN l.type = 'service_rutin' THEN l.date END) AS last_service_date,
                   MAX(CASE WHEN l.type = 'service_rutin' THEN l.next_km END) AS next_service_km
            FROM vehicles v
            LEFT JOIN vehicle_logs l ON l.vehicle_id = v.id
            WHERE v.user_id = ?
            GROUP BY v.id
            ORDER BY v.created_at DESC
        ", [$userId])->getResultArray();
    }

    /**
     * Get single vehicle with summary stats
     */
    public function getDetail(int $id, int $userId): ?array
    {
        $vehicle = $this->where('id', $id)->where('user_id', $userId)->first();
        if (!$vehicle) return null;

        $stats = $this->db->query("
            SELECT
                COALESCE(SUM(cost), 0) AS total_expense,
                COUNT(id) AS total_logs
            FROM vehicle_logs
            WHERE vehicle_id = ?
        ", [$id])->getRowArray();

        $vehicle['total_expense'] = (float)($stats['total_expense'] ?? 0);
        $vehicle['total_logs']    = (int)($stats['total_logs'] ?? 0);

        return $vehicle;
    }
}
