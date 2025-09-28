<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Products;
use App\Models\Order_Item;

class Orders extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'user_id', 'invoice_number', 'total_amount', 'status', 'shipping_address',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Products::class, 'order_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(Order_Item::class, 'order_id', 'id');
    }
}