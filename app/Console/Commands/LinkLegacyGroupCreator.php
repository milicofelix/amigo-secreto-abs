<?php

namespace App\Console\Commands;

use App\Models\AmigoSecreto\Group;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LinkLegacyGroupCreator extends Command
{
    protected $signature = 'amigo-secreto:vincular-grupo {group} {email} {--confirm-creator : Confirma que a conta é do criador original}';

    protected $description = 'Vincula um grupo antigo sem responsável ao criador confirmado';

    public function handle(): int
    {
        if (!$this->option('confirm-creator')) {
            $this->error('Confirme a autoria do grupo antes de usar --confirm-creator.');

            return self::FAILURE;
        }

        $creator = User::where('email', Str::lower(trim($this->argument('email'))))
            ->where('is_organizer', true)->first();
        if (!$creator) {
            $this->error('Conta de organizador não encontrada.');

            return self::FAILURE;
        }

        $updated = Group::whereKey($this->argument('group'))->whereNull('user_id')
            ->update(['user_id' => $creator->id]);

        if (!$updated) {
            $this->error('Grupo inexistente ou já vinculado.');

            return self::FAILURE;
        }

        $this->info('Grupo vinculado ao criador confirmado.');

        return self::SUCCESS;
    }
}
