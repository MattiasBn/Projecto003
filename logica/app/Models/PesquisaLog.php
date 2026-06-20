<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesquisaLog extends Model
{
    protected $table = 'pesquisas_log';

    protected $fillable = [
        'user_id', 'termo', 'provincia',
        'municipio', 'categoria', 'total_resultados',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}