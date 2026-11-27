<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Random\Randomizer;

class DrawMatcher
{
    public function match(Collection $participants, Collection $restrictions): array
    {
        if ($participants->count() < 2) {
            $this->reject('São necessários pelo menos 2 participantes para realizar o sorteio.');
        }

        $random = new Randomizer;
        $ids = $random->shuffleArray($participants->modelKeys());
        $blocked = $restrictions->groupBy('participant_id');
        $candidates = [];
        foreach ($ids as $giver) {
            $excluded = $blocked->get($giver, collect())->pluck('cannot_draw_id')->push($giver)->all();
            $candidates[$giver] = $random->shuffleArray(array_values(array_diff($ids, $excluded)));
            if ($candidates[$giver] === []) {
                $this->reject($participants->find($giver)->name.' ficou sem destinatários possíveis. Remova uma restrição e tente novamente.');
            }
        }

        $assigned = [];
        foreach ($ids as $giver) {
            $visited = [];
            if (!$this->assign($giver, $candidates, $assigned, $visited)) {
                $this->reject('As restrições deixam alguns participantes disputando os mesmos destinatários. Remova ou ajuste uma restrição para permitir um par diferente para cada pessoa.');
            }
        }

        return array_flip($assigned);
    }

    private function assign(int $giver, array $candidates, array &$assigned, array &$visited): bool
    {
        foreach ($candidates[$giver] as $receiver) {
            if (isset($visited[$receiver])) {
                continue;
            }

            $visited[$receiver] = true;
            if (!isset($assigned[$receiver]) || $this->assign($assigned[$receiver], $candidates, $assigned, $visited)) {
                $assigned[$receiver] = $giver;

                return true;
            }
        }

        return false;
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['draw' => $message]);
    }
}
