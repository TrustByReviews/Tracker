<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Type Payment Report</title>
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
        .user-type-info {
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
        .user-type-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>User Type Payment Report</h1>
        <div class="user-type-badge">{{ ucfirst($user_type) }}</div>
    </div>

    <div class="content">
        <p>Hello,</p>
        
        <p>Please find attached the payment report for <strong>{{ ucfirst($user_type) }}</strong> users.</p>

        <div class="user-type-info">
            <h3>Report Information</h3>
            <p><strong>User Type:</strong> {{ ucfirst($user_type) }}</p>
            <p><strong>Total Users:</strong> {{ count($developers) }}</p>
            <p><strong>Period:</strong> {{ $period['start'] ?? 'N/A' }} to {{ $period['end'] ?? 'N/A' }}</p>
            <p><strong>Generated:</strong> {{ $generated_at }}</p>
        </div>

        <div class="summary-box">
            <h3>{{ ucfirst($user_type) }} Summary</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ count($developers) }}</div>
                    <div class="stat-label">Users</div>
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
            <p>This report includes both development work and QA testing activities for {{ ucfirst($user_type) }} users:</p>
            <ul>
                <li><strong>Development Work:</strong> Tasks and bugs completed by {{ $user_type }}s</li>
                <li><strong>QA Testing:</strong> Time spent testing tasks and bugs (if applicable)</li>
                <li><strong>Separate Tracking:</strong> QA earnings are calculated separately from development work</li>
            </ul>
        </div>

        <div class="summary-box">
            <h3>Earnings Breakdown</h3>
            <p>The report includes <strong>{{ count($developers) }}</strong> {{ $user_type }} users with the following breakdown:</p>
            
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
                <li><strong>Total {{ ucfirst($user_type) }} Earnings:</strong> ${{ number_format($totalEarnings, 2) }}</li>
            </ul>
        </div>

        <div class="summary-box">
            <h3>User Details</h3>
            <p>The detailed report includes information for each {{ $user_type }}:</p>
            <ul>
                <li>Individual user breakdown with hours and earnings</li>
                <li>Detailed work items (tasks and bugs)</li>
                <li>QA testing activities and time tracking (where applicable)</li>
                <li>Earnings calculation for each user</li>
                <li>Summary totals for the {{ $user_type }} group</li>
            </ul>
        </div>

        <p>The detailed report is attached to this email in PDF format.</p>

        <p>If you have any questions about this report, please don't hesitate to contact us.</p>

        <p>Best regards,<br>
        <strong>Tracker System</strong></p>
    </div>

    <div class="footer">
        <p>This report was automatically generated by the Tracker System</p>
        <p>Includes both development work and QA testing activities for {{ ucfirst($user_type) }} users</p>
    </div>
</body>
</html> 