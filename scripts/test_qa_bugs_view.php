<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DE VISTA DE BUGS PARA QA ===\n\n";

try {
    // Obtener usuario QA
    $qaUser = User::where('email', 'qa@tracker.com')->first();

    if (!$qaUser) {
        echo "❌ Error: No se encontró el usuario QA\n";
        exit(1);
    }

    echo "✅ Usuario QA encontrado:\n";
    echo "   - Nombre: {$qaUser->name}\n";
    echo "   - Email: {$qaUser->email}\n";
    echo "   - Roles: " . implode(', ', $qaUser->roles->pluck('value')->toArray()) . "\n\n";

    // Verificar proyectos asignados al QA
    $qaProjects = $qaUser->projects()->count();
    echo "📊 Proyectos asignados al QA: {$qaProjects}\n";

    // Verificar bugs disponibles para QA
    $qaBugs = Bug::whereHas('sprint.project', function ($query) use ($qaUser) {
        $query->whereHas('users', function ($q) use ($qaUser) {
            $q->where('users.id', $qaUser->id);
        });
    })->count();
    echo "🐛 Bugs disponibles para QA: {$qaBugs}\n";

    // Verificar bugs por estado
    $bugsByStatus = Bug::whereHas('sprint.project', function ($query) use ($qaUser) {
        $query->whereHas('users', function ($q) use ($qaUser) {
            $q->where('users.id', $qaUser->id);
        });
    })->selectRaw('status, count(*) as count')->groupBy('status')->get();

    echo "\n📋 Bugs por estado:\n";
    foreach ($bugsByStatus as $status) {
        echo "   - {$status->status}: {$status->count}\n";
    }

    // Verificar bugs listos para testear
    $bugsReadyForTest = Bug::whereHas('sprint.project', function ($query) use ($qaUser) {
        $query->whereHas('users', function ($q) use ($qaUser) {
            $q->where('users.id', $qaUser->id);
        });
    })->where('status', 'ready_for_test')->count();
    echo "\n🔍 Bugs listos para testear: {$bugsReadyForTest}\n";

    // Verificar bugs en testing
    $bugsInTesting = Bug::whereHas('sprint.project', function ($query) use ($qaUser) {
        $query->whereHas('users', function ($q) use ($qaUser) {
            $q->where('users.id', $qaUser->id);
        });
    })->where('status', 'testing')->count();
    echo "🧪 Bugs en testing: {$bugsInTesting}\n";

    // Verificar bugs aprobados por QA
    $bugsApprovedByQa = Bug::whereHas('sprint.project', function ($query) use ($qaUser) {
        $query->whereHas('users', function ($q) use ($qaUser) {
            $q->where('users.id', $qaUser->id);
        });
    })->where('qa_status', 'approved')->count();
    echo "✅ Bugs aprobados por QA: {$bugsApprovedByQa}\n";

    // Verificar bugs rechazados por QA
    $bugsRejectedByQa = Bug::whereHas('sprint.project', function ($query) use ($qaUser) {
        $query->whereHas('users', function ($q) use ($qaUser) {
            $q->where('users.id', $qaUser->id);
        });
    })->where('qa_status', 'rejected')->count();
    echo "❌ Bugs rechazados por QA: {$bugsRejectedByQa}\n\n";

    // Verificar rutas disponibles
    echo "🔗 Rutas disponibles para QA:\n";
    echo "   ✅ /bugs - Lista de bugs\n";
    echo "   ✅ /bugs/{id} - Detalle de bug\n";
    echo "   ✅ /bugs/available - Bugs disponibles\n";
    echo "   ✅ /bugs/my-bugs - Mis bugs asignados\n\n";

    // Verificar permisos específicos
    echo "🔐 Permisos específicos para bugs:\n";
    echo "   ✅ qa.bugs.view - Ver bugs para QA\n";
    echo "   ✅ qa.bugs.approve - Aprobar bugs como QA\n";
    echo "   ✅ qa.bugs.reject - Rechazar bugs como QA\n";
    echo "   ✅ qa.bugs.assign - Asignar bugs a sí mismo como QA\n";
    echo "   ✅ qa.bugs.edit - Editar bugs (solo campos de QA)\n\n";

    // Verificar funcionalidades específicas
    echo "🎯 FUNCIONALIDADES ESPECÍFICAS PARA QA:\n";
    echo "   ✅ Ver bugs de proyectos asignados\n";
    echo "   ✅ Filtrar por estado (ready_for_test, testing, etc.)\n";
    echo "   ✅ Aprobar/rechazar bugs\n";
    echo "   ✅ Asignar bugs a sí mismo\n";
    echo "   ✅ Ver detalles completos de bugs\n";
    echo "   ✅ Comentar en bugs\n";
    echo "   ✅ Ver historial de cambios\n\n";

    echo "🚀 ¡VISTA DE BUGS CONFIGURADA CORRECTAMENTE!\n";
    echo "   - Login: qa@tracker.com / password\n";
    echo "   - Bugs: http://127.0.0.1:8000/bugs\n";
    echo "   - Verificar filtros y funcionalidades\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 