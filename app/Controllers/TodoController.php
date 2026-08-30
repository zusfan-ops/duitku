<?php

namespace App\Controllers;

use App\Models\TodoModel;
use App\Models\SettingModel;

class TodoController extends BaseController
{
    protected TodoModel $todoModel;
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->todoModel    = new TodoModel();
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $userId   = session()->get('user_id');
        $filter   = $this->request->getGet('filter') ?: 'all';
        $category = $this->request->getGet('category') ?: 'all';
        $search   = trim((string)$this->request->getGet('search'));

        $tasks   = $this->todoModel->getForUser($userId, [
            'filter'   => $filter,
            'category' => $category,
            'search'   => $search,
        ]);
        $summary = $this->todoModel->getSummary($userId);

        $categories = [
            'Keuangan'   => ['icon' => '💰', 'color' => '#10B981'],
            'Pekerjaan'  => ['icon' => '💼', 'color' => '#3B82F6'],
            'Pribadi'    => ['icon' => '👤', 'color' => '#8B5CF6'],
            'Belanja'    => ['icon' => '🛒', 'color' => '#EC4899'],
            'Traveling'  => ['icon' => '✈️', 'color' => '#06B6D4'],
            'Kesehatan'  => ['icon' => '❤️', 'color' => '#EF4444'],
            'Lainnya'    => ['icon' => '📝', 'color' => '#64748B'],
        ];

