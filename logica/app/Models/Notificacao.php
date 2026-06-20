<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    protected $table = 'notificacoes';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'tipo',
        'dados', 'fcm_token', 'lida_em',
    ];

    protected $casts = [
        'dados'   => 'array',
        'lida_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNaoLidas($query)
    {
        return $query->whereNull('lida_em');
    }
}