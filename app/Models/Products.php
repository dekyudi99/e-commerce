<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
use App\Models\Order_Item;
use App\Models\Cart;
use Illuminate\Support\Facades\Storage;

class Products extends Model
{
    protected $appends = ['image_url'];
    protected $table = 'product';
    protected $fillable = [
        'shop_id', 'title', 'description', 'location', 'category', 'price', 'stock', 'image', 'rating'
    ];

    public function getImageUrlAttribute()
    {
        // 'image' adalah nama kolom di database Anda yang berisi nama file
        if ($this->image) 
        {
            // Gunakan helper asset() untuk membuat URL ke folder public/uploads/product
            return ('https://4bad-182-253-163-199.ngrok-free.app/UMKMConnect/public/uploads/product/' . $this->image);
        }

        // Kembalikan null atau URL gambar default jika tidak ada gambar
        return null;
    }

    public function user() {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function orderItem() {
        return $this->hasMany(Order_Item::class, 'product_id', 'id');
    }

    public function cart() {
        return $this->hasMany(Cart::class, 'product_id', 'id');
    }
}