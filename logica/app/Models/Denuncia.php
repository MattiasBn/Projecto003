<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denuncia extends Model
{
    protected $fillable = [
        'user_id', 'motivo', 'descricao', 'status',
        'reportavel_id', 'reportavel_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Polimórfico: pode ser Produto ou User
    public function reportavel()
    {
        return $this->morphTo();
    }
}