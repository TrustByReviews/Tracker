<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\BugAssignmentService;
use App\Services\BugTimeTrackingService;
use App\Services\PaymentService;
use Carbon\Carbon;

echo "=== TESTING BUG SYSTEM ===\n\n";

try {
    // 1. Verificar que existen bugs
    $bugsCount = Bug::count();
    echo "✅ Total bugs in database: {$bugsCount}\n";
    
    // 2. Verificar que existen usuarios desarrolladores
    $developers = User::whereHas('roles', function($query) {
        $query->where('name', 'developer');
    })->get();
    
    echo "✅ Developers found: {$developers->count()}\n";
    
    if ($developers->count() > 0) {
        $developer = $developers->first();
        echo "   - Testing with developer: {$developer->name} ({$developer->email})\n";
        
        // 3. Verificar actividades activas del desarrollador
        $activeTasks = $developer->tasks()
            ->whereIn('status', ['to do', 'in progress'])
            ->count();
            
        $activeBugs = $developer->bugs()
            ->whereIn('status', ['new', 'assigned', 'in progress'])
            ->count();
            
        $totalActive = $activeTasks + $activeBugs;
        
        echo "✅ Active activities for {$developer->name}:\n";
        echo "   - Tasks: {$activeTasks}\n";
        echo "   - Bugs: {$activeBugs}\n";
        echo "   - Total: {$totalActive}/3\n";
        
        // 4. Probar límite de actividades activas
        if ($totalActive >= 3) {
            echo "⚠️  Developer has reached the limit of 3 active activities\n";
        } else {
            echo "✅ Developer can still be assigned more activities\n";
        }
        
        // 5. Verificar bugs del desarrollador
        $developerBugs = $developer->bugs()->with(['project', 'sprint'])->get();
        echo "✅ Bugs assigned to {$developer->name}: {$developerBugs->count()}\n";
        
        foreach ($developerBugs as $bug) {
            echo "   - {$bug->title} ({$bug->status}) - {$bug->project->name}\n";
        }
        
        // 6. Probar servicio de asignación
        $assignmentService = new BugAssignmentService();
        $availableBugs = Bug::whereNull('user_id')->where('status', 'new')->get();
        
        if ($availableBugs->count() > 0 && $totalActive < 3) {
            $bugToAssign = $availableBugs->first();
            echo "✅ Testing bug assignment...\n";
            echo "   - Bug to assign: {$bugToAssign->title}\n";
            
            // Intentar asignar el bug
            $response = $assignmentService->selfAssignBug($bugToAssign->id, $developer->id);
            $responseData = json_decode($response->getContent(), true);
            
            if ($response->getStatusCode() === 200) {
                echo "✅ Bug assigned successfully!\n";
            } else {
                echo "❌ Error assigning bug: {$responseData['error']}\n";
            }
        } else {
            echo "ℹ️  No available bugs to assign or developer at limit\n";
        }
        
        // 7. Probar servicio de tiempo
        $timeService = new BugTimeTrackingService();
        $workingBugs = $developer->bugs()->where('status', 'in progress')->get();
        
        if ($workingBugs->count() > 0) {
            $bugToTest = $workingBugs->first();
            echo "✅ Testing time tracking...\n";
            echo "   - Bug to test: {$bugToTest->title}\n";
            echo "   - Current time: {$bugToTest->total_time_seconds} seconds\n";
            
            // Obtener logs de tiempo
            $timeLogs = $timeService->getBugTimeLogs($bugToTest->id);
            echo "   - Time logs count: " . count($timeLogs) . "\n";
        } else {
            echo "ℹ️  No bugs in progress to test time tracking\n";
        }
        
        // 8. Probar reportes de pago
        $paymentService = new PaymentService();
        $weekStart = Carbon::now()->subWeek()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        echo "✅ Testing payment reports...\n";
        echo "   - Week: {$weekStart->format('Y-m-d')} to {$weekEnd->format('Y-m-d')}\n";
        
        $report = $paymentService->generateReportForDateRange($developer, $weekStart, $weekEnd);
        
        echo "   - Report generated: {$report->id}\n";
        echo "   - Total hours: {$report->total_hours}\n";
        echo "   - Total payment: \${$report->total_payment}\n";
        echo "   - Completed activities: {$report->completed_tasks_count}\n";
        echo "   - In progress activities: {$report->in_progress_tasks_count}\n";
        
        // Mostrar detalles del reporte
        if (isset($report->task_details['bugs'])) {
            $completedBugs = count($report->task_details['bugs']['completed']);
            $inProgressBugs = count($report->task_details['bugs']['in_progress']);
            echo "   - Bugs in report: {$completedBugs} completed, {$inProgressBugs} in progress\n";
        }
        
    } else {
        echo "❌ No developers found in the system\n";
    }
    
    // 9. Estadísticas generales
    echo "\n=== BUG SYSTEM STATISTICS ===\n";
    
    $bugsByStatus = Bug::selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->get();
        
    echo "Bugs by status:\n";
    foreach ($bugsByStatus as $stat) {
        echo "   - {$stat->status}: {$stat->count}\n";
    }
    
    $bugsByImportance = Bug::selectRaw('importance, count(*) as count')
        ->groupBy('importance')
        ->get();
        
    echo "Bugs by importance:\n";
    foreach ($bugsByImportance as $stat) {
        echo "   - {$stat->importance}: {$stat->count}\n";
    }
    
    $bugsByType = Bug::selectRaw('bug_type, count(*) as count')
        ->groupBy('bug_type')
        ->get();
        
    echo "Bugs by type:\n";
    foreach ($bugsByType as $stat) {
        echo "   - {$stat->bug_type}: {$stat->count}\n";
    }
    
    echo "\n✅ Bug system test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error testing bug system: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 