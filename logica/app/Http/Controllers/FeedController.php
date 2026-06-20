<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoDestaque;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // ─── Produtos em destaque (topo do feed) ────────────────────
        $destaques = Produto::with(['user', 'imagemPrincipal', 'categoria'])
            ->activos()
            ->whereHas('destaques', fn($q) => $q->activos())
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // ─── Feed personalizado ──────────────────────────────────────
        $query = Produto::with(['user', 'imagemPrincipal', 'categoria'])
            ->activos();

        // Prioriza produtos de quem o utilizador segue
        if ($user) {
            $seguindo = $user->follows()->pluck('following_id');
            if ($seguindo->isNotEmpty()) {
                $query->orderByRaw(
                    "CASE WHEN user_id IN ({$seguindo->implode(',')}) THEN 0 ELSE 1 END"
                );
            }

            // Prioriza pela província do utilizador
            if ($user->provincia) {
                $query->orderByRaw(
                    "CASE WHEN provincia = ? THEN 0 ELSE 1 END", [$user->provincia]
                );
            }
        }

        $query->orderBy('created_at', 'desc');

        return response()->json([
            'destaques' => $destaques,
            'feed'      => $query->paginate(20),
        ]);
    }
}