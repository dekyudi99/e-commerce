<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Review;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'phone_number', 'role', 'profile_picture'];
    protected $hidden = ['password'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        // 'image' adalah nama kolom di database Anda yang berisi nama file
        if ($this->path_image) 
        {
            // Gunakan helper asset() untuk membuat URL ke folder public/uploads/product
            return (env('APP_URL').'/uploads/profile/'.$this->path_image);
        }

        // Kembalikan null atau URL gambar default jika tidak ada gambar
        return null;
    }

    // Implementasi JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // biasanya return id
    }

    public function getJWTCustomClaims()
    {
        return [
            // 'role' => $this->role,
            // 'bisnis_name' => $this->bisnis_name,
        ]; // bisa tambahkan data tambahan ke token jika perlu
    }

    public function orders() {
        return $this->hasMany(Orders::class, 'user_id', 'id');
    }

    public function product() {
        return $this->hasMany(Products::class, 'user_id', 'id');
    }

    public function review() {
        return $this->hasMany(Review::class, 'user_id', 'id');
    }
}