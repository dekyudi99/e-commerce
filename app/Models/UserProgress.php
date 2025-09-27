<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Contens;

class UserProgress extends Model
{
    protected $table = 'user_progres';
    protected $fillable = [
        'user_id', 'content_id', 'compleated_at',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function contens() {
        return $this->belongsTo(Contens::class, 'content_id', 'id');
    }
}