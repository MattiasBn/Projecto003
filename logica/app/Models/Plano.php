<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $fillable = [
        'nome', 'preco',
        'limite_produtos', 'destaques_mes',
        'beneficios', 'activo',
    ];

    protected $casts = [
        'beneficios' => 'array',
        'activo'     => 'boolean',
        'preco'      => 'decimal:2',
    ];

    public function subscricoes()
    {
        return $this->hasMany(Subscricao::class);
    }
}