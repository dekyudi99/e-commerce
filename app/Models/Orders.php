<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Payments;
use App\Models\Products;

class Orders extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'user_id', 'invoice_number', 'total_amount', 'status', 'shipping_address',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function payments() {
        return $this->hasOne(Payments::class, 'order_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Payments::class, 'order_id', 'id');
    }
}