<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto; // Importe o seu Model Produto
use App\Models\User;   // Importe o Model User para pegar o ID
use Faker\Factory as Faker;

class ProdutoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $faker = Faker::create(); // Usa Faker com localidade brasileira/portuguesa
        
        // Pega o ID de um usuário para ser o criador dos produtos (user_id)
        // Tentamos pegar o admin, se não houver, pega o primeiro user
        $user_creator = User::where('role', 'administrador')->first() ?? User::first();
        
        if (!$user_creator) {
            echo "Aviso: Nenhum usuário encontrado para associar aos produtos.\n";
            return;
        }

        $userId = $user_creator->id;

        // Cria 50 produtos aleatórios
        for ($i = 0; $i < 50; $i++) {
            Produto::create([
                'user_id' => $userId, // Quem criou o produto
                'updated_by' => $userId, // Quem atualizou por último
               'nome' => $faker->words(3, true), // Nome criativo para o produto
                'descricao' => $faker->paragraph(2), // 2 parágrafos de descrição
                'preco' => $faker->randomFloat(2, 10, 1000), // Preço entre 10.00 e 1000.00
                'quantidade' => $faker->numberBetween(0, 500), // Quantidade entre 0 e 500
            ]);
        }
    }
}