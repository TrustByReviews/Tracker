<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Payment Report</title>
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
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .project-info {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .summary-box {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }
        .qa-highlight {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #2196f3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .stat-item {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Project Payment Report</h1>
        <h2>{{ $project['name'] }}</h2>
    </div>

    <div class="content">
        <p>Hello,</p>
        
        <p>Please find attached the payment report for <strong>{{ $project['name'] }}</strong> project.</p>

        <div class="project-info">
            <h3>Project Information</h3>
            <p><strong>Project:</strong> {{ $project['name'] }}</p>
            <p><strong>Description:</strong> {{ $project['description'] ?? 'N/A' }}</p>
            <p><strong>Period:</strong> {{ $period['start'] ?? 'N/A' }} to {{ $period['end'] ?? 'N/A' }}</p>
            <p><strong>Generated:</strong> {{ $generated_at }}</p>
        </div>

        <div class="summary-box">
            <h3>Project Summary</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ count($developers) }}</div>
                    <div class="stat-label">Team Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ number_format($totalHours, 1) }}</div>
                    <div class="stat-label">Total Hours</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${{ number_format($totalEarnings, 2) }}</div>
                    <div class="stat-label">Total Earnings</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $developers->sum('completed_tasks') }}</div>
                    <div class="stat-label">Completed Items</div>
                </div>
            </div>
        </div>

        <div class="qa-highlight">
            <h4>📊 QA Testing Activities Included</h4>
            <p>This report includes both development work and QA testing activities:</p>
            <ul>
                <li><strong>Development Work:</strong> Tasks and bugs completed by developers</li>
                <li><strong>QA Testing:</strong> Time spent testing tasks and bugs by QA team</li>
                <li><strong>Separate Tracking:</strong> QA earnings are calculated separately from development work</li>
            </ul>
        </div>

        <div class="summary-box">
            <h3>Team Members Overview</h3>
            <p>The report includes <strong>{{ count($developers) }}</strong> team members with the following breakdown:</p>
            
            @php
                $developmentEarnings = collect($developers)->sum(function ($dev) {
                    return $dev['total_earnings'] - $dev['qa_task_earnings'] - $dev['qa_bug_earnings'];
                });
                $qaEarnings = collect($developers)->sum(function ($dev) {
                    return $dev['qa_task_earnings'] + $dev['qa_bug_earnings'];
                });
            @endphp
            
            <ul>
                <li><strong>Development Earnings:</strong> ${{ number_format($developmentEarnings, 2) }}</li>
                <li><strong>QA Testing Earnings:</strong> ${{ number_format($qaEarnings, 2) }}</li>
                <li><strong>Total Project Earnings:</strong> ${{ number_format($totalEarnings, 2) }}</li>
            </ul>
        </div>

        <p>The detailed report is attached to this email in PDF format, containing:</p>
        <ul>
            <li>Individual team member breakdown</li>
            <li>Detailed work items (tasks and bugs)</li>
            <li>QA testing activities and time tracking</li>
            <li>Earnings calculation for each team member</li>
            <li>Project summary and totals</li>
        </ul>

        <p>If you have any questions about this report, please don't hesitate to contact us.</p>

        <p>Best regards,<br>
        <strong>Tracker System</strong></p>
    </div>

    <div class="footer">
        <p>This report was automatically generated by the Tracker System</p>
        <p>Includes both development work and QA testing activities</p>
    </div>
</body>
</html> 