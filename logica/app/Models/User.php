<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'telefone', 'telefone_verificado',
        'avatar', 'bio',
        'provincia', 'municipio', 'bairro',
        'latitude', 'longitude',
        'score_credibilidade', 'total_vendas',
        'verificado', 'activo', 'bloqueado',
        'google_id', 'facebook_id', 'provider',
        'role', 'fcm_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'telefone_verificado' => 'boolean',
        'verificado'          => 'boolean',
        'activo'              => 'boolean',
        'bloqueado'           => 'boolean',
        'score_credibilidade' => 'decimal:2',
    ];

    // ─── Helpers de role ────────────────────────────────────────────
    public function isAdmin(): bool      { return $this->role === 'administrador'; }
    public function isGerente(): bool    { return $this->role === 'gerente'; }
    public function isFuncionario(): bool{ return $this->role === 'funcionario'; }
    public function isUsuario(): bool    { return $this->role === 'usuario'; }
    public function isStaff(): bool      { return in_array($this->role, ['administrador', 'gerente', 'funcionario']); }

    // ─── Relacionamentos ────────────────────────────────────────────
    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function follows()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function seguidores()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function avaliacoesFeitas()
    {
        return $this->hasMany(Avaliacao::class, 'avaliador_id');
    }

    public function avaliacoesRecebidas()
    {
        return $this->hasMany(Avaliacao::class, 'vendedor_id');
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class);
    }

    public function denuncias()
    {
        return $this->hasMany(Denuncia::class);
    }


   // Adiciona estes métodos ao User.php existente

public function pontos()
{
    return $this->hasOne(UserPonto::class);
}

public function pontosTransacoes()
{
    return $this->hasMany(PontosTransacao::class);
}

public function alertas()
{
    return $this->hasMany(Alerta::class);
}

public function subscricao()
{
    return $this->hasOne(Subscricao::class)
                ->where('status', 'activa')
                ->where('fim_em', '>', now());
}

public function pagamentos()
{
    return $this->hasMany(Pagamento::class);
}

public function destaques()
{
    return $this->hasMany(ProdutoDestaque::class);
}


}