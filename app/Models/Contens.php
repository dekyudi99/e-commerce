<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserProgress;

class Contens extends Model 
{
    protected $appends = ['quiz_times'];
    protected $table = 'contents';
    protected $fillable = [
        'title', 'video', 'description', 'creator', 'playlist', 'thumbnail',
    ];

    public function getQuizTimesAttribute() {
        return [
            "1" => "Apa tujuan utama dari UMKM?",
            "3" => "Sebutkan tantangan digitalisasi!",
        ];
    }

    public function user() {
        return $this->hasMany(UserProgress::class, 'content_id', 'id');
    }
}