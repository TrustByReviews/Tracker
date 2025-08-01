<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Services\AdminDashboardService;
use App\Services\TaskApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function __construct(
        private AdminDashboardService $adminDashboardService,
        private TaskApprovalService $taskApprovalService
    ) {
    }

    /**
     * Dashboard principal del administrador
     */
    public function dashboard(): Response
    {
        $admin = Auth::user();
        
        // Verificar que el usuario es admin
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            abort(403, 'Access denied. Admin role required.');
        }
        
        $systemStats = $this->adminDashboardService->getSystemStats();
        $tasksRequiringAttention = $this->adminDashboardService->getTasksRequiringAttention();
        $pendingApprovalTasks = $this->adminDashboardService->getPendingApprovalTasks();
        $activeProjectsSummary = $this->adminDashboardService->getActiveProjectsSummary();
        $activeDevelopersSummary = $this->adminDashboardService->getActiveDevelopersSummary();
        
        return Inertia::render('Admin/Dashboard', [
            'systemStats' => $systemStats,
            'tasksRequiringAttention' => $tasksRequiringAttention,
            'pendingApprovalTasks' => $pendingApprovalTasks,
            'activeProjectsSummary' => $activeProjectsSummary,
            'activeDevelopersSummary' => $activeDevelopersSummary,
        ]);
    }

    /**
     * Vista de tareas en curso con filtros avanzados
     */
    public function inProgressTasks(Request $request): Response
    {
        $admin = Auth::user();
        
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            abort(403, 'Access denied. Admin role required.');
        }
        
        $filters = $request->only([
            'project_id', 'user_id', 'priority', 'time_comparison',
            'start_date', 'end_date', 'search', 'order_by', 'order_direction'
        ]);
        
        $inProgressTasks = $this->adminDashboardService->getInProgressTasksWithFilters($filters);
        $projects = Project::all();
        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->get();
        
        return Inertia::render('Admin/InProgressTasks', [
            'inProgressTasks' => $inProgressTasks,
            'filters' => $filters,
            'projects' => $projects,
            'developers' => $developers,
        ]);
    }

    /**
     * Vista de métricas de rendimiento por desarrollador
     */
    public function developerMetrics(): Response
    {
        $admin = Auth::user();
        
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            abort(403, 'Access denied. Admin role required.');
        }
        
        $developerMetrics = $this->adminDashboardService->getDeveloperPerformanceMetrics();
        
        return Inertia::render('Admin/DeveloperMetrics', [
            'developerMetrics' => $developerMetrics,
        ]);
    }

    /**
     * Vista de métricas de rendimiento por proyecto
     */
    public function projectMetrics(): Response
    {
        $admin = Auth::user();
        
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            abort(403, 'Access denied. Admin role required.');
        }
        
        $projectMetrics = $this->adminDashboardService->getProjectPerformanceMetrics();
        
        return Inertia::render('Admin/ProjectMetrics', [
            'projectMetrics' => $projectMetrics,
        ]);
    }

    /**
     * Vista de reportes de tiempo
     */
    public function timeReports(Request $request): Response
    {
        $admin = Auth::user();
        
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            abort(403, 'Access denied. Admin role required.');
        }
        
        $period = $request->get('period', 'week');
        $timeReport = $this->adminDashboardService->getTimeReportByPeriod($period);
        
        return Inertia::render('Admin/TimeReports', [
            'timeReport' => $timeReport,
            'selectedPeriod' => $period,
        ]);
    }

    /**
     * Vista de tareas que requieren atención
     */
    public function tasksRequiringAttention(): Response
    {
        $admin = Auth::user();
        
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            abort(403, 'Access denied. Admin role required.');
        }
        
        $tasksRequiringAttention = $this->adminDashboardService->getTasksRequiringAttention();
        
        return Inertia::render('Admin/TasksRequiringAttention', [
            'tasksRequiringAttention' => $tasksRequiringAttention,
        ]);
    }

    /**
     * Vista de tareas pendientes de aprobación
     */
    public function pendingApprovalTasks(): Response
    {
        $admin = Auth::user();
        
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            abort(403, 'Access denied. Admin role required.');
        }
        
        $pendingApprovalTasks = $this->adminDashboardService->getPendingApprovalTasks();
        
        return Inertia::render('Admin/PendingApprovalTasks', [
            'pendingApprovalTasks' => $pendingApprovalTasks,
        ]);
    }

    /**
     * Obtener estadísticas del sistema (API)
     */
    public function getSystemStats(): JsonResponse
    {
        try {
            $admin = Auth::user();
            
            if (!$admin->roles()->where('name', 'admin')->exists()) {
                throw new \Exception('Access denied. Admin role required.');
            }
            
            $stats = $this->adminDashboardService->getSystemStats();

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas del sistema', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Obtener tareas en curso con filtros (API)
     */
    public function getInProgressTasks(Request $request): JsonResponse
    {
        try {
            $admin = Auth::user();
            
            if (!$admin->roles()->where('name', 'admin')->exists()) {
                throw new \Exception('Access denied. Admin role required.');
            }
            
            $filters = $request->only([
                'project_id', 'user_id', 'priority', 'time_comparison',
                'start_date', 'end_date', 'search', 'order_by', 'order_direction'
            ]);
            
            $tasks = $this->adminDashboardService->getInProgressTasksWithFilters($filters);

            return response()->json([
                'success' => true,
                'tasks' => $tasks,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas en progreso', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas'
            ], 500);
        }
    }

    /**
     * Obtener métricas de desarrolladores (API)
     */
    public function getDeveloperMetrics(): JsonResponse
    {
        try {
            $admin = Auth::user();
            
            if (!$admin->roles()->where('name', 'admin')->exists()) {
                throw new \Exception('Access denied. Admin role required.');
            }
            
            $metrics = $this->adminDashboardService->getDeveloperPerformanceMetrics();

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de desarrolladores', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas'
            ], 500);
        }
    }

    /**
     * Obtener métricas de proyectos (API)
     */
    public function getProjectMetrics(): JsonResponse
    {
        try {
            $admin = Auth::user();
            
            if (!$admin->roles()->where('name', 'admin')->exists()) {
                throw new \Exception('Access denied. Admin role required.');
            }
            
            $metrics = $this->adminDashboardService->getProjectPerformanceMetrics();

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener métricas de proyectos', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas'
            ], 500);
        }
    }

    /**
     * Obtener reporte de tiempo por período (API)
     */
    public function getTimeReport(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'period' => 'nullable|string|in:week,month,quarter,year',
            ]);

            $admin = Auth::user();
            
            if (!$admin->roles()->where('name', 'admin')->exists()) {
                throw new \Exception('Access denied. Admin role required.');
            }
            
            $period = $validatedData['period'] ?? 'week';
            $report = $this->adminDashboardService->getTimeReportByPeriod($period);

            return response()->json([
                'success' => true,
                'report' => $report
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener reporte de tiempo', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reporte'
            ], 500);
        }
    }

    /**
     * Obtener tareas que requieren atención (API)
     */
    public function getTasksRequiringAttention(): JsonResponse
    {
        try {
            $admin = Auth::user();
            
            if (!$admin->roles()->where('name', 'admin')->exists()) {
                throw new \Exception('Access denied. Admin role required.');
            }
            
            $tasks = $this->adminDashboardService->getTasksRequiringAttention();

            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas que requieren atención', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas'
            ], 500);
        }
    }

    /**
     * Obtener resumen de proyectos activos (API)
     */
    public function getActiveProjectsSummary(): JsonResponse
    {
        try {
            $admin = Auth::user();
            
            if (!$admin->roles()->where('name', 'admin')->exists()) {
                throw new \Exception('Access denied. Admin role required.');
            }
            
            $projects = $this->adminDashboardService->getActiveProjectsSummary();

            return response()->json([
                'success' => true,
                'projects' => $projects
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener resumen de proyectos activos', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proyectos'
            ], 500);
        }
    }

    /**
     * Obtener resumen de desarrolladores activos (API)
     */
    public function getActiveDevelopersSummary(): JsonResponse
    {
        try {
            $admin = Auth::user();
            
            if (!$admin->roles()->where('name', 'admin')->exists()) {
                throw new \Exception('Access denied. Admin role required.');
            }
            
            $developers = $this->adminDashboardService->getActiveDevelopersSummary();

            return response()->json([
                'success' => true,
                'developers' => $developers
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener resumen de desarrolladores activos', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener desarrolladores'
            ], 500);
        }
    }

    // TEMPORARILY DISABLED - Login as User functionality
    // Login as another user (admin only).
    // Uncomment to reactivate
    /*
    public function loginAsUser(Request $request, $userId)
    {
        // Verificar que el usuario actual es admin
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Buscar el usuario objetivo
        $targetUser = User::findOrFail($userId);

        // Guardar el ID del admin original en la sesión
        $request->session()->put('admin_original_user_id', Auth::id());

        // Login como el usuario objetivo
        Auth::login($targetUser);

        return redirect()->route('dashboard')->with('success', "Now logged in as {$targetUser->name}");
    }

    // Return to admin user.
    public function returnToAdmin(Request $request)
    {
        $adminUserId = $request->session()->get('admin_original_user_id');

        if (!$adminUserId) {
            return redirect()->route('dashboard')->with('error', 'No admin session found.');
        }

        $adminUser = User::findOrFail($adminUserId);

        // Login como admin
        Auth::login($adminUser);

        // Limpiar la sesión
        $request->session()->forget('admin_original_user_id');

        return redirect()->route('admin.dashboard')->with('success', 'Returned to admin account.');
    }
    */

    /**
     * Get users for admin management.
     */
    public function users(Request $request): Response
    {
        $admin = Auth::user();
        
        // Verificar que el usuario es admin
        if (!$admin->hasRole('admin')) {
            abort(403, 'Access denied. Admin role required.');
        }
        
        // TEMPORARILY DISABLED - Login as User functionality
        // Verificar si está en sesión de impersonación
        // $adminOriginalUserId = session('admin_original_user_id');
        // $isImpersonating = !empty($adminOriginalUserId);
        
        // Si está impersonando, verificar que el usuario original sea admin
        // if ($isImpersonating) {
        //     $originalAdmin = User::find($adminOriginalUserId);
        //     if (!$originalAdmin || !$originalAdmin->hasRole('admin')) {
        //         // Limpiar sesión corrupta
        //         session()->forget('admin_original_user_id');
        //         abort(403, 'Invalid impersonation session');
        //     }
        // }
        
        $query = User::with(['roles', 'projects', 'tasks']);

        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('value', $request->get('role'));
            });
        }

        $users = $query->paginate($request->get('per_page', 10));

        // Estadísticas
        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'developers' => User::whereHas('roles', function ($q) {
                $q->where('value', 'developer');
            })->count(),
            'admins' => User::whereHas('roles', function ($q) {
                $q->where('value', 'admin');
            })->count(),
        ];

        return Inertia::render('User/Index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'role', 'per_page']),
        ]);
    }
} 