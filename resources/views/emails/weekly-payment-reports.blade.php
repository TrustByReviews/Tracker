<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Semanal de Pagos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .summary h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2980b9;
        }
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .developers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .developers-table th {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .developers-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
        }
        .developers-table tr:hover {
            background: #f8f9fa;
        }
        .amount {
            font-weight: bold;
            color: #27ae60;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            color: #7f8c8d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💰 Reporte Semanal de Pagos</h1>
        <p>Período: {{ \Carbon\Carbon::parse($week_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($week_end)->format('d/m/Y') }}</p>
        <p>Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="content">
        <div class="summary">
            <h3>📊 Resumen General</h3>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value">{{ count($reports) }}</div>
                    <div class="stat-label">Desarrolladores</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">COP {{ number_format($total_payment, 0, ',', '.') }},00</div>
                    <div class="stat-label">Total a Pagar</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format(array_sum(array_column($reports, 'total_hours')), 1, ',', '.') }}h</div>
                    <div class="stat-label">Horas Totales</div>
                </div>
            </div>
        </div>

        <div class="summary">
            <h3>👥 Detalle por Desarrollador</h3>
            <table class="developers-table">
                <thead>
                    <tr>
                        <th>Desarrollador</th>
                        <th>Horas</th>
                        <th>Tarifa/Hora</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>
                            <strong>{{ $report->user->name }}</strong><br>
                            <small>{{ $report->user->email }}</small>
                        </td>
                        <td>{{ number_format($report->total_hours, 1, ',', '.') }}h</td>
                        <td>COP {{ number_format($report->hourly_rate, 0, ',', '.') }},00</td>
                        <td class="amount">COP {{ number_format($report->total_payment, 0, ',', '.') }},00</td>
                        <td>
                            <span style="
                                padding: 4px 8px;
                                border-radius: 4px;
                                font-size: 12px;
                                font-weight: bold;
                                @if($report->status === 'pending')
                                    background: #fff3cd;
                                    color: #856404;
                                @elseif($report->status === 'approved')
                                    background: #d1ecf1;
                                    color: #0c5460;
                                @elseif($report->status === 'paid')
                                    background: #d4edda;
                                    color: #155724;
                                @else
                                    background: #f8d7da;
                                    color: #721c24;
                                @endif
                            ">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary">
            <h3>📋 Tareas Completadas</h3>
            @foreach($reports as $report)
                @if($report->completed_tasks_count > 0)
                <div style="margin-bottom: 15px;">
                    <h4>{{ $report->user->name }} ({{ $report->completed_tasks_count }} tareas)</h4>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        @foreach($report->task_details['completed'] ?? [] as $task)
                        <li>
                            <strong>{{ $task['name'] }}</strong> - 
                            {{ $task['project'] }} - 
                            {{ number_format($task['hours'], 1, ',', '.') }}h - 
                            COP {{ number_format($task['payment'], 0, ',', '.') }},00
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            @endforeach
        </div>

        <div class="summary">
            <h3>⏳ Tareas en Progreso</h3>
            @foreach($reports as $report)
                @if($report->in_progress_tasks_count > 0)
                <div style="margin-bottom: 15px;">
                    <h4>{{ $report->user->name }} ({{ $report->in_progress_tasks_count }} tareas)</h4>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        @foreach($report->task_details['in_progress'] ?? [] as $task)
                        <li>
                            <strong>{{ $task['name'] }}</strong> - 
                            {{ $task['project'] }} - 
                            {{ number_format($task['hours'], 1, ',', '.') }}h - 
                            COP {{ number_format($task['payment'], 0, ',', '.') }},00
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            @endforeach
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/payments/admin') }}" class="btn">Ver Dashboard de Pagos</a>
        </div>
    </div>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema de gestión de pagos.</p>
        <p>Para cualquier consulta, contacte al administrador del sistema.</p>
    </div>
</body>
</html> 