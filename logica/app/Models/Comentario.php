<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comentario extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'produto_id',
        'comentario_pai_id', 'texto', 'total_respostas',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function pai()
    {
        return $this->belongsTo(Comentario::class, 'comentario_pai_id');
    }

    public function respostas()
    {
        return $this->hasMany(Comentario::class, 'comentario_pai_id')
                    ->orderBy('created_at', 'asc');
    }
}