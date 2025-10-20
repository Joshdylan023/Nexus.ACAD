<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // ✅ Sincronizar integrações RH automaticamente
        
        // A cada hora (hourly)
        $schedule->command('hr:sync --type=colaboradores')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Sincronização RH hourly concluída');
            })
            ->onFailure(function () {
                \Log::error('Sincronização RH hourly falhou');
            });

        // Diariamente às 02:00 (full sync)
        $schedule->command('hr:sync --type=completo')
            ->dailyAt('02:00')
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping()
            ->runInBackground();

        // Semanalmente aos domingos às 03:00
        $schedule->command('hr:sync --type=estrutura')
            ->weeklyOn(0, '03:00')
            ->timezone('America/Sao_Paulo');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
