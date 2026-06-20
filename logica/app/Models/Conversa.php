<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversa extends Model
{
    protected $fillable = [
        'user1_id', 'user2_id', 'produto_id',
        'ultima_mensagem_id', 'ultima_actividade_em',
    ];

    protected $casts = [
        'ultima_actividade_em' => 'datetime',
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function mensagens()
    {
        return $this->hasMany(Mensagem::class)->orderBy('created_at', 'asc');
    }

    public function ultimaMensagem()
    {
        return $this->hasOne(Mensagem::class)->latestOfMany();
    }

    public function encontro()
    {
        return $this->hasOne(Encontro::class);
    }

    // Retorna o outro utilizador da conversa
    public function outroUser(int $meuId)
    {
        return $this->user1_id === $meuId ? $this->user2 : $this->user1;
    }
}