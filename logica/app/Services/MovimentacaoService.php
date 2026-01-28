<?php
namespace App\Services;

use App\Models\Movimentacao;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class MovimentacaoService
{
    const TIPO_CRIACAO    = 'criacao';
    const TIPO_ATUALIZACAO = 'atualizacao';
    const TIPO_REMOCAO     = 'remocao';

    /**
     * Registra movimentação e log de uma entidade (produto ou user)
     *
     * $dados esperados:
     *  - entity_type (string) => 'user' ou 'produto'
     *  - entity_id   (int)
     *  - tipo        (string) => 'criacao', 'atualizacao', 'remocao'
     *  - descricao   (string) opcional
     *  - quantidade  (int) opcional
     *  - before      (array) opcional
     *  - after       (array) opcional
     *  - performed_by (int) opcional (id do usuário que realizou)
     */
    public static function registrar(array $dados): void
    {
        $userId = $dados['performed_by'] ?? Auth::id();

        // 1️ Movimentação
        $movimentacao = Movimentacao::create([
            'entity_type'  => $dados['entity_type'],
            'entity_id'    => $dados['entity_id'],
            'tipo'         => $dados['tipo'],
            'quantidade'   => $dados['quantidade'] ?? null,
            'descricao'    => $dados['descricao'] ?? null,
            'performed_by' => $userId,
        ]);

        // 2️ Log detalhado
        ActivityLog::create([
            'entity_type'  => $dados['entity_type'],
            'entity_id'    => $dados['entity_id'],
            'action'       => $dados['tipo'],
            'before'       => $dados['before'] ?? null,
            'after'        => $dados['after'] ?? $movimentacao->toArray(),
            'performed_by' => $userId,
        ]);
    }
}
