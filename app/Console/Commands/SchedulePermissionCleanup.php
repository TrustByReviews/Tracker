<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schedule;

class SchedulePermissionCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:schedule-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule automatic cleanup of expired permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🕐 Configurando limpieza automática de permisos expirados...');
        
        // Schedule daily cleanup at 2:00 AM
        Schedule::command('permissions:cleanup')
            ->daily()
            ->at('02:00')
            ->withoutOverlapping()
            ->runInBackground();
        
        $this->info('✅ Limpieza automática programada para ejecutarse diariamente a las 2:00 AM');
        $this->info('📝 Para verificar el estado: php artisan schedule:list');
        $this->info('📝 Para ejecutar manualmente: php artisan schedule:run');
        
        return 0;
    }
} 