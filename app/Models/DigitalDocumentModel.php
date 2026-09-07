<?php

namespace App\Models;

use CodeIgniter\Model;

class DigitalDocumentModel extends Model
{
    protected $table         = 'digital_documents';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id',
        'name',
        'document_type',
        'category',
        'document_number',
        'issued_date',
        'expiry_date',
        'issuing_authority',
        'owner_name',
        'notes',
        'photo_path',
        'photo_back_path',
        'storage_location',
        'notify_before_days',
        'last_reminder_at',
        'status',
        'is_favorite',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Daftar dokumen user, dengan info metadata sudah lewat masa berlaku / segera.
     */
    public function getForUser(int $userId, string $category = ''): array
    {
        $q = $this->db->query("
            SELECT *,
                   (expiry_date IS NOT NULL AND expiry_date < CURDATE())                                       AS is_expired,
                   (expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE()
                        AND DATE_ADD(CURDATE(), INTERVAL notify_before_days DAY))                              AS is_expiring_soon
            FROM digital_documents
            WHERE user_id = ?
              " . ($category ? "AND category = ?" : "") . "
            ORDER BY is_favorite DESC, expiry_date ASC
        ", $category ? [$userId, $category] : [$userId])->getResultArray();

        foreach ($q as &$r) {
            $r['is_expired'] = (bool)$r['is_expired'];
            $r['is_expiring_soon'] = (bool)$r['is_expiring_soon'];
        }

        return $q;
    }

    /**
     * Dokumen yang masa berlakunya segera habis atau sudah lewat (untuk reminder).
     */
    public function getExpiring(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('status', 'active')
            ->groupStart()
                ->where('expiry_date < CURDATE()')
                ->orWhere('expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL notify_before_days DAY)')
            ->groupEnd()
            ->orderBy('expiry_date', 'ASC')
            ->findAll();
    }

    /**
     * Ringkasan dokumen per kategori/status.
     */
    public function getSummary(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();

        $categories = [];
        $expired = 0;
        $favorites = 0;

        foreach ($rows as $r) {
            $cat = $r['category'] ?: 'Lainnya';
            $categories[$cat] = ($categories[$cat] ?? 0) + 1;

            if ($r['is_favorite']) $favorites++;
            if ($r['expiry_date'] && $r['expiry_date'] < date('Y-m-d')) $expired++;
        }

        arsort($categories);

        return [
            'total_docs'  => count($rows),
            'categories'  => $categories,
            'expired'     => $expired,
            'favorites'   => $favorites,
            'status_breakdown' => [
                'active'   => count(array_filter($rows, fn($r) => $r['status'] === 'active')),
                'expired'  => count(array_filter($rows, fn($r) => $r['status'] === 'expired')),
                'renewed'  => count(array_filter($rows, fn($r) => $r['status'] === 'renewed')),
                'archived' => count(array_filter($rows, fn($r) => $r['status'] === 'archived')),
            ],
        ];
    }
}
