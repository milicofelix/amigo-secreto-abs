<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AmigoSecreto\Restriction;

class RestrictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupId = 1;

        // Exemplo: impedir que certas pessoas tirem seus pares ou familiares
        $restricoes = [
            ['giver_id' => 1, 'cannot_draw_id' => 2], // Adriano não pode tirar Shislene
            ['giver_id' => 2, 'cannot_draw_id' => 1], // Shislene não pode tirar Adriano
            ['giver_id' => 3, 'cannot_draw_id' => 5], // Cassilda não pode tirar Sueli
            ['giver_id' => 5, 'cannot_draw_id' => 3], // Sueli não pode tirar Cassilda
        ];

        foreach ($restricoes as $r) {
            Restriction::create([
                'group_id'        => $groupId,
                'participant_id'  => $r['giver_id'],
                'cannot_draw_id'  => $r['cannot_draw_id'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
