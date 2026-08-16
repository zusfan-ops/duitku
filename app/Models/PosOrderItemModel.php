<?php

namespace App\Models;

use CodeIgniter\Model;

class PosOrderItemModel extends Model
{
    protected $table            = 'pos_order_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'order_id', 'product_id', 'product_name', 'notes',
        'qty', 'price', 'cost_price', 'subtotal',
    ];

    protected $useTimestamps = false;
}
