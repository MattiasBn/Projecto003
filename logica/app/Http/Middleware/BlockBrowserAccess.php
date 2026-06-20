<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBrowserAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 0. SE FOR PREFLIGHT (OPTIONS), DEIXA PASSAR DIRETO PARA O CORS DO LARAVEL
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        // 1. Mata o Favicon
        if ($request->is('favicon.ico') || str_contains($request->getRequestUri(), 'favicon.ico')) {
            return response('', 404)
                ->header('Content-Type', 'image/x-icon')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');
        }

        // 2. Caixa Preta: Se a requisição aceita HTML e NÃO espera explicitamente JSON
        // Correção aqui: Adicionado $this-> para chamar o método da classe corretamente
        if ($request->acceptsHtml() && !$request->expectsJson()) {
            return $this->responseContent();
        }

        // 3. Deixa passar as requisições legítimas
        return $next($request);
    }

    /**
     * Função auxiliar para renderizar a tela 100% preta, sem cabeçalhos e sem rastros do Laravel
     */
    private function responseContent(): Response 
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gateway</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #000000 !important;
            overflow: hidden;
            cursor: default;
        }
    </style>
</head>
<body>
</body>
</html>
HTML;

        return response($html, 200)
            ->header('Content-Type', 'text/html')
            ->header('Server', 'Mixa-Gateway')
            ->header('X-Powered-By', 'Mixa-Core');
    }
}