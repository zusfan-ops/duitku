<?php

namespace App\Models;

use CodeIgniter\Model;

class RecurringTransactionModel extends Model
{
    protected $table         = 'recurring_transactions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'category_id', 'type', 'amount', 'note', 'next_date'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
