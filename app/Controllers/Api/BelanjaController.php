<?php

namespace App\Controllers\Api;

use App\Models\BelanjaModel;

class BelanjaController extends ApiController
{
    public function index()
    {
        $userId = $this->uid();
        $model  = new BelanjaModel();
        $rows   = $model->getAll($userId);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['data_key']] = $row['data_value'];
        }

        return $this->ok(['data' => $result]);
    }

    public function sync()
    {
        $userId = $this->uid();
        $model  = new BelanjaModel();
        $body   = $this->request->getJSON(true) ?? [];

        $allowed = [
            'belanja_data', 'belanja_notes', 'belanja_storage',
            'belanja_favorites', 'belanja_history', 'belanja_pantry',
            'belanja_reminders', 'belanja_lists', 'belanja_current_list',
            'belanja_parking',
        ];

        $saved = 0;
        foreach ($body as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            $model->upsert(
                $userId,
                $key,
                is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)
            );
            $saved++;
        }

        return $this->ok(['saved' => $saved]);
    }
}
