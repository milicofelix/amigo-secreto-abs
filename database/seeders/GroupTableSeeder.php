<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AmigoSecreto\Group;

class GroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Group::create([
            'user_id'        => 1,
            'name'           => 'Amigo Secreto de Final de Ano',
            'description'    => 'Um grupo especial para celebrar a amizade e trocar presentes.',
            'budget_min'     => 100,
            'budget_max'     => 1000000,
            'event_date'     => '2025-12-24',
            'event_location' => 'Casa da Sula',
        ]);
    }
}
