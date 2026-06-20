<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nome', 'icone', 'cor', 'activa', 'ordem'];

    protected $casts = ['activa' => 'boolean'];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}