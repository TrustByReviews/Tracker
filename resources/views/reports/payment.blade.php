<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #6b7280;
            margin: 5px 0 0 0;
        }
        .summary {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item h3 {
            margin: 0;
            color: #1e40af;
            font-size: 24px;
        }
        .summary-item p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 14px;
        }
        .developer-section {
            margin-bottom: 30px;
        }
        .developer-header {
            background: #3b82f6;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        .developer-header h2 {
            margin: 0;
            font-size: 20px;
        }
        .developer-info {
            background: #f1f5f9;
            padding: 15px;
            border-left: 4px solid #3b82f6;
        }
        .developer-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item h4 {
            margin: 0;
            color: #1e40af;
            font-size: 18px;
        }
        .stat-item p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .total-row {
            background: #f0f9ff;
            font-weight: 600;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Report</h1>
        <p>Generated on {{ $generated_at }}</p>
        @if($period['start'] && $period['end'])
            <p>Period: {{ $period['start'] }} to {{ $period['end'] }}</p>
        @endif
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <h3>{{ count($developers) }}</h3>
                <p>Developers</p>
            </div>
            <div class="summary-item">
                <h3>${{ number_format($totalEarnings, 2) }}</h3>
                <p>Total Earnings</p>
            </div>
            <div class="summary-item">
                <h3>{{ $totalHours }}h</h3>
                <p>Total Hours</p>
            </div>
        </div>
    </div>

    @foreach($developers as $developer)
    <div class="developer-section">
        <div class="developer-header">
            <h2>{{ $developer['name'] }}</h2>
        </div>
        
        <div class="developer-info">
            <div class="developer-stats">
                <div class="stat-item">
                    <h4>${{ $developer['hour_value'] }}/hr</h4>
                    <p>Hour Rate</p>
                </div>
                <div class="stat-item">
                    <h4>{{ $developer['completed_tasks'] }}</h4>
                    <p>Completed Tasks</p>
                </div>
                <div class="stat-item">
                    <h4>{{ $developer['total_hours'] }}h</h4>
                    <p>Total Hours</p>
                </div>
                <div class="stat-item">
                    <h4>${{ number_format($developer['total_earnings'], 2) }}</h4>
                    <p>Total Earnings</p>
                </div>
            </div>

            @if(count($developer['tasks']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Hours</th>
                        <th>Earnings</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($developer['tasks'] as $task)
                    <tr>
                        <td>{{ $task['name'] }}</td>
                        <td>{{ $task['project'] }}</td>
                        <td>{{ $task['hours'] }}h</td>
                        <td>${{ number_format($task['earnings'], 2) }}</td>
                        <td>{{ $task['completed_at'] ? \Carbon\Carbon::parse($task['completed_at'])->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2"><strong>Total</strong></td>
                        <td><strong>{{ $developer['total_hours'] }}h</strong></td>
                        <td><strong>${{ number_format($developer['total_earnings'], 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            @else
            <p style="text-align: center; color: #6b7280; font-style: italic;">No completed tasks in this period</p>
            @endif
        </div>
    </div>
    @endforeach

    <div class="footer">
        <p>This report was generated automatically by Tracker System</p>
        <p>For questions or support, please contact your administrator</p>
    </div>
</body>
</html> 