<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Payment Report - {{ $project['name'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .project-info {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table th,
        .details-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        .details-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .summary-section {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .qa-earnings {
            color: #2c5aa0;
            font-weight: bold;
        }
        .development-earnings {
            color: #28a745;
            font-weight: bold;
        }
        .total-earnings {
            color: #dc3545;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Project Payment Report</h1>
        <h2>{{ $project['name'] }}</h2>
        <p>Generated: {{ $generated_at }}</p>
    </div>

    <div class="project-info">
        <h3>Project Information</h3>
        <p><strong>Name:</strong> {{ $project['name'] }}</p>
        <p><strong>Description:</strong> {{ $project['description'] ?? 'N/A' }}</p>
        <p><strong>Period:</strong> {{ $period['start'] ?? 'N/A' }} to {{ $period['end'] ?? 'N/A' }}</p>
    </div>

    <div class="summary-section">
        <h3>Team Member Summary</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Hour Rate ($)</th>
                    <th>Total Hours</th>
                    <th>Total Earnings ($)</th>
                    <th>QA Task Earnings ($)</th>
                    <th>QA Bug Earnings ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($developers as $developer)
                <tr>
                    <td>{{ $developer['name'] }}</td>
                    <td>{{ ucfirst($developer['role']) }}</td>
                    <td>{{ $developer['email'] }}</td>
                    <td>{{ number_format($developer['hour_value'], 2) }}</td>
                    <td>{{ number_format($developer['total_hours'], 2) }}</td>
                    <td class="total-earnings">{{ number_format($developer['total_earnings'], 2) }}</td>
                    <td class="qa-earnings">{{ number_format($developer['qa_task_earnings'], 2) }}</td>
                    <td class="qa-earnings">{{ number_format($developer['qa_bug_earnings'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-section">
        <h3>Work Details</h3>
        <table class="details-table">
            <thead>
                <tr>
                    <th>Team Member</th>
                    <th>Role</th>
                    <th>Work Item</th>
                    <th>Type</th>
                    <th>Project</th>
                    <th>Hours</th>
                    <th>Earnings ($)</th>
                    <th>Completed Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($developers as $developer)
                    @foreach($developer['tasks'] as $task)
                    <tr>
                        <td>{{ $developer['name'] }}</td>
                        <td>{{ ucfirst($developer['role']) }}</td>
                        <td>{{ $task['name'] }}</td>
                        <td>{{ $task['type'] }}</td>
                        <td>{{ $task['project'] }}</td>
                        <td>{{ number_format($task['hours'], 2) }}</td>
                        <td>{{ number_format($task['earnings'], 2) }}</td>
                        <td>{{ $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A' }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-section">
        <h3>Project Summary</h3>
        @php
            $developmentEarnings = collect($developers)->sum(function ($dev) {
                return $dev['total_earnings'] - $dev['qa_task_earnings'] - $dev['qa_bug_earnings'];
            });
            $qaEarnings = collect($developers)->sum(function ($dev) {
                return $dev['qa_task_earnings'] + $dev['qa_bug_earnings'];
            });
        @endphp
        <table class="summary-table">
            <tr>
                <td><strong>Total Team Members:</strong></td>
                <td>{{ count($developers) }}</td>
            </tr>
            <tr>
                <td><strong>Total Hours:</strong></td>
                <td>{{ number_format($totalHours, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Earnings:</strong></td>
                <td class="total-earnings">{{ number_format($totalEarnings, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Development Earnings:</strong></td>
                <td class="development-earnings">{{ number_format($developmentEarnings, 2) }}</td>
            </tr>
            <tr>
                <td><strong>QA Earnings:</strong></td>
                <td class="qa-earnings">{{ number_format($qaEarnings, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 40px; text-align: center; color: #666; font-size: 12px;">
        <p>Report generated by Tracker System</p>
        <p>This report includes both development work and QA testing activities</p>
    </div>
</body>
</html> 