<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoImagem extends Model
{
    protected $fillable = ['produto_id', 'caminho', 'url', 'principal', 'ordem'];

    protected $casts = ['principal' => 'boolean'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}