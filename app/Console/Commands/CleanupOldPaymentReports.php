<?php

namespace App\Console\Commands;

use App\Models\PaymentReport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupOldPaymentReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:cleanup-old-reports {--months=6 : Number of months to keep} {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old payment reports older than specified months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $months = $this->option('months');
        $dryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subMonths($months);

        $this->info('🧹 Limpiando reportes de pago antiguos...');
        $this->line("   - Manteniendo reportes de los últimos {$months} meses");
        $this->line("   - Fecha de corte: {$cutoffDate->format('d/m/Y')}");
        
        if ($dryRun) {
            $this->line('   - Modo DRY RUN (no se eliminarán archivos)');
        }
        $this->newLine();

        // Contar reportes que serían eliminados
        $oldReports = PaymentReport::where('week_start_date', '<', $cutoffDate->toDateString());
        $countToDelete = $oldReports->count();

        if ($countToDelete === 0) {
            $this->info('✅ No hay reportes antiguos para eliminar.');
            return 0;
        }

        $this->info("📊 Reportes encontrados para eliminar: {$countToDelete}");
        
        // Mostrar estadísticas de los reportes a eliminar
        $this->showDeletionStats($oldReports);

        if ($dryRun) {
            $this->warn('⚠️  Modo DRY RUN - No se eliminaron reportes');
            $this->info('   Ejecute sin --dry-run para eliminar realmente');
            return 0;
        }

        // Confirmar eliminación
        if (!$this->confirm('¿Está seguro de que desea eliminar estos reportes?')) {
            $this->info('❌ Operación cancelada.');
            return 0;
        }

        // Eliminar reportes antiguos
        $deletedCount = $oldReports->delete();

        $this->info("✅ Se eliminaron {$deletedCount} reportes antiguos.");
        
        // Mostrar estadísticas después de la limpieza
        $this->showCurrentStats();

        return 0;
    }

    private function showDeletionStats($query)
    {
        $this->info('📋 Estadísticas de reportes a eliminar:');
        
        // Por estado
        $byStatus = $query->get()->groupBy('status');
        foreach ($byStatus as $status => $reports) {
            $totalPayment = $reports->sum('total_payment');
            $totalHours = $reports->sum('total_hours');
            $this->line("   - {$status}: {$reports->count()} reportes, COP " . number_format($totalPayment, 0, ',', '.') . ",00, " . number_format($totalHours, 1, ',', '.') . "h");
        }

        // Por año
        $byYear = $query->get()->groupBy(function ($report) {
            return Carbon::parse($report->week_start_date)->year;
        });
        
        $this->line('   Por año:');
        foreach ($byYear as $year => $reports) {
            $totalPayment = $reports->sum('total_payment');
            $this->line("     - {$year}: {$reports->count()} reportes, COP " . number_format($totalPayment, 0, ',', '.') . ",00");
        }

        $this->newLine();
    }

    private function showCurrentStats()
    {
        $this->info('📊 Estadísticas actuales después de la limpieza:');
        
        $totalReports = PaymentReport::count();
        $totalPayment = PaymentReport::sum('total_payment');
        $totalHours = PaymentReport::sum('total_hours');
        
        $this->line("   - Total de reportes: {$totalReports}");
        $this->line("   - Total de pagos: COP " . number_format($totalPayment, 0, ',', '.') . ",00");
        $this->line("   - Total de horas: " . number_format($totalHours, 1, ',', '.') . "h");

        // Por estado
        $byStatus = PaymentReport::selectRaw('status, COUNT(*) as count, SUM(total_payment) as total_payment, SUM(total_hours) as total_hours')
            ->groupBy('status')
            ->get();

        $this->line('   Por estado:');
        foreach ($byStatus as $stat) {
            $this->line("     - {$stat->status}: {$stat->count} reportes, COP " . number_format($stat->total_payment, 0, ',', '.') . ",00, " . number_format($stat->total_hours, 1, ',', '.') . "h");
        }
    }
}
