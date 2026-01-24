<?php

namespace App\Http\Controllers;

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
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'role'      => $request->role,
            'cargo'     => $request->cargo,
            'morada'    => $request->morada,
            'telefone'  => $request->telefone,
            'confirmado'=> $request->confirmado ?? 1
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

        $user->update([
            'name'      => $request->name      ?? $user->name,
            'email'     => $request->email     ?? $user->email,
            'role'      => $request->role      ?? $user->role,
            'cargo'     => $request->cargo     ?? $user->cargo,
            'morada'    => $request->morada    ?? $user->morada,
            'telefone'  => $request->telefone  ?? $user->telefone,
            'confirmado'=> $request->confirmado?? $user->confirmado,
        ]);

        return response()->json($user);
    }

    public function destroy($id)
    {
        User::destroy($id);

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