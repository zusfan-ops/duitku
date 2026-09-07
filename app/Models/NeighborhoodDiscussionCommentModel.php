<?php

namespace App\Models;

use CodeIgniter\Model;

class NeighborhoodDiscussionCommentModel extends Model
{
    protected $table            = 'neighborhood_discussion_comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'discussion_id',
        'user_id',
        'comment',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function ensureTable(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists($this->table)) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `discussion_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `comment` TEXT NOT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_disc_comment` (`discussion_id`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Ambil komentar untuk suatu diskusi
     */
    public function getComments(int $discussionId): array
    {
        return $this->select('neighborhood_discussion_comments.*, u.name AS author_name, u.role AS author_role, u.house_number')
            ->join('users u', 'u.id = neighborhood_discussion_comments.user_id', 'left')
            ->where('neighborhood_discussion_comments.discussion_id', $discussionId)
            ->orderBy('neighborhood_discussion_comments.created_at', 'ASC')
            ->findAll();
    }
}
