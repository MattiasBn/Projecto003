<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\ProdutoImagem;
use App\Models\PesquisaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    // Feed principal — produtos activos paginados
    public function index(Request $request)
    {
        $query = Produto::with(['user', 'imagemPrincipal', 'categoria'])
            ->activos();

        // ─── Filtros de localização ──────────────────────────────────
        if ($request->provincia) {
            $query->porProvincia($request->provincia);
        }
        if ($request->municipio) {
            $query->porMunicipio($request->municipio);
        }
        if ($request->bairro) {
            $query->porBairro($request->bairro);
        }

        // ─── Filtro por proximidade GPS ──────────────────────────────
        if ($request->latitude && $request->longitude) {
            $raio = $request->raio_km ?? 10;
            $query->proximoDe($request->latitude, $request->longitude, $raio);
        }

        // ─── Filtros de produto ──────────────────────────────────────
        if ($request->categoria_id) {
            $query->porCategoria($request->categoria_id);
        }
        if ($request->preco_min || $request->preco_max) {
            $query->porPreco(
                $request->preco_min ?? 0,
                $request->preco_max ?? 999999999
            );
        }
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }
        if ($request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        // ─── Pesquisa por termo ──────────────────────────────────────
        if ($request->termo) {
            $termo = $request->termo;
            $query->where(function ($q) use ($termo) {
                $q->where('titulo', 'like', "%{$termo}%")
                  ->orWhere('descricao', 'like', "%{$termo}%");
            });

            // Regista a pesquisa para analytics
            PesquisaLog::create([
                'user_id'          => auth()->id(),
                'termo'            => $termo,
                'provincia'        => $request->provincia,
                'municipio'        => $request->municipio,
                'categoria'        => $request->categoria_id,
                'total_resultados' => $query->count(),
            ]);
        }

        // ─── Ordenação ───────────────────────────────────────────────
        $ordem = $request->ordem ?? 'recente';
        match ($ordem) {
            'preco_asc'    => $query->orderBy('preco', 'asc'),
            'preco_desc'   => $query->orderBy('preco', 'desc'),
            'mais_likes'   => $query->orderBy('total_likes', 'desc'),
            'mais_visto'   => $query->orderBy('visualizacoes', 'desc'),
            default        => $query->orderBy('created_at', 'desc'),
        };

        // Destaques sempre no topo
        $query->orderBy('destaque', 'desc');

        return response()->json(
            $query->paginate(20)
        );
    }

    // Detalhe de um produto
    public function show($id)
    {
        $produto = Produto::with([
            'user',
            'categoria',
            'imagens',
            'comentarios.user',
            'comentarios.respostas.user',
        ])->findOrFail($id);

        // Incrementa visualizações
        $produto->increment('visualizacoes');

        // Verifica se o user autenticado já deu like
        $jaGostou    = false;
        $jaDesconto  = false;

        if (auth()->check()) {
            $jaGostou   = $produto->gostos()->where('user_id', auth()->id())->exists();
            $jaDesconto = $produto->pedidosDesconto()->where('user_id', auth()->id())->exists();
        }

        return response()->json([
            'produto'     => $produto,
            'ja_gostou'   => $jaGostou,
            'ja_desconto' => $jaDesconto,
        ]);
    }

    // Criar produto
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descricao'   => 'required|string',
            'preco'       => 'required|numeric|min:0',
            'categoria_id'=> 'nullable|exists:categorias,id',
            'tipo'        => 'in:produto,servico',
            'estado'      => 'in:novo,usado',
            'provincia'   => 'required|string',
            'municipio'   => 'required|string',
            'bairro'      => 'nullable|string',
            'imagens'     => 'nullable|array|max:6',
            'imagens.*'   => 'image|max:5120', // 5MB por imagem
        ]);

        $produto = Produto::create([
            ...$request->only([
                'titulo', 'descricao', 'preco', 'categoria_id',
                'tipo', 'estado', 'disponibilidade',
                'provincia', 'municipio', 'bairro', 'referencia',
                'latitude', 'longitude',
            ]),
            'user_id' => auth()->id(),
        ]);

        // Guarda as imagens
        if ($request->hasFile('imagens')) {
            foreach ($request->file('imagens') as $index => $imagem) {
                $caminho = $imagem->store("produtos/{$produto->id}", 'public');
                ProdutoImagem::create([
                    'produto_id' => $produto->id,
                    'caminho'    => $caminho,
                    'url'        => Storage::url($caminho),
                    'principal'  => $index === 0,
                    'ordem'      => $index,
                ]);
            }
        }

        // Dá pontos por publicar
        app(PontosController::class)->creditar(
            auth()->id(), 'publicar_produto', $produto->id, Produto::class
        );

        return response()->json([
            'success' => true,
            'produto' => $produto->load('imagens'),
        ], 201);
    }

    // Editar produto
    public function update(Request $request, $id)
    {
        $produto = Produto::where('user_id', auth()->id())
                          ->findOrFail($id);

        $produto->update($request->only([
            'titulo', 'descricao', 'preco', 'categoria_id',
            'tipo', 'estado', 'status', 'disponibilidade',
            'provincia', 'municipio', 'bairro', 'referencia',
            'latitude', 'longitude',
        ]));

        return response()->json([
            'success' => true,
            'produto' => $produto,
        ]);
    }

    // Apagar produto
    public function destroy($id)
    {
        $produto = Produto::where('user_id', auth()->id())
                          ->findOrFail($id);
        $produto->delete(); // SoftDelete — recuperável pelo admin

        return response()->json(['success' => true]);
    }

    // Like / Desconto
    public function like(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|in:gosto,desconto',
        ]);

        $produto = Produto::findOrFail($id);

        $like = $produto->likes()
            ->where('user_id', auth()->id())
            ->where('tipo', $request->tipo)
            ->first();

        if ($like) {
            // Remove o like (toggle)
            $like->delete();
            $campo = $request->tipo === 'gosto' ? 'total_likes' : 'total_descontos';
            $produto->decrement($campo);
            return response()->json(['action' => 'removido']);
        }

        // Adiciona o like
        $produto->likes()->create([
            'user_id' => auth()->id(),
            'tipo'    => $request->tipo,
        ]);

        $campo = $request->tipo === 'gosto' ? 'total_likes' : 'total_descontos';
        $produto->increment($campo);

        // Dá pontos ao vendedor se atingir 10 gostos
        if ($request->tipo === 'gosto' && $produto->total_likes === 10) {
            app(PontosController::class)->creditar(
                $produto->user_id, 'avaliacao_recebida', $produto->id, Produto::class
            );
        }

        return response()->json(['action' => 'adicionado']);
    }
}