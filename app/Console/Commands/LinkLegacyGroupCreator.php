<?php

namespace App\Console\Commands;

use App\Models\AmigoSecreto\Group;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

        $updated = DB::transaction(function () use ($creator) {
            $group = Group::whereKey($this->argument('group'))->whereNull('user_id')->lockForUpdate()->first();
            if (!$group) {
                return false;
            }

            $group->user()->associate($creator)->save();
            DB::table('group_creator_confirmations')->insert([
                'group_id' => $group->id,
                'creator_id' => $creator->id,
                'group_name' => $group->name,
                'creator_name' => $creator->name,
                'confirmed_at' => now(),
            ]);

            return true;
        });

        if (!$updated) {
            $this->error('Grupo inexistente ou já vinculado.');

            return self::FAILURE;
        }

        $this->info('Grupo vinculado ao criador confirmado.');

        return self::SUCCESS;
    }
}
