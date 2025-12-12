<?php
namespace App\Http\Controllers;

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
            'user_id'    => $request->user_id,  // sem auth, vem do front
            'updated_by' => $request->user_id,
            'nome'       => $request->nome,
            'descricao'  => $request->descricao,
            'preco'      => $request->preco,
            'quantidade' => $request->quantidade,
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

        $produto->update([
            'updated_by' => $request->updated_by,
            'nome'       => $request->nome       ?? $produto->nome,
            'descricao'  => $request->descricao  ?? $produto->descricao,
            'preco'      => $request->preco      ?? $produto->preco,
            'quantidade' => $request->quantidade ?? $produto->quantidade,
        ]);

        return response()->json($produto);
    }

    public function destroy($id)
    {
        Produto::destroy($id);
        return response()->json(['msg' => 'Produto removido.']);
    }
}
