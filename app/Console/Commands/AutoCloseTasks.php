<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCloseTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-close tasks that have been running for more than 12 hours and send alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for tasks that need auto-close or alerts...');
        
        try {
            // Obtener tareas que están trabajando actualmente
            $workingTasks = Task::where('is_working', true)
                ->whereNotNull('work_started_at')
                ->where('status', 'in progress')
                ->where('auto_paused', false)
                ->with(['user', 'project', 'sprint'])
                ->get();

            $this->info("Found {$workingTasks->count()} working tasks to check");

            // Procesar auto-cierre por tarea individual
            foreach ($workingTasks as $task) {
                $this->processAutoClose($task);
            }

            // Procesar alertas por usuario (para evitar spam)
            $this->processUserAlerts();

            $this->info('✅ Auto-close check completed successfully');
            
        } catch (\Exception $e) {
            Log::error('Error in auto-close tasks command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('❌ Error occurred: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Procesar alertas por usuario para evitar spam
     */
    private function processUserAlerts()
    {
        // Agrupar tareas por usuario
        $userTasks = Task::where('is_working', true)
            ->whereNotNull('work_started_at')
            ->where('status', 'in progress')
            ->where('auto_paused', false)
            ->with(['user', 'project', 'sprint'])
            ->get()
            ->groupBy('user_id');

        foreach ($userTasks as $userId => $tasks) {
            $user = $tasks->first()->user;
            if (!$user) continue;

            // Verificar si el usuario necesita alerta
            $this->checkAndSendUserAlert($user, $tasks);
        }
    }

    /**
     * Verificar y enviar alerta por usuario
     */
    private function checkAndSendUserAlert($user, $tasks)
    {
        $currentTime = Carbon::now();
        $longestTask = $tasks->sortBy('work_started_at')->first();
        $workStartTime = Carbon::parse($longestTask->work_started_at);
        $hoursWorking = $workStartTime->diffInHours($currentTime);

        // Si han pasado menos de 2 horas, no enviar alerta
        if ($hoursWorking < 2) {
            return;
        }

        // Verificar si ya se envió una alerta recientemente al usuario
        $lastUserAlert = $this->getLastUserAlertTime($user);
        if ($lastUserAlert && $lastUserAlert->diffInHours($currentTime) < 2) {
            return; // Esperar 2 horas entre alertas por usuario
        }

        // Contar alertas enviadas al usuario
        $userAlertCount = $this->getUserAlertCount($user);
        
        // Si ya se enviaron 3 alertas, auto-pausar todas las tareas del usuario
        if ($userAlertCount >= 3) {
            $this->autoPauseUserTasks($user, $tasks);
            return;
        }

        // Enviar alerta al usuario
        $this->sendUserAlert($user, $tasks, $hoursWorking, $userAlertCount);
    }

    /**
     * Obtener la última alerta enviada al usuario
     */
    private function getLastUserAlertTime($user)
    {
        // Buscar en logs o crear un campo en la tabla users para tracking
        // Por ahora, usamos el campo last_alert_at de la tarea más reciente
        $latestTask = Task::where('user_id', $user->id)
            ->where('is_working', true)
            ->orderBy('last_alert_at', 'desc')
            ->first();

        return $latestTask ? Carbon::parse($latestTask->last_alert_at) : null;
    }

    /**
     * Obtener el conteo de alertas del usuario
     */
    private function getUserAlertCount($user)
    {
        // Por ahora, usamos el alert_count de la tarea con más alertas
        $taskWithMostAlerts = Task::where('user_id', $user->id)
            ->where('is_working', true)
            ->orderBy('alert_count', 'desc')
            ->first();

        return $taskWithMostAlerts ? $taskWithMostAlerts->alert_count : 0;
    }

    /**
     * Auto-pausar todas las tareas de un usuario
     */
    private function autoPauseUserTasks($user, $tasks)
    {
        $this->warn("🔄 Auto-pausing all tasks for user: {$user->name} (3 alerts sent)");

        foreach ($tasks as $task) {
            $this->autoPauseTask($task);
        }

        // Enviar notificación de auto-pausa al usuario
        $this->sendAutoPauseUserNotification($user, $tasks);
    }

    /**
     * Enviar alerta al usuario
     */
    private function sendUserAlert($user, $tasks, int $hoursWorking, int $alertCount)
    {
        $this->line("📧 Sending alert to user: {$user->name} (" . ($alertCount + 1) . "/3)");

        // Incrementar contador de alertas en todas las tareas del usuario
        foreach ($tasks as $task) {
            $task->update([
                'alert_count' => $alertCount + 1,
                'last_alert_at' => Carbon::now()
            ]);
        }

        // Enviar email de alerta
        $this->sendUserAlertNotification($user, $tasks, $hoursWorking, $alertCount + 1);
    }

    private function processAutoClose(Task $task)
    {
        $workStartTime = Carbon::parse($task->work_started_at);
        $currentTime = Carbon::now();
        $hoursWorking = $workStartTime->diffInHours($currentTime);

        $this->line("Task: {$task->name} - Working for {$hoursWorking} hours");

        // Si han pasado más de 12 horas, cerrar automáticamente
        if ($hoursWorking >= 12) {
            $this->autoCloseTask($task);
        }
    }

    private function autoCloseTask(Task $task)
    {
        try {
            $this->warn("🔄 Auto-closing task: {$task->name} (working for 12+ hours)");

            // Calcular tiempo final
            $workStartTime = Carbon::parse($task->work_started_at);
            $currentTime = Carbon::now();
            $duration = max(0, $currentTime->diffInSeconds($workStartTime));

            // Actualizar tarea
            $task->update([
                'status' => 'done',
                'is_working' => false,
                'total_time_seconds' => max(0, $task->total_time_seconds + $duration),
                'actual_finish' => $currentTime->format('Y-m-d'),
                'approval_status' => 'pending',
                'auto_close_at' => $currentTime,
                'auto_pause_reason' => 'Auto-closed after 12 hours of continuous work'
            ]);

            // Enviar email de notificación
            $this->sendAutoCloseNotification($task);

            Log::info('Task auto-closed', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'user_id' => $task->user_id,
                'hours_working' => $workStartTime->diffInHours($currentTime)
            ]);

            $this->info("✅ Task {$task->name} auto-closed successfully");

        } catch (\Exception $e) {
            Log::error('Error auto-closing task', [
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
            
            $this->error("❌ Error auto-closing task {$task->name}: " . $e->getMessage());
        }
    }

    private function checkAndSendAlert(Task $task, int $hoursWorking)
    {
        $lastAlertTime = $task->last_alert_at ? Carbon::parse($task->last_alert_at) : null;
        $currentTime = Carbon::now();

        // Verificar si han pasado al menos 2 horas desde la última alerta
        if ($lastAlertTime && $lastAlertTime->diffInHours($currentTime) < 2) {
            return;
        }

        // Si ya se enviaron 3 alertas, pausar automáticamente
        if ($task->alert_count >= 3) {
            $this->autoPauseTask($task);
            return;
        }

        // Enviar alerta
        $this->sendAlert($task, $hoursWorking);
    }

    private function sendAlert(Task $task, int $hoursWorking)
    {
        try {
            $this->warn("⚠️ Sending alert for task: {$task->name} (working for {$hoursWorking} hours)");

            // Actualizar contador de alertas
            $task->update([
                'alert_count' => $task->alert_count + 1,
                'last_alert_at' => Carbon::now()
            ]);

            // Enviar email de alerta
            $this->sendAlertNotification($task, $hoursWorking);

            Log::info('Alert sent for task', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'user_id' => $task->user_id,
                'alert_count' => $task->alert_count,
                'hours_working' => $hoursWorking
            ]);

            $this->info("✅ Alert {$task->alert_count} sent for task {$task->name}");

        } catch (\Exception $e) {
            Log::error('Error sending alert', [
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
            
            $this->error("❌ Error sending alert for task {$task->name}: " . $e->getMessage());
        }
    }

    private function autoPauseTask(Task $task)
    {
        try {
            $this->warn("⏸️ Auto-pausing task: {$task->name} (3 alerts sent)");

            // Calcular tiempo final
            $workStartTime = Carbon::parse($task->work_started_at);
            $currentTime = Carbon::now();
            $duration = max(0, $currentTime->diffInSeconds($workStartTime));

            // Actualizar tarea
            $task->update([
                'is_working' => false,
                'total_time_seconds' => max(0, $task->total_time_seconds + $duration),
                'auto_paused' => true,
                'auto_paused_at' => $currentTime,
                'auto_pause_reason' => 'Auto-paused after 3 alerts without response'
            ]);

            // Enviar email de notificación de pausa
            $this->sendAutoPauseNotification($task);

            Log::info('Task auto-paused', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'user_id' => $task->user_id,
                'alert_count' => $task->alert_count
            ]);

            $this->info("✅ Task {$task->name} auto-paused successfully");

        } catch (\Exception $e) {
            Log::error('Error auto-pausing task', [
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
            
            $this->error("❌ Error auto-pausing task {$task->name}: " . $e->getMessage());
        }
    }

    private function sendAlertNotification(Task $task, int $hoursWorking)
    {
        try {
            $emailService = app(EmailService::class);
            
            $subject = "⚠️ Alerta de Tarea - {$task->name}";
            $message = "
                <h2>Alerta de Tarea</h2>
                <p>Hola {$task->user->name},</p>
                <p>Has estado trabajando en la tarea <strong>{$task->name}</strong> durante <strong>{$hoursWorking} horas</strong>.</p>
                <p><strong>Detalles de la tarea:</strong></p>
                <ul>
                    <li><strong>Proyecto:</strong> {$task->project->name}</li>
                    <li><strong>Sprint:</strong> {$task->sprint->name}</li>
                    <li><strong>Tiempo acumulado:</strong> " . gmdate('H:i:s', $task->total_time_seconds) . "</li>
                </ul>
                <p>Esta es la alerta número <strong>" . ($task->alert_count + 1) . "</strong> de 3.</p>
                <p>Si continúas trabajando en esta tarea, no es necesario que respondas. Si necesitas ayuda o tienes alguna pregunta, por favor contacta a tu team leader.</p>
                <p>Después de 3 alertas, la tarea se pausará automáticamente.</p>
                <br>
                <p>Saludos,<br>Equipo de Tracker</p>
            ";

            $emailService->sendEmail($task->user->email, $subject, $message);

        } catch (\Exception $e) {
            Log::error('Error sending alert notification', [
                'task_id' => $task->id,
                'user_email' => $task->user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendAutoCloseNotification(Task $task)
    {
        try {
            $emailService = app(EmailService::class);
            
            $subject = "🔒 Tarea Cerrada Automáticamente - {$task->name}";
            $message = "
                <h2>Tarea Cerrada Automáticamente</h2>
                <p>Hola {$task->user->name},</p>
                <p>La tarea <strong>{$task->name}</strong> ha sido cerrada automáticamente después de 12 horas de trabajo continuo.</p>
                <p><strong>Detalles de la tarea:</strong></p>
                <ul>
                    <li><strong>Proyecto:</strong> {$task->project->name}</li>
                    <li><strong>Sprint:</strong> {$task->sprint->name}</li>
                    <li><strong>Tiempo total:</strong> " . gmdate('H:i:s', $task->total_time_seconds) . "</li>
                    <li><strong>Estado:</strong> Pendiente de revisión por team leader</li>
                </ul>
                <p>La tarea ahora está pendiente de revisión por tu team leader. Si necesitas hacer algún ajuste o tienes alguna pregunta, por favor contacta a tu team leader.</p>
                <br>
                <p>Saludos,<br>Equipo de Tracker</p>
            ";

            $emailService->sendEmail($task->user->email, $subject, $message);

        } catch (\Exception $e) {
            Log::error('Error sending auto-close notification', [
                'task_id' => $task->id,
                'user_email' => $task->user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendAutoPauseNotification(Task $task)
    {
        try {
            $emailService = app(EmailService::class);
            
            $subject = "⏸️ Tarea Pausada Automáticamente - {$task->name}";
            $message = "
                <h2>Tarea Pausada Automáticamente</h2>
                <p>Hola {$task->user->name},</p>
                <p>La tarea <strong>{$task->name}</strong> ha sido pausada automáticamente después de 3 alertas sin respuesta.</p>
                <p><strong>Detalles de la tarea:</strong></p>
                <ul>
                    <li><strong>Proyecto:</strong> {$task->project->name}</li>
                    <li><strong>Sprint:</strong> {$task->sprint->name}</li>
                    <li><strong>Tiempo acumulado:</strong> " . gmdate('H:i:s', $task->total_time_seconds) . "</li>
                    <li><strong>Estado:</strong> Pausada automáticamente</li>
                </ul>
                <p>Para reanudar el trabajo en esta tarea, simplemente haz clic en el botón 'Reanudar' en la interfaz de la aplicación.</p>
                <p>Si necesitas ayuda o tienes alguna pregunta, por favor contacta a tu team leader.</p>
                <br>
                <p>Saludos,<br>Equipo de Tracker</p>
            ";

            $emailService->sendEmail($task->user->email, $subject, $message);

        } catch (\Exception $e) {
            Log::error('Error sending auto-pause notification', [
                'task_id' => $task->id,
                'user_email' => $task->user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificación de alerta por usuario
     */
    private function sendUserAlertNotification($user, $tasks, int $hoursWorking, int $alertCount)
    {
        try {
            $emailService = app(EmailService::class);
            
            $subject = "⚠️ Alerta de Tareas Activas - {$user->name}";
            $message = "
                <h2>Alerta de Tareas Activas</h2>
                <p>Hola {$user->name},</p>
                <p>Has estado trabajando en <strong>{$tasks->count()} tareas</strong> durante <strong>{$hoursWorking} horas</strong>.</p>
                <p><strong>Tareas activas:</strong></p>
                <ul>";
            
            foreach ($tasks as $task) {
                $message .= "<li><strong>{$task->name}</strong> - {$task->project->name} ({$task->sprint->name})</li>";
            }
            
            $message .= "
                </ul>
                <p>Esta es la alerta número <strong>{$alertCount}</strong> de 3.</p>
                <p>Si continúas trabajando en estas tareas, no es necesario que respondas. Si necesitas ayuda o tienes alguna pregunta, por favor contacta a tu team leader.</p>
                <p>Después de 3 alertas, todas tus tareas se pausarán automáticamente.</p>
                <br>
                <p>Saludos,<br>Equipo de Tracker</p>
            ";

            $emailService->sendEmail($user->email, $subject, $message);

        } catch (\Exception $e) {
            Log::error('Error sending user alert notification', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificación de auto-pausa por usuario
     */
    private function sendAutoPauseUserNotification($user, $tasks)
    {
        try {
            $emailService = app(EmailService::class);
            
            $subject = "⏸️ Tareas Auto-Pausadas - {$user->name}";
            $message = "
                <h2>Tareas Auto-Pausadas</h2>
                <p>Hola {$user->name},</p>
                <p>Todas tus tareas activas han sido pausadas automáticamente después de 3 alertas sin respuesta.</p>
                <p><strong>Tareas pausadas:</strong></p>
                <ul>";
            
            foreach ($tasks as $task) {
                $message .= "<li><strong>{$task->name}</strong> - {$task->project->name} ({$task->sprint->name})</li>";
            }
            
            $message .= "
                </ul>
                <p>Para reanudar el trabajo en estas tareas, ve a tu tablero de tareas y haz clic en 'Reanudar Auto-Pausada' en cada tarea.</p>
                <br>
                <p>Saludos,<br>Equipo de Tracker</p>
            ";

            $emailService->sendEmail($user->email, $subject, $message);

        } catch (\Exception $e) {
            Log::error('Error sending auto-pause user notification', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }
}
