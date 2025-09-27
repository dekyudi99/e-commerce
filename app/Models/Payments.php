<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Orders;

class Payments extends Model
{
    protected $table = 'payments';
    protected $fillable = [
        'order_id', 'amount', 'payment_gateway', 'gateway_transaction_id', 'payment_method', 'status', 'paid_at',
    ];

    public function order() {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }
}