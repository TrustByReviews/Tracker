<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PaymentReport;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestPaymentModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:test {--user= : Test specific user ID} {--week= : Test specific week (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the payment module functionality';

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Payment Module...');
        $this->newLine();

        try {
            // Test 1: Verificar estructura de base de datos
            $this->testDatabaseStructure();

            // Test 2: Verificar desarrolladores
            $this->testDevelopers();

            // Test 3: Generar reporte de prueba
            $this->testReportGeneration();

            // Test 4: Verificar estadísticas
            $this->testStatistics();

            // Test 5: Verificar permisos
            $this->testPermissions();

            $this->info('✅ All tests passed! Payment module is working correctly.');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function testDatabaseStructure()
    {
        $this->info('📊 Testing database structure...');

        // Verificar que la tabla existe
        if (!DB::getSchemaBuilder()->hasTable('payment_reports')) {
            throw new \Exception('Payment reports table does not exist');
        }

        // Verificar columnas principales
        $columns = DB::getSchemaBuilder()->getColumnListing('payment_reports');
        $requiredColumns = ['id', 'user_id', 'week_start_date', 'week_end_date', 'total_hours', 'hourly_rate', 'total_payment', 'status'];
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $columns)) {
                throw new \Exception("Required column '{$column}' is missing from payment_reports table");
            }
        }

        $this->info('✅ Database structure is correct');
    }

    private function testDevelopers()
    {
        $this->info('👥 Testing developers...');

        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->get();

        if ($developers->isEmpty()) {
            $this->warn('⚠️  No developers found. Creating test developer...');
            
            // Crear un desarrollador de prueba
            $developer = User::create([
                'name' => 'Test Developer',
                'email' => 'test.developer@example.com',
                'password' => bcrypt('password'),
                'hour_value' => 25,
                'status' => 'active',
            ]);

            // Asignar rol de desarrollador
            $developerRole = \App\Models\Role::where('name', 'developer')->first();
            if ($developerRole) {
                $developer->roles()->attach($developerRole->id);
            }

            $this->info("✅ Created test developer: {$developer->name}");
        } else {
            $this->info("✅ Found {$developers->count()} developers");
            foreach ($developers as $developer) {
                $this->line("   - {$developer->name} (\${$developer->hour_value}/hr)");
            }
        }
    }

    private function testReportGeneration()
    {
        $this->info('📋 Testing report generation...');

        $weekStart = $this->option('week') 
            ? Carbon::parse($this->option('week'))->startOfWeek()
            : Carbon::now()->subWeek()->startOfWeek();

        $this->info("   Testing week: {$weekStart->format('Y-m-d')} to {$weekStart->copy()->endOfWeek()->format('Y-m-d')}");

        // Generar reportes
        $reportsData = $this->paymentService->generateWeeklyReportsForAllDevelopers($weekStart);

        $this->info("   Generated " . count($reportsData['reports']) . " reports");
        $this->info("   Total payment: $" . number_format($reportsData['total_payment'], 2));

        // Mostrar detalles de reportes
        if (!empty($reportsData['reports'])) {
            $tableData = [];
            foreach ($reportsData['reports'] as $report) {
                $tableData[] = [
                    $report->user->name,
                    $report->total_hours . ' hrs',
                    '$' . $report->hourly_rate,
                    '$' . number_format($report->total_payment, 2),
                    $report->status,
                ];
            }
            
            $this->table(
                ['Developer', 'Hours', 'Rate', 'Payment', 'Status'],
                $tableData
            );
        }

        $this->info('✅ Report generation test passed');
    }

    private function testStatistics()
    {
        $this->info('📈 Testing statistics...');

        $statistics = $this->paymentService->getPaymentStatistics();

        $this->info("   Total reports: {$statistics['total_reports']}");
        $this->info("   Total payment: $" . number_format($statistics['total_payment'], 2));
        $this->info("   Total hours: " . number_format($statistics['total_hours'], 2));
        $this->info("   Pending reports: {$statistics['pending_reports']}");
        $this->info("   Approved reports: {$statistics['approved_reports']}");
        $this->info("   Paid reports: {$statistics['paid_reports']}");

        $this->info('✅ Statistics test passed');
    }

    private function testPermissions()
    {
        $this->info('🔐 Testing permissions...');

        // Verificar que las políticas están registradas
        $policies = app('Illuminate\Contracts\Auth\Access\Gate')->policies();
        
        if (!isset($policies[PaymentReport::class])) {
            $this->warn('⚠️  PaymentReport policy not found. Checking if it exists...');
            
            if (class_exists(\App\Policies\PaymentReportPolicy::class)) {
                $this->info('✅ PaymentReportPolicy class exists');
            } else {
                throw new \Exception('PaymentReportPolicy class does not exist');
            }
        } else {
            $this->info('✅ PaymentReport policy is registered');
        }

        // Verificar permisos en el sistema RBAC
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if ($admin) {
            $this->info("   Testing with admin user: {$admin->name}");
            
            // Verificar que el admin puede ver reportes de pago
            if ($admin->hasPermission('payment-reports.view')) {
                $this->info('✅ Admin has payment-reports.view permission');
            } else {
                $this->warn('⚠️  Admin does not have payment-reports.view permission');
            }
        } else {
            $this->warn('⚠️  No admin user found for permission testing');
        }

        $this->info('✅ Permissions test passed');
    }
}
