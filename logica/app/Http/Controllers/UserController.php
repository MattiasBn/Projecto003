<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OtpVerificacao;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class UserController extends Controller
{
    // ─── 1. Verificar identificador (email ou telefone) ─────────────

    public function  checkAndAuthenticate (Request $request)
    {
        $request->validate(['identity' => 'required|string']);
        $id = $request->identity;

        $isEmail = filter_var($id, FILTER_VALIDATE_EMAIL);

        $user = $isEmail
            ? User::where('email', $id)->first()
            : User::where('telefone', $id)->first();
    
        // Registo automático se não existir
        if (!$user) {
            $user = User::create([
                'name'     => $isEmail ? explode('@', $id)[0] : 'User_' . substr($id, -4),
                'email'    => $isEmail ? $id : null,
                'telefone' => !$isEmail ? $id : null,
                'password' => bcrypt(str()->random(16)),
            ]);
        }

        if ($isEmail) {
            // Por agora devolve sucesso; podes adicionar magic link depois
            return response()->json([
                'success' => true,
                'action'  => 'email_registered',
                'message' => 'E-mail registado.',
            ]);
        }

        return $this->enviarOtp($id);
    }

    // ─── 2. Verificar OTP ───────────────────────────────────────────

   public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'otp'      => 'required|string|size:6',
        ]);

        $telefone = $request->identity;
        $codigo   = $request->otp;

        // 1. Procura o registo ignorando a comparação direta de data do banco
        $verificacao = OtpVerificacao::where('telefone', $telefone)
            ->where('codigo', $codigo)
            ->where('usado', false)
            ->first();

        // 2. Se não existir OU se a diferença entre agora e a atualização for maior que 5 minutos
        if (!$verificacao || Carbon::parse($verificacao->updated_at)->diffInMinutes(Carbon::now()) > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Código OTP incorreto ou expirado...',
            ], 422);
        }

        // Marcar como usado para ninguém reutilizar o mesmo código
        $verificacao->update(['usado' => true]);

        $user = User::where('telefone', $telefone)->first();
        
        if ($user) {
            $user->update(['phone_verified_at' => \Carbon\Carbon::now()]);
        }

        return $this->responderComToken($user, 'Sessão iniciada via telefone.');
    }
    // ─── 3. Login com Google ────────────────────────────────────────

    public function loginWithGoogle(Request $request)
    {
        $request->validate(['google_token' => 'required|string']);

        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->google_token,
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Token Google inválido.'], 401);
        }

        $googleUser = $response->json();
        $audience   = $googleUser['aud'] ?? '';

        $allowedClients = config('services.google.allowed_clients', [
            config('services.google.client_id'),
        ]);

        if (!in_array($audience, $allowedClients)) {
            return response()->json(['message' => 'Token não autorizado.'], 401);
        }

        if (($googleUser['email_verified'] ?? 'false') !== 'true') {
            return response()->json(['message' => 'E-mail Google não verificado.'], 401);
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser['email']],
            [
                'name'              => $googleUser['name'] ?? explode('@', $googleUser['email'])[0],
                'google_id'         => $googleUser['sub'],
                'provider'          => 'google',
                'email_verified_at' => Carbon::now(),
                'password'          => bcrypt(str()->random(16)),
            ]
        );

        return $this->responderComToken($user, 'Sessão iniciada via Google.');
    }

    // ─── 4. Logout ──────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sessão encerrada.']);
    }

    // ─── Auxiliares ─────────────────────────────────────────────────

   private function enviarOtp(string $telefone)
    {
        $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // O updateOrCreate agora força a atualização do timer 'updated_at' a cada novo envio
        OtpVerificacao::updateOrCreate(
            ['telefone' => $telefone],
            [
                'codigo'    => $codigo,
                'usado'     => false,
                'expira_em' => Carbon::now()->addMinutes(5), // Mantido por compatibilidade da tabela
            ]
        );

        // TODO: integrar provider SMS (Infobip / Africa's Talking)
        return response()->json([
            'success'           => true,
            'action'            => 'verify_otp',
            'message'           => 'Código enviado.',
            'dev_code_preview'  => $codigo, // ⚠️ remover em produção real
        ]);
    }
    private function responderComToken(User $user, string $message)
    {
        $token = $user->createToken('mixa_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $message,
            'token'   => $token,
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'telefone' => $user->telefone,
                'avatar'   => $user->avatar,
                'provincia'=> $user->provincia,
            ],
        ]);
    }
}