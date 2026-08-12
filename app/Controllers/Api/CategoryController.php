<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;

class CategoryController extends ApiController
{
    protected CategoryModel $catModel;

    public function __construct()
    {
        $this->catModel = new CategoryModel();
    }

    public function index()
    {
        $userId = $this->uid();
        $type   = $this->request->getGet('type');
        $cats   = $this->catModel->getForUser($userId, $type ?: null);
        return $this->ok(['categories' => $cats]);
    }

    public function store()
    {
        $userId = $this->uid();
        $json   = $this->request->getJSON(true) ?? [];
        $name   = trim($json['name'] ?? '');
        $type   = $json['type'] ?? '';
        $icon   = trim($json['icon'] ?? '') ?: 'other';
        $color  = trim($json['color'] ?? '') ?: '#6B7280';

        if (!$name || !in_array($type, ['income', 'expense'])) {
            return $this->fail('Data tidak valid.');
        }

        $id = $this->catModel->insert([
            'user_id'    => $userId,
            'name'       => $name,
            'type'       => $type,
            'icon'       => $icon,
            'color'      => $color,
            'is_default' => 0,
            'sort_order' => 99,
        ]);

        return $this->ok(['id' => $id]);
    }

    public function delete(int $id)
    {
        $userId  = $this->uid();
        $deleted = $this->catModel->deleteForUser($id, $userId);

        if ($deleted) {
            return $this->ok();
        }
        return $this->fail('Tidak dapat dihapus.');
    }
}
