<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetTwoFactorAuthentication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-2fa {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zera a autenticação em dois fatores de um usuário (uso emergencial em caso de perda de acesso ao dispositivo e aos códigos de recuperação)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("Usuário com e-mail '{$this->argument('email')}' não encontrado.");

            return self::FAILURE;
        }

        if (! $this->confirm("Isso vai remover a autenticação em dois fatores de '{$user->email}', exigindo que o usuário a configure novamente no próximo login. Confirmar?")) {
            return self::FAILURE;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->info("Autenticação em dois fatores removida para '{$user->email}'.");

        return self::SUCCESS;
    }
}