        return view('todo/index', [
            'pageTitle'      => 'Todo-List',
            'tasks'          => $tasks,
            'summary'        => $summary,
            'filter'         => $filter,
            'category'       => $category,
            'search'         => $search,
            'todoCategories' => $categories,
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $title  = trim((string)$this->request->getPost('title'));

        if (empty($title)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Judul tugas tidak boleh kosong.']);
            }
            return redirect()->back()->with('error', 'Judul tugas tidak boleh kosong.');
        }

        $description = trim((string)$this->request->getPost('description'));
        $category    = $this->request->getPost('category') ?: 'Pribadi';
        $priority    = $this->request->getPost('priority') ?: 'medium';
        $dueDate     = $this->request->getPost('due_date') ?: null;
        $dueTime     = $this->request->getPost('due_time') ?: null;

        // Subtasks
        $subtasksRaw = $this->request->getPost('subtasks');
        $subtasksJson = null;
        if (is_array($subtasksRaw)) {
            $cleaned = [];
            foreach ($subtasksRaw as $st) {
                if (!empty($st['title'])) {
                    $cleaned[] = [
                        'title' => trim($st['title']),
                        'done'  => !empty($st['done']) ? 1 : 0,
                    ];
                }
            }
            if (!empty($cleaned)) {
                $subtasksJson = json_encode($cleaned);
            }
        }

        $data = [
            'user_id'      => $userId,
            'title'        => $title,
            'description'  => $description ?: null,
            'category'     => $category,
            'priority'     => in_array($priority, ['high', 'medium', 'low']) ? $priority : 'medium',
            'due_date'     => !empty($dueDate) ? $dueDate : null,
            'due_time'     => !empty($dueTime) ? $dueTime : null,
            'is_completed' => 0,
            'subtasks'     => $subtasksJson,
        ];

        $id = $this->todoModel->insert($data);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'id'      => $id,
                'message' => 'Tugas berhasil ditambahkan!',
                'summary' => $this->todoModel->getSummary($userId),
            ]);
        }

        return redirect()->to('/todo')->with('success', 'Tugas baru berhasil ditambahkan!');
    }

    public function toggle($id)
    {
        $userId = session()->get('user_id');
        $task = $this->todoModel->where('user_id', $userId)->find($id);

        if (!$task) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tugas tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Tugas tidak ditemukan.');
        }

        $newCompleted = $task['is_completed'] ? 0 : 1;
        $completedAt  = $newCompleted ? date('Y-m-d H:i:s') : null;

        $this->todoModel->update($id, [
            'is_completed' => $newCompleted,
            'completed_at' => $completedAt,
        ]);

        $summary = $this->todoModel->getSummary($userId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'      => true,
                'is_completed' => (bool)$newCompleted,
                'summary'      => $summary,
                'message'      => $newCompleted ? 'Tugas selesai! 🎉' : 'Tugas diaktifkan kembali.',
            ]);
        }

        return redirect()->back();
    }

    public function updateTask($id)
    {
        $userId = session()->get('user_id');
        $task = $this->todoModel->where('user_id', $userId)->find($id);

        if (!$task) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tugas tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Tugas tidak ditemukan.');
        }

        $title = trim((string)$this->request->getPost('title'));
        if (empty($title)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Judul tidak boleh kosong.']);
            }
            return redirect()->back()->with('error', 'Judul tidak boleh kosong.');
        }

        $subtasksRaw = $this->request->getPost('subtasks');
        $subtasksJson = null;
        if (is_array($subtasksRaw)) {
            $cleaned = [];
            foreach ($subtasksRaw as $st) {
                if (!empty($st['title'])) {
                    $cleaned[] = [
                        'title' => trim($st['title']),
                        'done'  => !empty($st['done']) ? 1 : 0,
                    ];
                }
            }
            if (!empty($cleaned)) {
                $subtasksJson = json_encode($cleaned);
            }
        } elseif ($this->request->getPost('subtasks_json')) {
            $subtasksJson = $this->request->getPost('subtasks_json');
        }

        $data = [
            'title'       => $title,
            'description' => trim((string)$this->request->getPost('description')) ?: null,
            'category'    => $this->request->getPost('category') ?: $task['category'],
            'priority'    => $this->request->getPost('priority') ?: $task['priority'],
            'due_date'    => $this->request->getPost('due_date') ?: null,
            'due_time'    => $this->request->getPost('due_time') ?: null,
            'subtasks'    => $subtasksJson,
        ];

        $this->todoModel->update($id, $data);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui!',
                'summary' => $this->todoModel->getSummary($userId),
            ]);
        }

        return redirect()->to('/todo')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function delete($id)
    {
        $userId = session()->get('user_id');
        $task = $this->todoModel->where('user_id', $userId)->find($id);

        if (!$task) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tugas tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Tugas tidak ditemukan.');
        }

        $this->todoModel->delete($id);
        $summary = $this->todoModel->getSummary($userId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tugas berhasil dihapus.',
                'summary' => $summary,
            ]);
        }

        return redirect()->to('/todo')->with('success', 'Tugas berhasil dihapus.');
    }

    // ── REST API Endpoints for Flutter Native App ─────────────────────────────
    public function apiList()
    {
        $userId = \App\Libraries\ApiAuth::id() ?: session()->get('user_id') ?: (int)$this->request->getGet('user_id');
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $filter   = $this->request->getGet('filter') ?: 'all';
        $category = $this->request->getGet('category') ?: 'all';
        $search   = trim((string)$this->request->getGet('search'));

        $tasks   = $this->todoModel->getForUser((int)$userId, [
            'filter'   => $filter,
            'category' => $category,
            'search'   => $search,
        ]);
        $summary = $this->todoModel->getSummary((int)$userId);

        return $this->response->setJSON([
            'success' => true,
            'data'    => $tasks,
            'summary' => $summary,
        ]);
    }

    public function apiStore()
    {
        $userId = \App\Libraries\ApiAuth::id() ?: session()->get('user_id') ?: (int)$this->request->getPost('user_id');
        if (!$userId) {
            $json = $this->request->getJSON(true);
            $userId = (int)($json['user_id'] ?? 0);
        }
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $input = $this->request->getJSON(true) ?: $this->request->getPost();
        $id    = (int)($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');

        if (empty($title)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Judul wajib diisi.']);
        }

        $data = [
            'user_id'      => (int)$userId,
            'title'        => $title,
            'description'  => $input['description'] ?? null,
            'category'     => $input['category'] ?? 'Pribadi',
            'priority'     => $input['priority'] ?? 'medium',
            'due_date'     => $input['due_date'] ?? null,
            'due_time'     => $input['due_time'] ?? null,
            'is_completed' => 0,
            'subtasks'     => isset($input['subtasks']) ? (is_array($input['subtasks']) ? json_encode($input['subtasks']) : $input['subtasks']) : null,
        ];

        if ($id > 0) {
            $existing = $this->todoModel->where('id', $id)->where('user_id', (int)$userId)->first();
            if ($existing) {
                unset($data['is_completed']);
                $this->todoModel->update($id, $data);
                $task = $this->todoModel->find($id);
                return $this->response->setJSON([
                    'success' => true,
                    'data'    => $task,
                    'message' => 'Tugas berhasil diperbarui!',
                ]);
            }
        }

        $id = $this->todoModel->insert($data);
        $task = $this->todoModel->find($id);

        return $this->response->setJSON([
            'success' => true,
            'data'    => $task,
            'message' => 'Tugas berhasil disimpan!',
        ]);
    }

    public function apiToggle($id)
    {
        $userId = \App\Libraries\ApiAuth::id() ?: session()->get('user_id') ?: (int)$this->request->getGet('user_id');
        if (!$userId) {
            $json = $this->request->getJSON(true);
            $userId = (int)($json['user_id'] ?? 0);
        }
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $task = $this->todoModel->where('id', $id)->where('user_id', (int)$userId)->first();
        if (!$task) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tugas tidak ditemukan.']);
        }

        $newCompleted = $task['is_completed'] ? 0 : 1;
        $this->todoModel->update($id, [
            'is_completed' => $newCompleted,
            'completed_at' => $newCompleted ? date('Y-m-d H:i:s') : null,
        ]);

        return $this->response->setJSON([
            'success'      => true,
            'is_completed' => (bool)$newCompleted,
            'message'      => $newCompleted ? 'Tugas selesai' : 'Tugas aktif',
        ]);
    }

    public function apiDelete($id)
    {
        $userId = \App\Libraries\ApiAuth::id() ?: session()->get('user_id') ?: (int)$this->request->getGet('user_id');
        if (!$userId) {
            $json = $this->request->getJSON(true);
            $userId = (int)($json['user_id'] ?? 0);
        }
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $task = $this->todoModel->where('id', $id)->where('user_id', (int)$userId)->first();
        if (!$task) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tugas tidak ditemukan.']);
        }
        $this->todoModel->delete($id);
        return $this->response->setJSON(['success' => true, 'message' => 'Tugas dihapus.']);
    }
}
