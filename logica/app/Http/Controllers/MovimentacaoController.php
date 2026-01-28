<?php

use App\Models\Movimentacao;

class MovimentacaoController extends Controller
{
    public function index()
    {
        return Movimentacao::latest()->get();
    }

    public function porEntidade(string $tipo, int $id)
    {
        return Movimentacao::where('entity_type', $tipo)
            ->where('entity_id', $id)
            ->latest()
            ->get();
    }
}
