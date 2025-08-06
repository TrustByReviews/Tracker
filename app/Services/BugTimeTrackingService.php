<?php

namespace App\Services;

use App\Models\Bug;
use App\Models\BugTimeLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BugTimeTrackingService
{
    public function startWork(Bug $bug): array
    {
        DB::beginTransaction();
        
        try {
            // Verificar que el bug no esté ya trabajando
            if ($bug->is_working) {
                throw new \Exception('El bug ya está siendo trabajado.');
            }
            
            // Verificar límite de actividades activas
            $activeCount = $this->getActiveActivitiesCount($bug->user_id);
            if ($activeCount >= 3) {
                throw new \Exception('Ya tienes el máximo de actividades activas (3).');
            }
            
            // Pausar otras actividades del usuario
            $this->pauseOtherActivities($bug->user_id);
            
            // Iniciar trabajo en el bug
            $bug->update([
                'is_working' => true,
                'work_started_at' => now(),
                'status' => 'in progress'
            ]);
            
            // Crear log de tiempo
            $timeLog = BugTimeLog::create([
                'bug_id' => $bug->id,
                'user_id' => $bug->user_id,
                'started_at' => now()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Trabajo iniciado exitosamente.',
                'bug' => $bug->fresh(),
                'time_log' => $timeLog
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function pauseWork(Bug $bug): array
    {
        DB::beginTransaction();
        
        try {
            if (!$bug->is_working) {
                throw new \Exception('El bug no está siendo trabajado.');
            }
            
            // Calcular duración de la sesión actual
            $startTime = $bug->work_started_at;
            $endTime = now();
            
            // Usar Carbon para calcular la diferencia correctamente
            $duration = $startTime->diffInSeconds($endTime, false);
            
            // Asegurar que la duración sea un entero positivo
            $duration = max(0, (int) $duration);
            
            // Calcular el nuevo tiempo total
            $currentTotal = (int) ($bug->total_time_seconds ?? 0);
            $newTotal = $currentTotal + $duration;
            
            // Actualizar tiempo total
            $bug->update([
                'is_working' => false,
                'work_paused_at' => now(),
                'total_time_seconds' => $newTotal
            ]);
            
            // Finalizar log de tiempo
            $timeLog = BugTimeLog::where('bug_id', $bug->id)
                ->whereNull('finished_at')
                ->latest()
                ->first();
                
            if ($timeLog) {
                $timeLog->update([
                    'finished_at' => now(),
                    'duration' => $duration
                ]);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Trabajo pausado exitosamente.',
                'bug' => $bug->fresh()
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function resumeWork(Bug $bug): array
    {
        DB::beginTransaction();
        
        try {
            if ($bug->is_working) {
                throw new \Exception('El bug ya está siendo trabajado.');
            }
            
            // Verificar límite de actividades activas
            $activeCount = $this->getActiveActivitiesCount($bug->user_id);
            if ($activeCount >= 3) {
                throw new \Exception('Ya tienes el máximo de actividades activas (3).');
            }
            
            // Pausar otras actividades del usuario
            $this->pauseOtherActivities($bug->user_id);
            
            // Reanudar trabajo
            $bug->update([
                'is_working' => true,
                'work_started_at' => now()
            ]);
            
            // Crear nuevo log de tiempo
            $timeLog = BugTimeLog::create([
                'bug_id' => $bug->id,
                'user_id' => $bug->user_id,
                'started_at' => now()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Trabajo reanudado exitosamente.',
                'bug' => $bug->fresh(),
                'time_log' => $timeLog
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function finishWork(Bug $bug): array
    {
        DB::beginTransaction();
        
        try {
            // Si está trabajando, pausar primero
            if ($bug->is_working) {
                $this->pauseWork($bug);
                $bug->refresh();
            }
            
            // Marcar como completado
            $bug->update([
                'status' => 'resolved',
                'resolved_by' => auth()->id(),
                'resolved_at' => now()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Trabajo finalizado exitosamente.',
                'bug' => $bug->fresh()
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function getCurrentWorkTime(Bug $bug): int
    {
        if (!$bug->is_working || !$bug->work_started_at) {
            return 0;
        }
        
        $startTime = $bug->work_started_at;
        $endTime = now();
        
        // Usar Carbon para calcular la diferencia correctamente
        $duration = $startTime->diffInSeconds($endTime, false);
        
        return max(0, (int) $duration);
    }
    
    public function getBugTimeLogs(Bug $bug): \Illuminate\Database\Eloquent\Collection
    {
        return BugTimeLog::where('bug_id', $bug->id)
            ->orderBy('started_at', 'desc')
            ->get();
    }
    
    public function autoPauseInactiveBugs(): int
    {
        $inactiveBugs = Bug::where('is_working', true)
            ->where('work_started_at', '<', now()->subMinutes(30))
            ->get();
            
        $pausedCount = 0;
        
        foreach ($inactiveBugs as $bug) {
            try {
                $this->pauseWork($bug);
                $pausedCount++;
            } catch (\Exception $e) {
                // Log error but continue
                \Log::error("Error auto-pausing bug {$bug->id}: " . $e->getMessage());
            }
        }
        
        return $pausedCount;
    }
    
    private function getActiveActivitiesCount(string $userId): int
    {
        $activeTasks = DB::table('tasks')
            ->where('user_id', $userId)
            ->where('is_working', true)
            ->count();
            
        $activeBugs = DB::table('bugs')
            ->where('user_id', $userId)
            ->where('is_working', true)
            ->count();
            
        return $activeTasks + $activeBugs;
    }
    
    private function pauseOtherActivities(string $userId): void
    {
        // Pausar otras tareas activas
        DB::table('tasks')
            ->where('user_id', $userId)
            ->where('is_working', true)
            ->update([
                'is_working' => false,
                'work_finished_at' => now()
            ]);
            
        // Pausar otros bugs activos
        DB::table('bugs')
            ->where('user_id', $userId)
            ->where('is_working', true)
            ->update([
                'is_working' => false,
                'work_finished_at' => now()
            ]);
    }
} 