<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Orders;
use App\Models\Products;

class Order_Item extends Model
{
    protected $table='order_item';
    protected $fillable=[
        'order_id', 'product_id', 'quantity', 'name_at_purchase', 'price_at_purchase', 'description_at_purchase', 'subtotal'
    ];

    public function order() {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}