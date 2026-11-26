<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rifas\Rifa;
use App\Models\Rifas\RifaNome;

class RifaNomesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $rifa = Rifa::create([
            'titulo' => 'Cartela da Sorte',
            'premio' => 'R$ 200,00',
            'valor'  => 5.00
        ]);

        $nomes = [
            ['MARIA','GRAZIELA','CLAUDIA','REGINA','VILMA'],
            ['FERNANDA','EMÍLIA','DANIELA','SANDRA','ZULEIKA'],
            ['MARISA','EMANOELE','DOLORES','CÉLIA','BERENICE'],
            ['SOLANGE','ÂNGELA','GUIOMAR','MARGOT','DALILA'],
            ['ANADARK','CRISTINA','DÉBORA','ELIANE','GENILDA'],
            ['ROSÁLIA','SIMONE','HELENA','VALQUÍRIA','ANDRÉA'],
            ['CLEIDE','ELAINE','ROBERTA','GLÓRIA','SUZANA'],
            ['ADRIANA','OLGA','ISABEL','JULIANA','KÁTIA'],
            ['IRENE','DAYANE','LETÍCIA','MARTA','PAULA'],
            ['MARCELA','OLÍVIA','JOANA','LENICE','FÁTIMA'],
            ['ROSIMEIRE','DENISE','ELIZABETH','CLAUDETTE','MIKAELE'],
            ['CELESTE','AURORA','ROSÂNGELA','LOURDES','ISAURA'],
            ['MÔNICA','LEONOR','JANETE','RAQUEL','VIRGÍNIA'],
            ['CAMILA','ROSELI','VALÉRIA','ADELAIDE','SALETE'],
            ['RAISSA','NATACHA','BRUNA','ARIANE','CAROLINE']
        ];

        foreach ($nomes as $linha) {
            foreach ($linha as $nome) {
                RifaNome::create([
                    'rifa_id' => $rifa->id,
                    'nome' => $nome
                ]);
            }
        }
    }
}
