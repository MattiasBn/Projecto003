<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index()
    {
        return response()->json(
            Alerta::where('user_id', auth()->id())->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo'        => 'required|in:baixa_preco,produto_proximo,novo_do_vendedor',
            'produto_id'  => 'nullable|exists:produtos,id',
            'categoria_id'=> 'nullable|exists:categorias,id',
            'preco_maximo'=> 'nullable|numeric',
            'raio_km'     => 'nullable|integer|min:1|max:100',
        ]);

        $alerta = Alerta::create([
            ...$request->only([
                'tipo', 'produto_id', 'categoria_id',
                'preco_maximo', 'raio_km',
            ]),
            'user_id' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'alerta' => $alerta], 201);
    }

    public function destroy($id)
    {
        Alerta::where('user_id', auth()->id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}