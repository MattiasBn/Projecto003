<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Importe o seu Model User
use Illuminate\Support\Facades\Hash; // Usaremos o Hash para senhas
use Carbon\Carbon; // Usaremos o Carbon para o email_verified_at

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // --- 1. Usuário Administrador Fixo (Para login garantido) ---
        User::create([
            'name' => 'Admin Mestre',
            'email' => 'admin@projeto.com',
            'password' => Hash::make('12345678'), // Senha fácil para teste: 12345678
            'role' => 'administrador', // Conforme sua coluna 'role' enum
            'cargo' => 'Chief Technology Officer (CTO)',
            'morada' => 'Rua Principal, 100, Cidade Admin',
            'telefone' => '912345678',
            'email_verified_at' => Carbon::now(),
            'confirmado' => 1,
        ]);

        // --- 2. Usuários Aleatórios usando o Factory ---
        // (Assumindo que você criou um UserFactory para gerar dados complexos)
        // Se você não tem um UserFactory, pode descomentar o bloco abaixo (Opção B)

        \App\Models\User::factory()->count(10)->create();
    }
}