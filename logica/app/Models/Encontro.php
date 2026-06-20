<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encontro extends Model
{
    protected $fillable = [
        'conversa_id', 'comprador_id', 'vendedor_id',
        'latitude_destino', 'longitude_destino', 'morada_destino',
        'status', 'agendado_para', 'iniciado_em', 'concluido_em',
    ];

    protected $casts = [
        'agendado_para' => 'datetime',
        'iniciado_em'   => 'datetime',
        'concluido_em'  => 'datetime',
        'latitude_destino'  => 'decimal:7',
        'longitude_destino' => 'decimal:7',
    ];

    public function conversa()
    {
        return $this->belongsTo(Conversa::class);
    }

    public function comprador()
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function localizacoes()
    {
        return $this->hasMany(EncontroLocalizacao::class)->orderBy('created_at', 'desc');
    }

    public function ultimaLocalizacao(int $userId)
    {
        return $this->hasMany(EncontroLocalizacao::class)
                    ->where('user_id', $userId)
                    ->latest()
                    ->first();
    }
}