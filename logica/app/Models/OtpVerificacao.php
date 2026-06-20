<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerificacao extends Model
{
    protected $table =  'otp_verificacoes';

    protected $fillable =  ['telefone', 'codigo', 'usado', 'expira_em'];

    protected $casts = [
        'usado'     => 'boolean',
        'expira_em' => 'datetime',
    ];
}