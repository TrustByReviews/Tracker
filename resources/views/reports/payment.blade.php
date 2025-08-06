<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            font-size: 10px;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 18px;
        }
        .header p {
            color: #7f8c8d;
            margin: 3px 0;
            font-size: 10px;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary h2 {
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        .summary-item {
            text-align: center;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 3px;
        }
        .summary-item h3 {
            margin: 0;
            color: #7f8c8d;
            font-size: 9px;
        }
        .summary-item p {
            margin: 3px 0 0 0;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        .summary-item .amount {
            color: #27ae60;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        th {
            background-color: #3498db;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            padding: 4px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .efficiency-positive {
            color: #27ae60;
            font-weight: bold;
        }
        .efficiency-negative {
            color: #e74c3c;
            font-weight: bold;
        }
        .efficiency-neutral {
            color: #f39c12;
            font-weight: bold;
        }
        .currency {
            text-align: right;
        }
        .page-break {
            page-break-before: always;
        }
        .notes {
            margin-top: 20px;
            padding: 10px;
            background-color: #ecf0f1;
            border-radius: 3px;
            font-size: 9px;
        }
        .notes h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 11px;
        }
        .notes ul {
            margin: 5px 0;
            padding-left: 15px;
        }
        .notes li {
            margin-bottom: 3px;
        }
        .developer-name {
            font-weight: bold;
            font-size: 9px;
        }
        .email {
            font-size: 8px;
            color: #7f8c8d;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            margin: 15px 0 10px 0;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
        }
        .no-data {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 20px;
            font-size: 10px;
        }
        @media print {
            body {
                font-size: 9px;
            }
            table {
                font-size: 8px;
            }
            th, td {
                padding: 3px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PAYMENT REPORT - TASK TRACKING SYSTEM</h1>
        <p>Generated on: {{ $generated_at }}</p>
        @if($period['start'] && $period['end'])
            <p>Period: {{ $period['start'] }} to {{ $period['end'] }}</p>
        @else
            <p>Period: All available data</p>
        @endif
    </div>

    <div class="summary">
        <h2>General Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <h3>Total Developers</h3>
                <p>{{ count($developers) }}</p>
            </div>
            <div class="summary-item">
                <h3>Total Hours</h3>
                <p>{{ number_format($totalHours, 2) }}</p>
            </div>
            <div class="summary-item">
                <h3>Total Paid</h3>
                <p class="amount">${{ number_format($totalEarnings, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="section-title">Developer Summary</div>
    <table>
        <thead>
            <tr>
                <th>Developer</th>
                <th>Hourly Rate</th>
                <th>Tasks</th>
                <th>Hours</th>
                <th>Total Earned</th>
            </tr>
        </thead>
        <tbody>
            @foreach($developers as $developer)
                <tr>
                    <td>
                        <div class="developer-name">{{ $developer['name'] }}</div>
                        <div class="email">{{ $developer['email'] }}</div>
                    </td>
                    <td class="currency">${{ number_format($developer['hour_value'], 2) }}</td>
                    <td>{{ $developer['completed_tasks'] + $developer['in_progress_tasks'] }}</td>
                    <td>{{ number_format($developer['total_hours'], 2) }}</td>
                    <td class="currency">${{ number_format($developer['total_earnings'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(collect($developers)->some(fn($dev) => count($dev['tasks']) > 0))
        <div class="page-break"></div>
        
        <div class="section-title">Task Details</div>
        <table>
            <thead>
                <tr>
                    <th>Developer</th>
                    <th>Task</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Hours</th>
                    <th>Payment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($developers as $developer)
                    @foreach($developer['tasks'] as $task)
                        <tr>
                            <td>{{ $developer['name'] }}</td>
                            <td>{{ $task['name'] }}</td>
                            <td>{{ $task['project'] }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $task['status'])) }}</td>
                            <td>{{ number_format($task['actual_hours'], 2) }}</td>
                            <td class="currency">${{ number_format($task['earnings'], 2) }}</td>
                            <td>{{ $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : ($task['created_at'] ? date('Y-m-d', strtotime($task['created_at'])) : 'N/A') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            No tasks found in the specified period
        </div>
    @endif

    <div class="notes">
        <h3>Important Notes:</h3>
        <ul>
            <li>This report includes both completed and in-progress tasks in the specified period</li>
            <li>For in-progress tasks, hours are calculated based on work time until the report generation date</li>
            <li>Monetary values are in US dollars</li>
            <li>Hours are calculated based on actual recorded time for completed tasks and estimated progress for in-progress tasks</li>
            <li>This report is automatically generated by the system</li>
        </ul>
    </div>
</body>
</html> 