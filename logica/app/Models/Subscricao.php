<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscricao extends Model
{
    protected $table = 'subscricoes';

    protected $fillable = [
        'user_id', 'plano_id',
        'inicio_em', 'fim_em',
        'status', 'renovacao_auto',
    ];

    protected $casts = [
        'inicio_em'     => 'datetime',
        'fim_em'        => 'datetime',
        'renovacao_auto'=> 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('status', 'activa')
                     ->where('fim_em', '>', now());
    }
}