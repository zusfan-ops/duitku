<?php

namespace App\Controllers;

use App\Models\BelanjaModel;

class BelanjaController extends BaseController
{
    public function index()
    {
        return view('belanja');
    }

    public function sync()
    {
        $userId = session()->get('user_id');
        $model  = new BelanjaModel();

        if ($this->request->is('post')) {
            $body = $this->request->getJSON(true) ?? [];

            $allowed = [
                'belanja_data', 'belanja_notes', 'belanja_storage',
                'belanja_favorites', 'belanja_history', 'belanja_pantry',
                'belanja_reminders', 'belanja_lists', 'belanja_current_list',
                'belanja_parking',
            ];

            foreach ($body as $key => $value) {
                if (! in_array($key, $allowed, true)) {
                    continue;
                }
                $model->upsert(
                    $userId,
                    $key,
                    is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)
                );
            }

            return $this->response->setJSON(['ok' => true]);
        }

        // GET — return all stored keys for this user
        $rows   = $model->getAll($userId);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['data_key']] = $row['data_value'];
        }

        return $this->response->setJSON($result);
    }
}
