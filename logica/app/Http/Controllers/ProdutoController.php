<?php
namespace App\Http\Controllers;

use App\Services\MovimentacaoService;
use Illuminate\Http\Request;
use App\Models\Produto;

class ProdutoController extends Controller
{
    public function index()
    {
        return response()->json(
            Produto::with(['produtosCriados', 'produtosEditados'])
                ->orderBy('nome')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $produto = Produto::create([
            'user_id'    => $request->user_id,   // vem do front
            'updated_by' => $request->user_id,
            'nome'       => $request->nome,
            'descricao'  => $request->descricao,
            'preco'      => $request->preco,
            'quantidade' => $request->quantidade,
        ]);

        //  REGISTRA MOVIMENTAÇÃO + LOG
        MovimentacaoService::registrar([
            'entity_type' => 'produto',
            'entity_id'   => $produto->id,
            'tipo'        => 'criacao',
            'quantidade'  => $produto->quantidade,
            'descricao'   => 'Produto criado',
            'performed_by'=> $request->user_id,
            'after'       => $produto->toArray(),
        ]);

        return response()->json($produto, 201);
    }

    public function show($id)
    {
        return response()->json(
            Produto::with(['produtosCriados','produtosEditados'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $before = $produto->toArray(); // estado antes

        $produto->update([
            'updated_by' => $request->updated_by,
            'nome'       => $request->nome       ?? $produto->nome,
            'descricao'  => $request->descricao  ?? $produto->descricao,
            'preco'      => $request->preco      ?? $produto->preco,
            'quantidade' => $request->quantidade ?? $produto->quantidade,
        ]);

        //  MOVIMENTAÇÃO + LOG
        MovimentacaoService::registrar([
            'entity_type' => 'produto',
            'entity_id'   => $produto->id,
            'tipo'        => 'atualizacao',
            'quantidade'  => $produto->quantidade,
            'descricao'   => 'Produto atualizado',
            'performed_by'=> $request->updated_by ?? $request->user_id ?? null,
            'before'      => $before,
            'after'       => $produto->toArray(),
        ]);

        return response()->json($produto);
    }

    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);
        $before = $produto->toArray();

        $produto->delete();

        //  MOVIMENTAÇÃO + LOG
        MovimentacaoService::registrar([
            'entity_type' => 'produto',
            'entity_id'   => $id,
            'tipo'        => 'remocao',
            'descricao'   => 'Produto removido',
            'performed_by'=> request()->user_id ?? null,
            'before'      => $before,
        ]);

        return response()->json(['msg' => 'Produto removido.']);
    }

    public function search(Request $request)
    {
        $termo = $request->query('q');

        $query = Produto::query();

        if (is_numeric($termo)) {
            $query->where('id', $termo);
        }

        $query->orWhere('nome', 'like', "%{$termo}%");

        return response()->json($query->get());
    }
}
