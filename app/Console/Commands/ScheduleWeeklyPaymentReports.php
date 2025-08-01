<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schedule;

class ScheduleWeeklyPaymentReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:schedule-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule weekly payment reports generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📅 Scheduling weekly payment reports...');

        // Programar generación de reportes semanales cada domingo a las 8:00 AM
        Schedule::command('payments:generate-weekly --send-email')
            ->weekly()
            ->sundays()
            ->at('08:00')
            ->description('Generate weekly payment reports and send to admins');

        // Programar limpieza de reportes antiguos (más de 6 meses) cada mes
        Schedule::command('payments:cleanup-old-reports')
            ->monthly()
            ->description('Clean up old payment reports');

        $this->info('✅ Weekly payment reports scheduled successfully!');
        $this->info('   - Reports will be generated every Sunday at 8:00 AM');
        $this->info('   - Emails will be sent to admins automatically');
        $this->info('   - Old reports will be cleaned up monthly');
        
        $this->newLine();
        $this->info('📋 To test the schedule manually, run:');
        $this->info('   php artisan payments:generate-weekly --send-email');
        
        return 0;
    }
}
