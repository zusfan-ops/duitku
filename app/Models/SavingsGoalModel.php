<?php

namespace App\Models;

use CodeIgniter\Model;

class SavingsGoalModel extends Model
{
    protected $table         = 'savings_goals';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id', 'name', 'icon', 'color',
        'target_amount', 'saved_amount', 'deadline',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get all savings goals for a user, ordered by creation date.
     */
    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Add amount to a goal's saved_amount.
     *
     * @return bool
     */
    public function topUp(int $id, int $userId, float $amount): bool
    {
        $goal = $this->where('id', $id)->where('user_id', $userId)->first();
        if (!$goal) return false;

        $newSaved = min((float)$goal['saved_amount'] + $amount, (float)$goal['target_amount']);
        $this->update($id, ['saved_amount' => $newSaved]);
        return true;
    }

    /**
     * Delete a goal belonging to a user.
     */
    public function deleteForUser(int $id, int $userId): bool
    {
        $goal = $this->where('id', $id)->where('user_id', $userId)->first();
        if (!$goal) return false;
        $this->delete($id);
        return true;
    }
}
