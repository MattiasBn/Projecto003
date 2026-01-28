<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    //

     protected $fillable = [
        
        'user_id',
        'updated_by',
        'nome',
        'descricao',
        'preco',
        'quantidade',
        
    ];


public function  produtosCriados()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function produtosEditados()
{
    return $this->belongsTo(User::class, 'updated_by');
}


public function logs()
{
    return $this->hasMany(ActivityLog::class, 'entity_id')
        ->where('entity_type', 'produto');
}




}



