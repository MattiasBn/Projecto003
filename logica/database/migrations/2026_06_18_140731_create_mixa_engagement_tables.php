<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. DESTAQUES DE PRODUTOS ────────────────────────────────
        // Controla quais produtos aparecem no topo do feed e da pesquisa.
        // Por agora inactivo — o admin activa quando quiser monetizar.
        Schema::create('produto_destaques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', [
                'topo_pesquisa',  // aparece primeiro nos resultados
                'banner_feed',    // banner no topo do feed
                'selo_destaque',  // só mostra um selo dourado no card
            ])->default('selo_destaque');
            $table->timestamp('inicio_em');
            $table->timestamp('fim_em');
            $table->boolean('activo')->default(false); // admin controla
            $table->boolean('pago')->default(false);   // false = gratuito agora
            $table->decimal('valor_pago', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['activo', 'fim_em']); // para queries de feed rápidas
        });

        // ─── 2. SISTEMA DE PONTOS ────────────────────────────────────
        // Cada utilizador tem um saldo. Ganha pontos por acções no app.
        // Pontos criam vício — o utilizador quer acumular e gastar.
        Schema::create('user_pontos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
            $table->integer('saldo')->default(0);
            $table->integer('total_ganho')->default(0);
            $table->integer('total_gasto')->default(0);
            $table->integer('streak_dias')->default(0);       // dias consecutivos activo
            $table->date('ultimo_acesso')->nullable();         // para calcular streak
            $table->timestamps();
        });

        // ─── 3. HISTÓRICO DE PONTOS ──────────────────────────────────
        // Cada acção que gera ou gasta pontos fica registada aqui.
        // O utilizador vê o histórico e sente que está a progredir.
        Schema::create('pontos_transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['ganho', 'gasto']);
            $table->integer('pontos');
            $table->enum('motivo', [
                // Acções que ganham pontos
                'publicar_produto',      // +10 pts
                'venda_concluida',       // +50 pts
                'avaliacao_recebida',    // +15 pts
                'primeiro_login',        // +20 pts
                'completar_perfil',      // +30 pts
                'convidar_amigo',        // +40 pts
                'streak_7_dias',         // +25 pts
                'streak_30_dias',        // +100 pts
                // Acções que gastam pontos
                'destaque_produto',      // -X pts (quando activar monetização)
                'boost_feed',            // -X pts
            ]);
            $table->nullableMorphs('referencia'); // liga ao produto, encontro, etc.
            $table->text('descricao')->nullable(); // "Vendeste: Cadeira de escritório"
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // ─── 4. ALERTAS DE PREÇO E PROXIMIDADE ──────────────────────
        // O utilizador activa alertas. Quando o preço baixa ou aparece
        // um produto próximo, recebe notificação push automática.
        // ESTE é o maior driver de re-engajamento do app.
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', [
                'baixa_preco',       // avisa quando produto específico baixa de preço
                'produto_proximo',   // avisa quando produto da categoria aparece próximo
                'novo_do_vendedor',  // avisa quando vendedor seguido publica algo novo
            ]);
            $table->foreignId('produto_id')   // para alertas de preço específico
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('categoria_id') // para alertas de proximidade
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade');
            $table->decimal('preco_maximo', 10, 2)->nullable(); // filtro de preço
            $table->integer('raio_km')->default(10);            // raio de proximidade
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_disparo_em')->nullable(); // evita spam
            $table->timestamps();

            $table->index(['user_id', 'activo']);
        });

        // ─── 5. SUBSCRIÇÕES (INACTIVO ATÉ MONETIZAR) ────────────────
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');              // "Básico", "Pro", "Business"
            $table->decimal('preco', 8, 2);
            $table->integer('limite_produtos');  // quantos produtos pode publicar
            $table->integer('destaques_mes');    // destaques gratuitos incluídos
            $table->json('beneficios');          // lista de features em JSON
            $table->boolean('activo')->default(false); // admin activa quando quiser
            $table->timestamps();
        });

        Schema::create('subscricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plano_id')->constrained()->onDelete('cascade');
            $table->timestamp('inicio_em');
            $table->timestamp('fim_em');
            $table->enum('status', ['activa', 'expirada', 'cancelada'])->default('activa');
            $table->boolean('renovacao_auto')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // ─── 6. PAGAMENTOS (INACTIVO ATÉ MONETIZAR) ─────────────────
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('valor', 10, 2);
            $table->string('referencia')->unique(); // referência interna
            $table->enum('metodo', [
                'multicaixa',   // Angola — principal método
                'transferencia',
                'pontos',       // pagar com pontos acumulados
            ])->default('multicaixa');
            $table->enum('status', ['pendente', 'pago', 'falhado', 'reembolsado'])
                  ->default('pendente');
            $table->nullableMorphs('pagavel'); // subscrição ou destaque
            $table->timestamp('pago_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
        Schema::dropIfExists('subscricoes');
        Schema::dropIfExists('planos');
        Schema::dropIfExists('alertas');
        Schema::dropIfExists('pontos_transacoes');
        Schema::dropIfExists('user_pontos');
        Schema::dropIfExists('produto_destaques');
    }
};