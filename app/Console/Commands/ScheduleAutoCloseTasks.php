<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ScheduleAutoCloseTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:schedule-auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule the auto-close tasks command to run every 30 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Configurando cron job para auto-cierre de tareas...');

        try {
            // Obtener la ruta del proyecto
            $projectPath = base_path();
            $phpPath = PHP_BINARY;
            $artisanPath = $projectPath . '/artisan';
            $logPath = $projectPath . '/storage/logs/auto-close.log';

            // Crear el comando cron
            $cronCommand = "*/30 * * * * cd {$projectPath} && {$phpPath} {$artisanPath} tasks:auto-close >> {$logPath} 2>&1";

            $this->info('Comando cron generado:');
            $this->line($cronCommand);
            $this->newLine();

            // Verificar si el comando ya existe en crontab
            $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
            
            if (strpos($currentCrontab, 'tasks:auto-close') !== false) {
                $this->warn('⚠️ El comando ya está programado en crontab');
                $this->info('Para ver el crontab actual: crontab -l');
                $this->info('Para editar el crontab: crontab -e');
                return 0;
            }

            // Crear archivo temporal con el nuevo crontab
            $tempFile = tempnam(sys_get_temp_dir(), 'crontab');
            file_put_contents($tempFile, $currentCrontab . "\n" . $cronCommand . "\n");

            // Instalar el nuevo crontab
            $output = shell_exec("crontab {$tempFile} 2>&1");
            unlink($tempFile);

            if ($output) {
                $this->error('❌ Error al instalar crontab: ' . $output);
                return 1;
            }

            $this->info('✅ Cron job instalado correctamente');
            $this->info('El comando se ejecutará cada 30 minutos');
            $this->info('Logs disponibles en: ' . $logPath);
            $this->newLine();

            // Mostrar instrucciones adicionales
            $this->info('📋 Instrucciones adicionales:');
            $this->line('• Para ver el crontab actual: crontab -l');
            $this->line('• Para editar el crontab: crontab -e');
            $this->line('• Para remover el crontab: crontab -r');
            $this->line('• Para ver los logs: tail -f ' . $logPath);
            $this->newLine();

            // Crear archivo de configuración
            $configPath = storage_path('app/auto-close-config.json');
            $config = [
                'enabled' => true,
                'interval_minutes' => 30,
                'max_hours_before_auto_close' => 12,
                'alert_interval_hours' => 2,
                'max_alerts_before_auto_pause' => 3,
                'installed_at' => now()->toISOString(),
                'cron_command' => $cronCommand
            ];

            file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
            $this->info('✅ Configuración guardada en: ' . $configPath);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
