<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PontosTransacao extends Model
{
    protected $table = 'pontos_transacoes';

    protected $fillable = [
        'user_id', 'tipo', 'pontos',
        'motivo', 'descricao',
        'referencia_id', 'referencia_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referencia()
    {
        return $this->morphTo();
    }
}