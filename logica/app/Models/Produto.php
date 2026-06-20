<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'categoria_id',
        'titulo', 'descricao', 'preco',
        'tipo', 'estado', 'status', 'disponibilidade',
        'provincia', 'municipio', 'bairro', 'referencia',
        'latitude', 'longitude',
        'destaque', 'visualizacoes',
        'total_likes', 'total_descontos', 'total_comentarios',
    ];

    protected $casts = [
        'destaque'          => 'boolean',
        'preco'             => 'decimal:2',
        'latitude'          => 'decimal:7',
        'longitude'         => 'decimal:7',
        'total_likes'       => 'integer',
        'total_descontos'   => 'integer',
        'total_comentarios' => 'integer',
    ];

    // ─── Relacionamentos ────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagens()
    {
        return $this->hasMany(ProdutoImagem::class)->orderBy('ordem');
    }

    public function imagemPrincipal()
    {
        return $this->hasOne(ProdutoImagem::class)->where('principal', true);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function gostos()
    {
        return $this->hasMany(Like::class)->where('tipo', 'gosto');
    }

    public function pedidosDesconto()
    {
        return $this->hasMany(Like::class)->where('tipo', 'desconto');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class)
                    ->whereNull('comentario_pai_id')
                    ->orderBy('created_at', 'desc');
    }

    public function avaliacoes()
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function denuncias()
    {
        return $this->morphMany(Denuncia::class, 'reportavel');
    }

    // ─── Scope de pesquisa e filtros ────────────────────────────────
    public function scopeActivos($query)
    {
        return $query->where('status', 'activo');
    }

    public function scopePorProvincia($query, $provincia)
    {
        return $query->where('provincia', $provincia);
    }

    public function scopePorMunicipio($query, $municipio)
    {
        return $query->where('municipio', $municipio);
    }

    public function scopePorBairro($query, $bairro)
    {
        return $query->where('bairro', $bairro);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopePorPreco($query, $min, $max)
    {
        return $query->whereBetween('preco', [$min, $max]);
    }

    // Distância em km usando fórmula de Haversine
    public function scopeProximoDe($query, $lat, $lng, $raioKm = 10)
    {
        return $query->selectRaw("*,
            (6371 * acos(
                cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?)) * sin(radians(latitude))
            )) AS distancia", [$lat, $lng, $lat])
            ->having('distancia', '<=', $raioKm)
            ->orderBy('distancia');
    }
}