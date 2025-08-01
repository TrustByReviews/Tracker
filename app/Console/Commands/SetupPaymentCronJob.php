<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupPaymentCronJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:setup-cron {--force : Force overwrite existing cron entries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup cron job for automatic weekly payment reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Configurando cron job para reportes de pago automáticos...');
        $this->newLine();

        // Obtener la ruta del proyecto
        $projectPath = base_path();
        $artisanPath = $projectPath . '/artisan';

        // Verificar que el archivo artisan existe
        if (!File::exists($artisanPath)) {
            $this->error('❌ No se encontró el archivo artisan en: ' . $artisanPath);
            return 1;
        }

        // Comandos cron a agregar
        $cronCommands = [
            // Generar reportes semanales cada domingo a las 8:00 AM
            '0 8 * * 0 cd ' . $projectPath . ' && php artisan payments:generate-weekly --send-email >> /dev/null 2>&1',
            
            // Limpiar reportes antiguos cada primer día del mes a las 2:00 AM
            '0 2 1 * * cd ' . $projectPath . ' && php artisan payments:cleanup-old-reports >> /dev/null 2>&1',
            
            // Verificar el estado del sistema cada día a las 6:00 AM
            '0 6 * * * cd ' . $projectPath . ' && php artisan payments:test >> /dev/null 2>&1',
        ];

        $this->info('📋 Comandos cron a agregar:');
        foreach ($cronCommands as $index => $command) {
            $this->line('   ' . ($index + 1) . '. ' . $command);
        }
        $this->newLine();

        // Mostrar instrucciones para Windows
        $this->info('🪟 Para Windows (Task Scheduler):');
        $this->line('   1. Abrir "Task Scheduler" (Programador de tareas)');
        $this->line('   2. Crear tarea básica');
        $this->line('   3. Programar para ejecutar semanalmente los domingos a las 8:00 AM');
        $this->line('   4. Acción: Iniciar programa');
        $this->line('   5. Programa: ' . PHP_BINARY);
        $this->line('   6. Argumentos: ' . $artisanPath . ' payments:generate-weekly --send-email');
        $this->newLine();

        // Mostrar instrucciones para Linux/Unix
        $this->info('🐧 Para Linux/Unix:');
        $this->line('   1. Ejecutar: crontab -e');
        $this->line('   2. Agregar las siguientes líneas:');
        $this->newLine();

        foreach ($cronCommands as $command) {
            $this->line('   ' . $command);
        }

        $this->newLine();
        $this->info('📝 Para verificar que el cron está funcionando:');
        $this->line('   - Revisar logs: tail -f storage/logs/laravel.log');
        $this->line('   - Probar manualmente: php artisan payments:generate-weekly --send-email');
        $this->newLine();

        // Crear archivo de configuración
        $configContent = "<?php\n\n";
        $configContent .= "// Configuración automática de cron jobs para reportes de pago\n";
        $configContent .= "// Generado el: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $configContent .= "return [\n";
        $configContent .= "    'project_path' => '" . $projectPath . "',\n";
        $configContent .= "    'artisan_path' => '" . $artisanPath . "',\n";
        $configContent .= "    'cron_commands' => [\n";
        
        foreach ($cronCommands as $command) {
            $configContent .= "        '" . addslashes($command) . "',\n";
        }
        
        $configContent .= "    ],\n";
        $configContent .= "    'schedule' => [\n";
        $configContent .= "        'weekly_reports' => 'Every Sunday at 8:00 AM',\n";
        $configContent .= "        'cleanup_reports' => 'First day of month at 2:00 AM',\n";
        $configContent .= "        'system_check' => 'Every day at 6:00 AM',\n";
        $configContent .= "    ],\n";
        $configContent .= "];\n";

        $configPath = storage_path('app/payment-cron-config.php');
        File::put($configPath, $configContent);

        $this->info('✅ Archivo de configuración creado en: ' . $configPath);
        $this->newLine();

        // Verificar permisos
        $this->info('🔍 Verificando permisos y configuración...');
        
        if (!is_writable($projectPath)) {
            $this->warn('⚠️  El directorio del proyecto no es escribible');
        } else {
            $this->info('✅ Permisos del directorio correctos');
        }

        if (!File::exists(storage_path('logs'))) {
            $this->warn('⚠️  El directorio de logs no existe');
        } else {
            $this->info('✅ Directorio de logs disponible');
        }

        // Verificar configuración de email
        $this->info('📧 Verificando configuración de email...');
        $mailConfig = config('mail.default');
        if ($mailConfig && $mailConfig !== 'log') {
            $this->info('✅ Configuración de email detectada: ' . $mailConfig);
        } else {
            $this->warn('⚠️  Configuración de email no detectada o configurada como log');
            $this->line('   Configure su email en .env para recibir reportes automáticos');
        }

        $this->newLine();
        $this->info('🎉 Configuración de cron job completada!');
        $this->info('📋 Próximos pasos:');
        $this->line('   1. Configurar el cron job en su servidor');
        $this->line('   2. Verificar que los emails funcionan correctamente');
        $this->line('   3. Probar la generación manual: php artisan payments:generate-weekly --send-email');
        $this->line('   4. Revisar los logs para confirmar que funciona automáticamente');

        return 0;
    }
}
