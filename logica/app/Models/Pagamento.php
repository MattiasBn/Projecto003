<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = [
        'user_id', 'valor', 'referencia',
        'metodo', 'status',
        'pagavel_id', 'pagavel_type',
        'pago_em',
    ];

    protected $casts = [
        'valor'   => 'decimal:2',
        'pago_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pagavel()
    {
        return $this->morphTo();
    }
}