<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoDestaque extends Model
{
    protected $table = 'produto_destaques';

    protected $fillable = [
        'produto_id', 'user_id', 'tipo',
        'inicio_em', 'fim_em',
        'activo', 'pago', 'valor_pago',
    ];

    protected $casts = [
        'inicio_em' => 'datetime',
        'fim_em'    => 'datetime',
        'activo'    => 'boolean',
        'pago'      => 'boolean',
        'valor_pago'=> 'decimal:2',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true)
                     ->where('fim_em', '>', now());
    }
}