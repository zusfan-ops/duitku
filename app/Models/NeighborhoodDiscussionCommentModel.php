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
        'parent_id',
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
                `parent_id` INT UNSIGNED NULL DEFAULT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `comment` TEXT NOT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                INDEX `idx_disc_comment` (`discussion_id`, `created_at`),
                INDEX `idx_disc_parent` (`parent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            try {
                $db->query($sql);
            } catch (\Throwable $e) {}
        } else {
            // Cek apakah kolom parent_id sudah ada
            try {
                $fields = $db->getFieldData($this->table);
                $hasParentId = false;
                foreach ($fields as $field) {
                    if ($field->name === 'parent_id') {
                        $hasParentId = true;
                        break;
                    }
                }
                if (!$hasParentId) {
                    $db->query("ALTER TABLE `{$this->table}` ADD COLUMN `parent_id` INT UNSIGNED NULL DEFAULT NULL AFTER `discussion_id`, ADD INDEX `idx_disc_parent` (`parent_id`)");
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Ambil komentar untuk suatu diskusi beserta info balasan (reply)
     */
    public function getComments(int $discussionId): array
    {
        return $this->select('neighborhood_discussion_comments.*, u.name AS author_name, u.role AS author_role, u.house_number, pu.name AS reply_to_author_name')
            ->join('users u', 'u.id = neighborhood_discussion_comments.user_id', 'left')
            ->join('neighborhood_discussion_comments parent_c', 'parent_c.id = neighborhood_discussion_comments.parent_id', 'left')
            ->join('users pu', 'pu.id = parent_c.user_id', 'left')
            ->where('neighborhood_discussion_comments.discussion_id', $discussionId)
            ->orderBy('neighborhood_discussion_comments.created_at', 'ASC')
            ->findAll();
    }
}
