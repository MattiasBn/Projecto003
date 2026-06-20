<?php

namespace App\Http\Controllers;

use App\Models\UserPonto;
use App\Models\PontosTransacao;

class PontosController extends Controller
{
    // Tabela de pontos por acção
    private array $tabela = [
        'publicar_produto'   => 10,
        'venda_concluida'    => 50,
        'avaliacao_recebida' => 15,
        'primeiro_login'     => 20,
        'completar_perfil'   => 30,
        'convidar_amigo'     => 40,
        'streak_7_dias'      => 25,
        'streak_30_dias'     => 100,
    ];

    public function creditar(int $userId, string $motivo, $referenciaId = null, $referenciaType = null): void
    {
        $pontos = $this->tabela[$motivo] ?? 0;
        if ($pontos === 0) return;

        // Cria ou busca o saldo do utilizador
        $userPonto = UserPonto::firstOrCreate(
            ['user_id' => $userId],
            ['saldo' => 0, 'total_ganho' => 0, 'total_gasto' => 0]
        );

        $userPonto->creditar($pontos);

        // Regista a transacção
        PontosTransacao::create([
            'user_id'        => $userId,
            'tipo'           => 'ganho',
            'pontos'         => $pontos,
            'motivo'         => $motivo,
            'referencia_id'  => $referenciaId,
            'referencia_type'=> $referenciaType,
        ]);
    }

    // Retorna o saldo e histórico do utilizador autenticado
    public function meuSaldo()
    {
        $pontos = UserPonto::firstOrCreate(
            ['user_id' => auth()->id()],
            ['saldo' => 0, 'total_ganho' => 0, 'total_gasto' => 0]
        );

        $historico = PontosTransacao::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'saldo'    => $pontos->saldo,
            'streak'   => $pontos->streak_dias,
            'historico'=> $historico,
        ]);
    }
}