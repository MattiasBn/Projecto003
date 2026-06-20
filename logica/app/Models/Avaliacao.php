<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    protected $fillable = [
        'avaliador_id', 'vendedor_id',
        'produto_id', 'pontuacao', 'comentario',
    ];

    public function avaliador()
    {
        return $this->belongsTo(User::class, 'avaliador_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}