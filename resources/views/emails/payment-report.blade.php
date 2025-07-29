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
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .summary {
            background: #f8fafc;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
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
            font-weight: 600;
        }
        .summary-item p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 14px;
        }
        .developer-section {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .developer-header {
            background: #3b82f6;
            color: white;
            padding: 20px;
        }
        .developer-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        .developer-info {
            padding: 20px;
        }
        .developer-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-item h4 {
            margin: 0;
            color: #1e40af;
            font-size: 18px;
            font-weight: 600;
        }
        .stat-item p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 12px;
        }
        .footer {
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 20px;
        }
        .button:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Report</h1>
            <p>Generated on {{ $generated_at }}</p>
            @if($period['start'] && $period['end'])
                <p>Period: {{ $period['start'] }} to {{ $period['end'] }}</p>
            @endif
        </div>

        <div class="content">
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
                    <p><strong>Tasks completed in this period:</strong></p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        @foreach($developer['tasks'] as $task)
                        <li style="margin-bottom: 5px;">
                            <strong>{{ $task['name'] }}</strong> ({{ $task['project'] }}) - 
                            {{ $task['hours'] }}h - ${{ number_format($task['earnings'], 2) }}
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p style="text-align: center; color: #6b7280; font-style: italic;">No completed tasks in this period</p>
                    @endif
                </div>
            </div>
            @endforeach

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ config('app.url') }}/dashboard" class="button">View Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p>This report was generated automatically by Tracker System</p>
            <p>For questions or support, please contact your administrator</p>
            <p>© {{ date('Y') }} Tracker System. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 