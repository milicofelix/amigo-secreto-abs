<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ManageOrganizerAccount extends Command
{
    protected $signature = 'amigo-secreto:organizador {email} {--enable-existing : Habilita uma conta existente sem alterar a senha}';

    protected $description = 'Cria ou habilita uma conta de organizador pelo terminal';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        if (Validator::make(['email' => $email], ['email' => 'required|email|max:255'])->fails()) {
            $this->error('Informe um e-mail válido.');

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();
        if ($this->option('enable-existing')) {
            if (!$existing) {
                $this->error('Conta inexistente.');

                return self::FAILURE;
            }

            $existing->forceFill(['is_organizer' => true])->save();
            $this->info('Conta habilitada como organizador.');

            return self::SUCCESS;
        }

        if ($existing || !$this->input->isInteractive()) {
            $this->error('Use o modo interativo para criar uma conta nova ou --enable-existing para habilitar uma existente.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nome'));
        $password = $this->secret('Senha (mínimo de 12 caracteres)', false);
        $confirmation = $this->secret('Repita a senha', false);
        if ($name === '' || mb_strlen($name) > 255 || !is_string($password) || mb_strlen($password) < 12
            || strlen($password) > 72 || $password !== $confirmation) {
            $this->error('Confira o nome e a senha. Use pelo menos 12 caracteres e até 72 bytes.');

            return self::FAILURE;
        }

        (new User(['name' => $name, 'email' => $email, 'password' => $password]))
            ->forceFill(['is_organizer' => true])->save();

        $this->info('Conta de organizador criada.');

        return self::SUCCESS;
    }
}
