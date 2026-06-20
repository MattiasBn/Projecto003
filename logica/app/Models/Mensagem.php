<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
    protected $fillable = [
        'conversa_id', 'remetente_id',
        'texto', 'tipo', 'lida', 'lida_em',
    ];

    protected $casts = [
        'lida'    => 'boolean',
        'lida_em' => 'datetime',
    ];

    public function conversa()
    {
        return $this->belongsTo(Conversa::class);
    }

    public function remetente()
    {
        return $this->belongsTo(User::class, 'remetente_id');
    }
}