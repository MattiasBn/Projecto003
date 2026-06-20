<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use Illuminate\Http\Request;

class NotificacaoController extends Controller
{
    // Lista notificações do utilizador
    public function index()
    {
        $notificacoes = Notificacao::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $naoLidas = Notificacao::where('user_id', auth()->id())
            ->whereNull('lida_em')
            ->count();

        return response()->json([
            'notificacoes' => $notificacoes,
            'nao_lidas'    => $naoLidas,
        ]);
    }

    // Marcar uma como lida
    public function marcarLida($id)
    {
        Notificacao::where('user_id', auth()->id())
            ->findOrFail($id)
            ->update(['lida_em' => now()]);

        return response()->json(['success' => true]);
    }

    // Marcar todas como lidas
    public function marcarTodasLidas()
    {
        Notificacao::where('user_id', auth()->id())
            ->whereNull('lida_em')
            ->update(['lida_em' => now()]);

        return response()->json(['success' => true]);
    }

    // Guardar token FCM do dispositivo
    public function guardarFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string']);

        auth()->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['success' => true]);
    }
}