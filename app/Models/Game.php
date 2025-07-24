<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'groupe_id',
        'candidat',
    ];

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }
}
