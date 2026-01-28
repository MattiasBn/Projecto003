<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
      protected $fillable = [
        'entity_type',
        'entity_id',
        'tipo',
        'quantidade',
        'descricao',
        'performed_by',
    ];
}
