<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $fillable = [
        'user_id', 'tipo',
        'produto_id', 'categoria_id',
        'preco_maximo', 'raio_km',
        'activo', 'ultimo_disparo_em',
    ];

    protected $casts = [
        'activo'           => 'boolean',
        'ultimo_disparo_em'=> 'datetime',
        'preco_maximo'     => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}