<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use App\Models\UserProgress;
use App\Models\Shop;
use App\Models\Orders;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'role', 'path_image'];
    protected $hidden = ['password'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        // 'image' adalah nama kolom di database Anda yang berisi nama file
        if ($this->path_image) 
        {
            // Gunakan helper asset() untuk membuat URL ke folder public/uploads/product
            return ('https://4bad-182-253-163-199.ngrok-free.app/UMKMConnect/public/uploads/profile/' . $this->path_image);
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

    public function userProgress() {
        return $this->hasMany(UserProgress::class, 'user_id', 'id');
    }

    public function shop() {
        return $this->hasOne(Shop::class, 'user_id', 'id');
    }

    public function orders() {
        return $this->hasMany(Orders::class, 'user_id', 'id');
    }
}