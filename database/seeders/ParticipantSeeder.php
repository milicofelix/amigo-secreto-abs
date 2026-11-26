<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AmigoSecreto\Participant;
use Faker\Factory as Faker;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $faker = Faker::create('pt_BR');

        // ID do grupo (ajuste se necessário)
        $groupId = 3;

        $nomes = [
            'Andressa',
            'Paulo',
            'Alex',
            'Gabriela',
            'Pedro',
            'Roseli',
            'Zirdinha',
            'Daniele',
            'Rafael',
            'Laura',
        ];

        $phone_numbers = [
            '*************', 
            '*************', 
            '*************', 
            '*************', 
            '*************', 
            '*************', 
            '*************', 
            '*************', 
            '*************', 
            '*************', 
        ];

        //$phone_number = $faker->randomElement($phone_numbers);

        foreach ($nomes as $index => $nome) {
            Participant::create([
                'group_id'  => $groupId,
                'name'      => $nome,
                'gender'    => $faker->randomElement(['M', 'F']),
                'email'     => $faker->unique()->safeEmail(),
                'whatsapp'  => $phone_numbers[$index] ?? $faker->phoneNumber(),
                // 'wishlist'  => $faker->randomElement([
                //     'Perfume 212', 
                //     'Chocolate Lindt', 
                //     'Apple Watch', 
                //     'Playstation 4', 
                //     'Conjunto de roupa da Nike', 
                //     'Boné da Nike', 
                //     'Jogo de Camas', 
                //     'Caneca personalizada', 
                //     'Playstation 5', 
                //     'Camiseta personalizada Lula Ladrão', 
                //     'Caixa de Cerveja', 
                //     'Vale-presente para aniversariante',
                // ]),
                'wishlist'  => 'Acompanhar no grupo de WhatsApp da Familia Ricosti',
                'secret_key' => strtoupper(\Str::random(6)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
