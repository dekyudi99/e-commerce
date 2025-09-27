<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Products;

class Shop extends Model
{
    protected $table = 'shop';
    protected $fillable = [
        'user_id', 'name', 'foto_ktp', 'fotoProfil_toko', 'status'
    ];

    protected $appends = ['foto_profil_toko_url', 'foto_ktp_url', 'penghasilan']; // Tambahkan ini

    public function getFotoProfilTokoUrlAttribute()
    {
        if ($this->fotoProfil_toko) {
            // Asumsi Anda menyimpan di public/uploads/toko
            return ('https://4bad-182-253-163-199.ngrok-free.app/UMKMConnect/public/uploads/toko/' . $this->fotoProfil_toko);
        }
        return null;
    }

    public function getFotoKtpUrlAttribute()
    {
        if ($this->foto_ktp) {
            // Asumsi Anda menyimpan di public/uploads/toko
            return ('https://4bad-182-253-163-199.ngrok-free.app/UMKMConnect/public/uploads/foto_ktp/' . $this->foto_ktp);
        }
        return null;
    }

    /**
     * Accessor untuk membuat atribut 'penghasilan' secara dinamis.
     *
     * @return int
     */
    public function getPenghasilanAttribute()
    {
        // 1. Ambil semua ID produk dari toko ini.
        $productIds = $this->product()->pluck('id')->toArray();

        // 2. Hitung jumlah total dari sub_total di tabel order_items
        //    hanya untuk produk milik toko ini DAN yang status order-nya 'paid'.
        $total = \App\Models\Order_Item::whereIn('product_id', $productIds)
                    ->whereHas('order', function ($query) {
                        $query->where('status', 'paid');
                    })
                    ->sum('subtotal');

        return $total;
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product() {
        return $this->hasMany(Products::class, 'shop_id', 'id');
    }
}