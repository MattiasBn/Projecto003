<?php

namespace App\Http\Controllers;

use App\Services\MovimentacaoService;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::orderBy('name')->get());
    }

    public function store(Request $request)
{
    $user = User::create([
        'name'       => $request->name,
        'email'      => $request->email,
        'password'   => bcrypt($request->password),
        'role'       => $request->role,
        'cargo'      => $request->cargo,
        'morada'     => $request->morada,
        'telefone'   => $request->telefone,
        'confirmado' => $request->confirmado ?? 1
    ]);

    //  LOG DE CRIAÇÃO DE USER
    MovimentacaoService::registrar([
        'entity_type'  => 'user',
        'entity_id'    => $user->id,
        'tipo'         => 'criacao',
        'descricao'    => 'Utilizador criado',
        'performed_by' => $request->admin_id ?? null, // quem criou
    ]);

    return response()->json($user, 201);
}


    public function show($id)
    {
        return response()->json(User::findOrFail($id));
    }

   public function update(Request $request, $id)
{
    $user = User::findOrFail($id);
    $before = $user->toArray(); //  estado antes

    $user->update([
        'name'       => $request->name       ?? $user->name,
        'email'      => $request->email      ?? $user->email,
        'role'       => $request->role       ?? $user->role,
        'cargo'      => $request->cargo      ?? $user->cargo,
        'morada'     => $request->morada     ?? $user->morada,
        'telefone'   => $request->telefone   ?? $user->telefone,
        'confirmado' => $request->confirmado ?? $user->confirmado,
    ]);

    //  LOG DE ATUALIZAÇÃO
    MovimentacaoService::registrar([
        'entity_type'  => 'user',
        'entity_id'    => $user->id,
        'tipo'         => 'atualizacao',
        'descricao'    => 'Dados do utilizador atualizados',
        'performed_by' => $request->admin_id ?? null,
        'before'       => $before,
        'after'        => $user->toArray(),
    ]);

    return response()->json($user);
}


    


    public function destroy($id)
{
    $user = User::findOrFail($id);
    $before = $user->toArray();

    $user->delete();

    //  LOG DE REMOÇÃO
    MovimentacaoService::registrar([
        'entity_type'  => 'user',
        'entity_id'    => $id,
        'tipo'         => 'remocao',
        'descricao'    => 'Utilizador removido',
        'performed_by' => request()->admin_id ?? null,
        'before'       => $before,
    ]);

    return response()->json(['msg' => 'Usuário removido.']);
}



   public function search(Request $request)
{
    $termo = $request->query('q');

   $users = User::select('id', 'name', 'email')
    ->where(function ($query) use ($termo) {
        if (is_numeric($termo)) {
            $query->where('id', $termo);
        }

        $query->orWhere('name', 'like', "%{$termo}%")
              ->orWhere('email', 'like', "%{$termo}%");
    })
    ->paginate(10);

     return response()->json($users);

}
}