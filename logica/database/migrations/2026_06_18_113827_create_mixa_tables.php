<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. CATEGORIAS ──────────────────────────────────────────
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('icone')->nullable();
            $table->string('cor')->nullable(); // cor hex para UI (#FF5733)
            $table->boolean('activa')->default(true);
            $table->integer('ordem')->default(0); // para ordenar no feed
            $table->timestamps();
        });

        // ─── 2. PRODUTOS ────────────────────────────────────────────
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained()->onDelete('set null');

            // Conteúdo
            $table->string('titulo');
            $table->text('descricao');
            $table->decimal('preco', 10, 2);
            $table->enum('tipo', ['produto', 'servico'])->default('produto');
            $table->enum('estado', ['novo', 'usado'])->default('usado');
            $table->enum('status', ['activo', 'reservado', 'vendido', 'inactivo', 'removido'])->default('activo');
            $table->string('disponibilidade')->nullable(); // "dias úteis", "fins de semana", etc.

            // Localização completa (província → município → bairro)
            $table->string('provincia');
            $table->string('municipio');
            $table->string('bairro')->nullable();
            $table->string('referencia')->nullable(); // "perto do Shoprite", etc.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Métricas
            $table->boolean('destaque')->default(false);
            $table->integer('visualizacoes')->default(0);
            $table->integer('total_likes')->default(0);       // cache para performance
            $table->integer('total_descontos')->default(0);   // quantos querem desconto
            $table->integer('total_comentarios')->default(0); // cache para performance

            $table->timestamps();
            $table->softDeletes(); // permite recuperar produtos apagados

            // Índices para pesquisa rápida
            $table->index(['provincia', 'municipio', 'bairro']);
            $table->index(['categoria_id', 'status']);
            $table->index(['latitude', 'longitude']);
            $table->index('preco');
        });

        // ─── 3. IMAGENS DOS PRODUTOS ────────────────────────────────
        Schema::create('produto_imagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->string('caminho'); // path no storage
            $table->string('url')->nullable(); // URL pública
            $table->boolean('principal')->default(false); // imagem de capa
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // ─── 4. LIKES (dois tipos: gosto + desconto) ────────────────
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['gosto', 'desconto'])->default('gosto');
            // 'gosto'    = ❤️ gostei do produto
            // 'desconto' = 🏷️ quero desconto (sinal ao vendedor)
            $table->timestamps();
            $table->unique(['user_id', 'produto_id', 'tipo']); // um de cada tipo por user
        });

        // ─── 5. COMENTÁRIOS (com threads/respostas) ─────────────────
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->foreignId('comentario_pai_id')
                  ->nullable()
                  ->constrained('comentarios')
                  ->onDelete('cascade');
            $table->text('texto');
            $table->integer('total_respostas')->default(0); // cache
            $table->timestamps();
            $table->softDeletes();

            $table->index(['produto_id', 'comentario_pai_id']);
        });

        // ─── 6. FOLLOWS ─────────────────────────────────────────────
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['follower_id', 'following_id']);
        });

        // ─── 7. AVALIAÇÕES (pós-encontro) ───────────────────────────
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avaliador_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendedor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('pontuacao'); // 1 a 5 estrelas
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->unique(['avaliador_id', 'produto_id']);
        });

        // ─── 8. CONVERSAS ───────────────────────────────────────────
        Schema::create('conversas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('produto_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('ultima_mensagem_id')->nullable();
            $table->timestamp('ultima_actividade_em')->nullable();
            $table->timestamps();
            $table->unique(['user1_id', 'user2_id', 'produto_id']);
        });

        // ─── 9. MENSAGENS ───────────────────────────────────────────
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->constrained()->onDelete('cascade');
            $table->foreignId('remetente_id')->constrained('users')->onDelete('cascade');
            $table->text('texto');
            $table->enum('tipo', ['texto', 'imagem', 'localizacao', 'encontro'])->default('texto');
            // 'encontro' = mensagem especial quando se marca um encontro
            $table->boolean('lida')->default(false);
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();

            $table->index(['conversa_id', 'created_at']);
        });

        // ─── 10. ENCONTROS ──────────────────────────────────────────
        Schema::create('encontros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->constrained()->onDelete('cascade');
            $table->foreignId('comprador_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendedor_id')->constrained('users')->onDelete('cascade');

            // Ponto de destino combinado
            $table->decimal('latitude_destino', 10, 7);
            $table->decimal('longitude_destino', 10, 7);
            $table->string('morada_destino')->nullable();

            $table->enum('status', ['pendente', 'activo', 'concluido', 'cancelado'])->default('pendente');
            $table->datetime('agendado_para')->nullable();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();

            $table->timestamps();
        });

        // ─── 11. LOCALIZAÇÕES EM TEMPO REAL (GPS como Uber) ─────────
        Schema::create('encontro_localizacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encontro_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('velocidade', 5, 2)->nullable(); // km/h
            $table->decimal('precisao', 6, 2)->nullable();   // metros de precisão do GPS
            $table->timestamps();

            // Índice para buscar última localização rapidamente
            $table->index(['encontro_id', 'user_id', 'created_at']);
        });

        // ─── 12. OTP VERIFICAÇÕES ───────────────────────────────────
        Schema::create('otp_verificacoes', function (Blueprint $table) {
            $table->id();
            $table->string('telefone')->index();
            $table->string('codigo', 6);
            $table->boolean('usado')->default(false);
            $table->timestamp('expira_em');
            $table->timestamps();
        });

        // ─── 13. DENÚNCIAS ──────────────────────────────────────────
        Schema::create('denuncias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('reportavel'); // produto ou user
            $table->enum('motivo', [
                'spam',
                'produto_falso',
                'preco_abusivo',
                'conteudo_inapropriado',
                'fraude',
                'outro'
            ]);
            $table->text('descricao')->nullable();
            $table->enum('status', ['pendente', 'analisada', 'resolvida'])->default('pendente');
            $table->timestamps();
        });

        // ─── 14. NOTIFICAÇÕES ───────────────────────────────────────
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tipo');
            // Exemplos de tipo:
            // 'novo_like', 'novo_comentario', 'nova_mensagem',
            // 'novo_seguidor', 'encontro_marcado', 'avaliacao_recebida'
            $table->json('dados'); // dados dinâmicos da notificação
            $table->string('fcm_token')->nullable(); // token do dispositivo
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lida_em']);
        });

        // ─── 15. LOG DE PESQUISAS ────────────────────────────────────
        Schema::create('pesquisas_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('termo');
            $table->string('provincia')->nullable();
            $table->string('municipio')->nullable();
            $table->string('categoria')->nullable();
            $table->integer('total_resultados')->default(0);
            $table->timestamps();

            $table->index('termo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesquisas_log');
        Schema::dropIfExists('notificacoes');
        Schema::dropIfExists('denuncias');
        Schema::dropIfExists('otp_verificacoes');
        Schema::dropIfExists('encontro_localizacoes');
        Schema::dropIfExists('encontros');
        Schema::dropIfExists('mensagens');
        Schema::dropIfExists('conversas');
        Schema::dropIfExists('avaliacoes');
        Schema::dropIfExists('follows');
        Schema::dropIfExists('comentarios');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('produto_imagens');
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('categorias');
    }
};