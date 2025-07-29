<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
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
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .summary-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1em;
            color: #28a745;
        }
        .tasks-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .task-item {
            padding: 10px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .task-name {
            font-weight: bold;
            color: #007bff;
        }
        .task-details {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 0.9em;
        }
        .highlight {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Weekly Report</h1>
        <p>{{ $startDate }} - {{ $endDate }}</p>
    </div>

    <div class="content">
        <h2>Hello {{ $developerName }}!</h2>
        
        <p>Here's your weekly work summary for the period <strong>{{ $startDate }} to {{ $endDate }}</strong>.</p>

        <div class="summary">
            <h3>📈 Summary</h3>
            <div class="summary-item">
                <span>Hourly Rate:</span>
                <span>${{ number_format($reportData['hour_value'], 2) }}/hour</span>
            </div>
            <div class="summary-item">
                <span>Tasks Completed:</span>
                <span>{{ $reportData['tasks_count'] }} tasks</span>
            </div>
            <div class="summary-item">
                <span>Total Hours:</span>
                <span>{{ $reportData['total_hours'] }} hours</span>
            </div>
            <div class="summary-item">
                <span>Total Payment:</span>
                <span>${{ number_format($reportData['total_payment'], 2) }}</span>
            </div>
        </div>

        @if(!empty($reportData['tasks']))
        <div class="tasks-section">
            <h3>📝 Tasks Completed</h3>
            @foreach($reportData['tasks'] as $task)
            <div class="task-item">
                <div class="task-name">{{ $task['name'] }}</div>
                <div class="task-details">
                    <strong>Project:</strong> {{ $task['project'] }} | 
                    <strong>Hours:</strong> {{ $task['hours'] }}h | 
                    <strong>Status:</strong> {{ ucfirst($task['status']) }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="highlight">
            <h4>💰 Payment Information</h4>
            <p>Your payment for this week: <strong>${{ number_format($reportData['total_payment'], 2) }}</strong></p>
            <p>This amount will be processed according to your payment schedule.</p>
        </div>

        <p>Thank you for your hard work this week! 🚀</p>
    </div>

    <div class="footer">
        <p>This is an automated report from Tracker</p>
        <p>If you have any questions, please contact your project manager.</p>
    </div>
</body>
</html> 