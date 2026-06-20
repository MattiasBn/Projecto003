<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncontroLocalizacao extends Model
{
    protected $fillable = [
        'encontro_id', 'user_id',
        'latitude', 'longitude',
        'velocidade', 'precisao',
    ];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
        'velocidade'=> 'decimal:2',
        'precisao'  => 'decimal:2',
    ];

    public function encontro()
    {
        return $this->belongsTo(Encontro::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}