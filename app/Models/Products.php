<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order_Item;
use App\Models\Cart;
use App\Models\Review;

class Products extends Model
{
    protected $appends = ['image_url', 'average_rating'];
    protected $table = 'product';
    protected $fillable = [
        'user_id', 'title', 'description', 'location', 'category', 'price', 'stock', 'satuan', 'image',
    ];

    public function getImageUrlAttribute()
    {
        if ($this->image) 
        {
            return (env('APP_URL').'/uploads/product/' . $this->image);
        }
        return null;
    }

    public function getAverageRatingAttribute() {
        return $this->review()->avg('rating');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function orderItem() {
        return $this->hasMany(Order_Item::class, 'product_id', 'id');
    }

    public function cart() {
        return $this->hasMany(Cart::class, 'product_id', 'id');
    }

    public function review() {
        return $this->hasMany(Review::class, 'product_id', 'id');
    }
}