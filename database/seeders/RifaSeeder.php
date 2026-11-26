<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rifas\Rifa;
use App\Models\Rifas\RifaNome;
use Carbon\Carbon;

class RifaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --------------------------------------------
        // CONFIGURAÇÃO
        // --------------------------------------------
        $rifaId = 1;                    // ID da rifa que deseja popular
        $quantidadeNomes = 10;          // Quantos nomes quer gerar
        $faker = \Faker\Factory::create('pt_BR');

        // --------------------------------------------
        // GERAR NOMES DINÂMICOS
        // --------------------------------------------
        for ($i = 1; $i <= $quantidadeNomes; $i++) {

            // Nome do número da cartela (ex: 01, 02, 03...)
            $nomeDaCartela = mb_strtoupper($faker->firstNameFemale(), 'UTF-8');

            // Status aleatório
            $status = collect(['disponivel', 'reservado', 'pago'])->random();

            // Dados do comprador
            $compradorNome = null;
            $compradorWhatsapp = null;
            $compradoEm = null;

            // Preencher comprador somente se não for disponível
            if ($status !== 'disponivel') {
                $compradorNome = $faker->firstName(); 
                $compradorWhatsapp = '11' . $faker->numerify('9########'); 
                $compradoEm = Carbon::now()->subDays(rand(0, 10));
            }

            RifaNome::create([
                'rifa_id'            => $rifaId,
                'nome'               => $nomeDaCartela,
                'status'             => $status,
                'comprador_nome'     => $compradorNome,
                'comprador_whatsapp' => $compradorWhatsapp,
                'comprado_em'        => $compradoEm,
            ]);
        }

        echo "Seeder da rifa executado com sucesso! Gerados {$quantidadeNomes} nomes.\n";
    }
}
