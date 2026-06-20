<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPonto extends Model
{
    protected $table = 'user_pontos';

    protected $fillable = [
        'user_id', 'saldo',
        'total_ganho', 'total_gasto',
        'streak_dias', 'ultimo_acesso',
    ];

    protected $casts = [
        'ultimo_acesso' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Adiciona pontos ao saldo
    public function creditar(int $pontos): void
    {
        $this->increment('saldo', $pontos);
        $this->increment('total_ganho', $pontos);
    }

    // Remove pontos do saldo
    public function debitar(int $pontos): void
    {
        $this->decrement('saldo', $pontos);
        $this->decrement('total_gasto', $pontos);
    }

    // Calcula e actualiza o streak diário
    public function actualizarStreak(): void
    {
        $hoje = now()->toDateString();
        $ontem = now()->subDay()->toDateString();

        if ($this->ultimo_acesso?->toDateString() === $hoje) {
            return; // Já actualizou hoje
        }

        if ($this->ultimo_acesso?->toDateString() === $ontem) {
            $this->increment('streak_dias');
        } else {
            $this->streak_dias = 1; // Reinicia streak
        }

        $this->ultimo_acesso = $hoje;
        $this->save();
    }
}