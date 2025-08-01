<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentReportController extends Controller
{
    public function index()
    {
        $this->authorize('viewPaymentReports');

        $developers = User::with(['tasks', 'projects'])
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'admin');
            })
            ->get()
            ->map(function ($developer) {
                $completedTasks = $developer->tasks->where('status', 'done');
                $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                    return ($task->actual_hours ?? 0) * $developer->hour_value;
                });

                return [
                    'id' => $developer->id,
                    'name' => $developer->name,
                    'email' => $developer->email,
                    'hour_value' => $developer->hour_value,
                    'total_tasks' => $developer->tasks->count(),
                    'completed_tasks' => $completedTasks->count(),
                    'total_hours' => $completedTasks->sum('actual_hours'),
                    'total_earnings' => $totalEarnings,
                    'assigned_projects' => $developer->projects->count(),
                ];
            });

        return Inertia::render('PaymentReports/Index', [
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
        ]);
    }

    public function generate(Request $request)
    {
        $this->authorize('generatePaymentReports');

        $request->validate([
            'developer_ids' => 'required|array',
            'developer_ids.*' => 'exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:csv,pdf,email',
        ]);

        $developers = User::with(['tasks' => function ($query) use ($request) {
            $query->where('status', 'done');
            if ($request->start_date) {
                $query->where('actual_finish', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->where('actual_finish', '<=', $request->end_date);
            }
        }, 'projects'])
        ->whereIn('id', $request->developer_ids)
        ->get()
        ->map(function ($developer) {
            $completedTasks = $developer->tasks;
            $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                return ($task->actual_hours ?? 0) * $developer->hour_value;
            });

            return [
                'id' => $developer->id,
                'name' => $developer->name,
                'email' => $developer->email,
                'hour_value' => $developer->hour_value,
                'completed_tasks' => $completedTasks->count(),
                'total_hours' => $completedTasks->sum('actual_hours'),
                'total_earnings' => $totalEarnings,
                'tasks' => $completedTasks->map(function ($task) use ($developer) {
                    return [
                        'name' => $task->name,
                        'project' => $task->sprint->project->name ?? 'N/A',
                        'hours' => $task->actual_hours ?? 0,
                        'earnings' => ($task->actual_hours ?? 0) * $developer->hour_value,
                        'completed_at' => $task->actual_finish,
                    ];
                }),
            ];
        });

        $reportData = [
            'developers' => $developers,
            'totalEarnings' => $developers->sum('total_earnings'),
            'totalHours' => $developers->sum('total_hours'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
        ];

        switch ($request->format) {
            case 'csv':
                return $this->generateCSV($reportData);
            case 'pdf':
                return $this->generatePDF($reportData);
            case 'email':
                return $this->sendEmail($reportData, $request);
        }
    }

    private function generateCSV($data)
    {
        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['Payment Report']);
        fputcsv($output, ['Generated: ' . $data['generated_at']]);
        fputcsv($output, []);
        
        // Developer summary
        fputcsv($output, ['Developer Summary']);
        fputcsv($output, ['Name', 'Email', 'Hour Rate', 'Total Hours', 'Total Earnings']);
        
        foreach ($data['developers'] as $developer) {
            fputcsv($output, [
                $developer['name'],
                $developer['email'],
                '$' . $developer['hour_value'],
                $developer['total_hours'],
                '$' . number_format($developer['total_earnings'], 2)
            ]);
        }
        
        // Task details
        fputcsv($output, []);
        fputcsv($output, ['Task Details']);
        fputcsv($output, ['Developer', 'Task', 'Project', 'Hours', 'Earnings']);
        
        foreach ($data['developers'] as $developer) {
            foreach ($developer['tasks'] as $task) {
                fputcsv($output, [
                    $developer['name'],
                    $task['name'],
                    $task['project'],
                    $task['hours'],
                    '$' . number_format($task['earnings'], 2)
                ]);
            }
        }
        
        fclose($output);
        exit;
    }

    private function generatePDF($data)
    {
        $pdf = PDF::loadView('reports.payment', $data);
        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function sendEmail($data, $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $pdf = PDF::loadView('reports.payment', $data);
        $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.pdf';

        Mail::send('emails.payment-report', $data, function ($message) use ($request, $pdf, $filename) {
            $message->to($request->email)
                    ->subject('Payment Report - ' . date('Y-m-d'))
                    ->attachData($pdf->output(), $filename);
        });

        return redirect()->back()->with('success', 'Payment report sent to ' . $request->email);
    }
}