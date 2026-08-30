<?php

namespace App\Models;

use CodeIgniter\Model;

class TodoModel extends Model
{
    protected $table            = 'todos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'title',
        'description',
        'category',
        'priority',
        'due_date',
        'due_time',
        'is_completed',
        'completed_at',
        'subtasks',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    /**
     * Auto-create todos table if not exists for instant zero-config setup
     */
    public function ensureTable()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists($this->table)) {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'category' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Pribadi',
                ],
                'priority' => [
                    'type'       => 'ENUM',
                    'constraint' => ['high', 'medium', 'low'],
                    'default'    => 'medium',
                ],
                'due_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'due_time' => [
                    'type' => 'TIME',
                    'null' => true,
                ],
                'is_completed' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'completed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'subtasks' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $forge->addKey('id', true);
            $forge->addKey(['user_id', 'is_completed']);
            $forge->addKey(['user_id', 'due_date']);
            $forge->createTable($this->table, true);
        }
    }

    public function getForUser(int $userId, array $filters = []): array
    {
        $builder = $this->where('user_id', $userId);

        if (!empty($filters['filter'])) {
            $today = date('Y-m-d');
            switch ($filters['filter']) {
                case 'today':
                    $builder->where('due_date', $today);
                    break;
                case 'upcoming':
                    $builder->where('due_date >', $today)->where('is_completed', 0);
                    break;
                case 'overdue':
                    $builder->where('due_date <', $today)->where('due_date IS NOT NULL')->where('is_completed', 0);
                    break;
                case 'completed':
                    $builder->where('is_completed', 1);
                    break;
                case 'pending':
                    $builder->where('is_completed', 0);
                    break;
                case 'high':
                    $builder->where('priority', 'high')->where('is_completed', 0);
                    break;
            }
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $builder->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('title', $filters['search'])
                ->orLike('description', $filters['search'])
            ->groupEnd();
        }

        // Ordering: Incomplete first, then priority (high > medium > low), then due date, then created_at
        $builder->orderBy('is_completed', 'ASC')
                ->orderBy("FIELD(priority, 'high', 'medium', 'low')", '', false)
                ->orderBy('due_date', 'ASC')
                ->orderBy('created_at', 'DESC');

        $rows = $builder->findAll();
        foreach ($rows as &$r) {
            $r['subtasks_array'] = !empty($r['subtasks']) ? (json_decode($r['subtasks'], true) ?: []) : [];
        }
        unset($r);

        return $rows;
    }

    public function getSummary(int $userId): array
    {
        $today = date('Y-m-d');

        $totalToday = $this->where('user_id', $userId)->where('due_date', $today)->countAllResults();
        $completedToday = $this->where('user_id', $userId)->where('due_date', $today)->where('is_completed', 1)->countAllResults();

        $totalAll = $this->where('user_id', $userId)->countAllResults();
        $completedAll = $this->where('user_id', $userId)->where('is_completed', 1)->countAllResults();
        $pendingAll = $totalAll - $completedAll;

        $overdueCount = $this->where('user_id', $userId)
            ->where('is_completed', 0)
            ->where('due_date <', $today)
            ->where('due_date IS NOT NULL')
            ->countAllResults();

        // 2 urgent previews (due today or overdue or highest priority)
        $previews = $this->where('user_id', $userId)
            ->where('is_completed', 0)
            ->orderBy("FIELD(priority, 'high', 'medium', 'low')", '', false)
            ->orderBy('due_date', 'ASC')
            ->limit(2)
            ->findAll();

        foreach ($previews as &$p) {
            $p['subtasks_array'] = !empty($p['subtasks']) ? (json_decode($p['subtasks'], true) ?: []) : [];
        }
        unset($p);

        $completionRate = $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : ($totalAll > 0 ? round(($completedAll / $totalAll) * 100) : 0);

        return [
            'total_today'     => $totalToday,
            'completed_today' => $completedToday,
            'pending_today'   => max(0, $totalToday - $completedToday),
            'total_all'       => $totalAll,
            'completed_all'   => $completedAll,
            'pending_all'     => $pendingAll,
            'overdue_count'   => $overdueCount,
            'completion_rate' => $completionRate,
            'previews'        => $previews,
        ];
    }
}
