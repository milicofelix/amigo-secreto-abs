<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ManageSuperAdmin extends Command
{
    protected $signature = 'amigo-secreto:super-admin {email} {--revoke : Revoga o acesso ao painel global}';

    protected $description = 'Concede ou revoga o acesso de Super Admin em uma conta de organizador existente';

    public function handle(): int
    {
        $organizer = User::where('email', Str::lower(trim($this->argument('email'))))
            ->where('is_organizer', true)->first();

        if (!$organizer) {
            $this->error('Conta de organizador habilitada não encontrada.');

            return self::FAILURE;
        }

        $revoke = (bool) $this->option('revoke');
        $organizer->forceFill(['is_super_admin' => !$revoke])->save();
        $this->info($revoke ? 'Acesso de Super Admin revogado.' : 'Acesso de Super Admin concedido.');

        return self::SUCCESS;
    }
}
