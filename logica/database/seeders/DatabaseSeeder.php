<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Produto;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    { 
        $this->call([
         
            UserSeeder::class,    // 1. Rodar primeiro (cria o admin)
            ProdutoSeeder::class, // 2. Rodar em seguida (usa o ID do admin)
    
        ]);
        User::factory()->create([
            
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
