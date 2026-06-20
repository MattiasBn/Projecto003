<?php

namespace App\Http\Controllers;

use App\Models\Encontro;
use App\Models\EncontroLocalizacao;
use App\Models\Conversa;
use App\Models\Mensagem;
use App\Models\Notificacao;
use App\Models\Avaliacao;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EncontroController extends Controller
{
    // Marcar encontro numa conversa
    public function marcar(Request $request, $conversaId)
    {
        $request->validate([
            'latitude_destino'  => 'required|numeric',
            'longitude_destino' => 'required|numeric',
            'morada_destino'    => 'nullable|string',
            'agendado_para'     => 'nullable|date',
            'vendedor_id'       => 'required|exists:users,id',
        ]);

        $conversa = Conversa::where(function ($q) {
            $q->where('user1_id', auth()->id())
              ->orWhere('user2_id', auth()->id());
        })->findOrFail($conversaId);

        // Só pode ter um encontro activo por conversa
        $jaExiste = Encontro::where('conversa_id', $conversaId)
            ->whereIn('status', ['pendente', 'activo'])
            ->exists();

        if ($jaExiste) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um encontro activo nesta conversa.',
            ], 422);
        }

        $encontro = Encontro::create([
            'conversa_id'       => $conversaId,
            'comprador_id'      => auth()->id(),
            'vendedor_id'       => $request->vendedor_id,
            'latitude_destino'  => $request->latitude_destino,
            'longitude_destino' => $request->longitude_destino,
            'morada_destino'    => $request->morada_destino,
            'agendado_para'     => $request->agendado_para,
            'status'            => 'pendente',
        ]);

        // Envia mensagem automática na conversa
        Mensagem::create([
            'conversa_id'  => $conversaId,
            'remetente_id' => auth()->id(),
            'texto'        => '📍 Encontro marcado! Ponto de encontro definido.',
            'tipo'         => 'encontro',
        ]);

        // Notifica o vendedor
        Notificacao::create([
            'id'      => Str::uuid(),
            'user_id' => $request->vendedor_id,
            'tipo'    => 'encontro_marcado',
            'dados'   => [
                'encontro_id'    => $encontro->id,
                'comprador_nome' => auth()->user()->name,
                'morada'         => $request->morada_destino,
                'agendado_para'  => $request->agendado_para,
            ],
        ]);

        return response()->json([
            'success'  => true,
            'encontro' => $encontro,
        ], 201);
    }

    // Iniciar encontro (activa o GPS em tempo real)
    public function iniciar($encontroId)
    {
        $encontro = $this->meuEncontro($encontroId);

        if ($encontro->status !== 'pendente') {
            return response()->json([
                'success' => false,
                'message' => 'Este encontro não pode ser iniciado.',
            ], 422);
        }

        $encontro->update([
            'status'      => 'activo',
            'iniciado_em' => now(),
        ]);

        return response()->json(['success' => true, 'encontro' => $encontro]);
    }

    // Actualizar localização GPS em tempo real (chamado a cada 3-5 segundos pelo Flutter)
    public function actualizarLocalizacao(Request $request, $encontroId)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'velocidade'=> 'nullable|numeric',
            'precisao'  => 'nullable|numeric',
        ]);

        $encontro = $this->meuEncontro($encontroId);

        if ($encontro->status !== 'activo') {
            return response()->json(['success' => false], 422);
        }

        // Guarda a localização actual
        EncontroLocalizacao::create([
            'encontro_id' => $encontroId,
            'user_id'     => auth()->id(),
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'velocidade'  => $request->velocidade,
            'precisao'    => $request->precisao,
        ]);

        // Devolve a última localização do OUTRO utilizador
        // O Flutter usa isto para actualizar o mapa em tempo real
        $outroUserId = $encontro->comprador_id === auth()->id()
            ? $encontro->vendedor_id
            : $encontro->comprador_id;

        $localizacaoOutro = EncontroLocalizacao::where('encontro_id', $encontroId)
            ->where('user_id', $outroUserId)
            ->latest()
            ->first();

        return response()->json([
            'success'           => true,
            'outro_utilizador'  => $localizacaoOutro,
        ]);
    }

    // Ver estado actual do encontro (posições dos dois)
    public function estado($encontroId)
    {
        $encontro = $this->meuEncontro($encontroId);

        $locComprador = EncontroLocalizacao::where('encontro_id', $encontroId)
            ->where('user_id', $encontro->comprador_id)
            ->latest()->first();

        $locVendedor = EncontroLocalizacao::where('encontro_id', $encontroId)
            ->where('user_id', $encontro->vendedor_id)
            ->latest()->first();

        return response()->json([
            'encontro'          => $encontro,
            'loc_comprador'     => $locComprador,
            'loc_vendedor'      => $locVendedor,
            'destino'           => [
                'latitude'  => $encontro->latitude_destino,
                'longitude' => $encontro->longitude_destino,
                'morada'    => $encontro->morada_destino,
            ],
        ]);
    }

    // Concluir encontro
    public function concluir($encontroId)
    {
        $encontro = $this->meuEncontro($encontroId);

        $encontro->update([
            'status'       => 'concluido',
            'concluido_em' => now(),
        ]);

        // Actualiza total de vendas do vendedor
        $encontro->vendedor->increment('total_vendas');

        // Dá pontos aos dois
        app(PontosController::class)->creditar(
            $encontro->vendedor_id, 'venda_concluida',
            $encontroId, Encontro::class
        );

        // Notifica os dois para avaliarem
        foreach ([$encontro->comprador_id, $encontro->vendedor_id] as $userId) {
            Notificacao::create([
                'id'      => Str::uuid(),
                'user_id' => $userId,
                'tipo'    => 'avaliacao_recebida',
                'dados'   => ['encontro_id' => $encontroId],
            ]);
        }

        return response()->json(['success' => true]);
    }

    // Cancelar encontro
    public function cancelar($encontroId)
    {
        $encontro = $this->meuEncontro($encontroId);

        $encontro->update(['status' => 'cancelado']);

        return response()->json(['success' => true]);
    }

    // Avaliar após encontro
    public function avaliar(Request $request, $encontroId)
    {
        $request->validate([
            'pontuacao'  => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        $encontro = Encontro::findOrFail($encontroId);

        // Determina quem está a avaliar quem
        $avaliadorId = auth()->id();
        $vendedorId  = $encontro->vendedor_id;
        $produtoId   = Conversa::find($encontro->conversa_id)?->produto_id;

        $avaliacao = Avaliacao::firstOrCreate(
            ['avaliador_id' => $avaliadorId, 'produto_id' => $produtoId],
            [
                'vendedor_id' => $vendedorId,
                'pontuacao'   => $request->pontuacao,
                'comentario'  => $request->comentario,
            ]
        );

        // Actualiza score de credibilidade do vendedor
        $mediaScore = Avaliacao::where('vendedor_id', $vendedorId)
            ->avg('pontuacao');

        $encontro->vendedor->update([
            'score_credibilidade' => round($mediaScore, 2),
        ]);

        // Dá pontos por avaliar
        app(PontosController::class)->creditar(
            $avaliadorId, 'avaliacao_recebida',
            $encontroId, Encontro::class
        );

        return response()->json(['success' => true, 'avaliacao' => $avaliacao]);
    }

    // Auxiliar — garante que o utilizador é parte do encontro
    private function meuEncontro($encontroId): Encontro
    {
        return Encontro::where(function ($q) {
            $q->where('comprador_id', auth()->id())
              ->orWhere('vendedor_id', auth()->id());
        })->findOrFail($encontroId);
    }
}