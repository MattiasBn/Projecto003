<?php

namespace App\Http\Controllers;

use App\Models\Conversa;
use App\Models\Mensagem;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConversaController extends Controller
{
    // Lista todas as conversas do utilizador autenticado
    public function index()
    {
        $conversas = Conversa::with([
            'user1:id,name,avatar',
            'user2:id,name,avatar',
            'produto:id,titulo',
            'ultimaMensagem',
        ])
        ->where('user1_id', auth()->id())
        ->orWhere('user2_id', auth()->id())
        ->orderBy('ultima_actividade_em', 'desc')
        ->get()
        ->map(function ($conversa) {
            // Devolve sempre o "outro" utilizador da conversa
            $conversa->outro_user = $conversa->outroUser(auth()->id());
            return $conversa;
        });

        return response()->json($conversa);
    }

    // Iniciar ou abrir conversa sobre um produto
    public function iniciarOuAbrir(Request $request)
    {
        $request->validate([
            'outro_user_id' => 'required|exists:users,id',
            'produto_id'    => 'nullable|exists:produtos,id',
        ]);

        $meuId     = auth()->id();
        $outroId   = $request->outro_user_id;
        $produtoId = $request->produto_id;

        // Garante que user1 é sempre o menor ID (evita duplicados)
        $user1 = min($meuId, $outroId);
        $user2 = max($meuId, $outroId);

        $conversa = Conversa::firstOrCreate(
            [
                'user1_id'   => $user1,
                'user2_id'   => $user2,
                'produto_id' => $produtoId,
            ],
            ['ultima_actividade_em' => now()]
        );

        return response()->json([
            'conversa' => $conversa->load([
                'user1:id,name,avatar',
                'user2:id,name,avatar',
                'produto:id,titulo',
            ]),
        ]);
    }

    // Mensagens de uma conversa (paginadas)
    public function mensagens($conversaId)
    {
        $conversa = Conversa::where(function ($q) {
            $q->where('user1_id', auth()->id())
              ->orWhere('user2_id', auth()->id());
        })->findOrFail($conversaId);

        // Marca mensagens como lidas
        Mensagem::where('conversa_id', $conversaId)
            ->where('remetente_id', '!=', auth()->id())
            ->whereNull('lida_em')
            ->update(['lida' => true, 'lida_em' => now()]);

        $mensagens = Mensagem::with('remetente:id,name,avatar')
            ->where('conversa_id', $conversaId)
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return response()->json($mensagens);
    }

    // Enviar mensagem
    public function enviarMensagem(Request $request, $conversaId)
    {
        $request->validate([
            'texto' => 'required|string|max:2000',
            'tipo'  => 'in:texto,imagem,localizacao,encontro',
        ]);

        $conversa = Conversa::where(function ($q) {
            $q->where('user1_id', auth()->id())
              ->orWhere('user2_id', auth()->id());
        })->findOrFail($conversaId);

        $mensagem = Mensagem::create([
            'conversa_id'  => $conversaId,
            'remetente_id' => auth()->id(),
            'texto'        => $request->texto,
            'tipo'         => $request->tipo ?? 'texto',
        ]);

        // Actualiza última actividade da conversa
        $conversa->update([
            'ultima_mensagem_id'   => $mensagem->id,
            'ultima_actividade_em' => now(),
        ]);

        // Notifica o outro utilizador
        $outroUserId = $conversa->outroUser(auth()->id())->id;
        $this->notificar($outroUserId, $mensagem, $conversa);

        return response()->json([
            'success'  => true,
            'mensagem' => $mensagem->load('remetente:id,name,avatar'),
        ], 201);
    }

    private function notificar(int $userId, Mensagem $mensagem, Conversa $conversa): void
    {
        Notificacao::create([
            'id'      => Str::uuid(),
            'user_id' => $userId,
            'tipo'    => 'nova_mensagem',
            'dados'   => [
                'conversa_id'    => $conversa->id,
                'remetente_nome' => auth()->user()->name,
                'remetente_avatar'=> auth()->user()->avatar,
                'preview'        => substr($mensagem->texto, 0, 60),
            ],
        ]);
    }
}