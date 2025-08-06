<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Activity Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #2c3e50;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .summary {
            margin-bottom: 30px;
        }
        
        .summary h2 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        
        .summary-item h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #495057;
        }
        
        .summary-item p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin: 25px 0 15px 0;
            padding: 10px;
            background: #ecf0f1;
            border-left: 4px solid #3498db;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .developer-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .email {
            font-size: 10px;
            color: #7f8c8d;
        }
        
        .currency {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .amount {
            color: #27ae60;
            font-weight: bold;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
        }
        
        .notes h3 {
            margin: 0 0 10px 0;
            color: #856404;
            font-size: 14px;
        }
        
        .notes ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .notes li {
            margin-bottom: 5px;
            color: #856404;
        }
        
        .timezone-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 5px;
        }
        
        .timezone-info h3 {
            margin: 0 0 10px 0;
            color: #0c5460;
            font-size: 14px;
        }
        
        .timezone-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }
        
        .timezone-item {
            text-align: center;
            padding: 8px;
            background: white;
            border-radius: 3px;
            border: 1px solid #bee5eb;
        }
        
        .timezone-item .name {
            font-weight: bold;
            font-size: 11px;
            color: #0c5460;
        }
        
        .timezone-item .time {
            font-size: 12px;
            color: #2c3e50;
            margin: 3px 0;
        }
        
        .timezone-item .offset {
            font-size: 10px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DEVELOPER ACTIVITY REPORT - TASK TRACKING SYSTEM</h1>
        <p>Generated on: {{ $generated_at }}</p>
        @if($startDate && $endDate)
            <p>Period: {{ $startDate }} to {{ $endDate }}</p>
        @else
            <p>Period: All available data</p>
        @endif
    </div>

    <!-- Timezone Information -->
    <div class="timezone-info">
        <h3>Current Time Zones</h3>
        <div class="timezone-grid">
            <div class="timezone-item">
                <div class="name">Colombia</div>
                <div class="time">{{ $timezoneInfo['colombia_time'] }}</div>
                <div class="offset">{{ $timezoneInfo['colombia_offset'] }}</div>
            </div>
            <div class="timezone-item">
                <div class="name">Italy</div>
                <div class="time">{{ $timezoneInfo['italy_time'] }}</div>
                <div class="offset">{{ $timezoneInfo['italy_offset'] }}</div>
            </div>
            <div class="timezone-item">
                <div class="name">Spain</div>
                <div class="time">{{ $timezoneInfo['spain_time'] }}</div>
                <div class="offset">{{ $timezoneInfo['spain_offset'] }}</div>
            </div>
            <div class="timezone-item">
                <div class="name">Mexico</div>
                <div class="time">{{ $timezoneInfo['mexico_time'] }}</div>
                <div class="offset">{{ $timezoneInfo['mexico_offset'] }}</div>
            </div>
            <div class="timezone-item">
                <div class="name">Argentina</div>
                <div class="time">{{ $timezoneInfo['argentina_time'] }}</div>
                <div class="offset">{{ $timezoneInfo['argentina_offset'] }}</div>
            </div>
            <div class="timezone-item">
                <div class="name">Brazil</div>
                <div class="time">{{ $timezoneInfo['brazil_time'] }}</div>
                <div class="offset">{{ $timezoneInfo['brazil_offset'] }}</div>
            </div>
            <div class="timezone-item">
                <div class="name">USA (East)</div>
                <div class="time">{{ $timezoneInfo['usa_east_time'] }}</div>
                <div class="offset">{{ $timezoneInfo['usa_east_offset'] }}</div>
            </div>
            <div class="timezone-item">
                <div class="name">UTC</div>
                <div class="time">{{ $timezoneInfo['utc_time'] }}</div>
                <div class="offset">Universal Time</div>
            </div>
        </div>
    </div>

    <div class="summary">
        <h2>General Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <h3>Total Developers</h3>
                <p>{{ count($developers) }}</p>
            </div>
            <div class="summary-item">
                <h3>Total Sessions</h3>
                <p>{{ collect($developers)->sum('total_sessions') }}</p>
            </div>
            <div class="summary-item">
                <h3>Avg Session Duration</h3>
                <p>{{ round(collect($developers)->avg('avg_session_duration_minutes'), 2) }} min</p>
            </div>
            <div class="summary-item">
                <h3>Total Task Activities</h3>
                <p>{{ collect($developers)->sum('task_activities') }}</p>
            </div>
        </div>
    </div>

    <div class="section-title">Developer Activity Summary</div>
    <table>
        <thead>
            <tr>
                <th>Developer</th>
                <th>Total Sessions</th>
                <th>Avg Session (min)</th>
                <th>Task Activities</th>
                <th>Preferred Time</th>
                <th>Morning</th>
                <th>Afternoon</th>
                <th>Night</th>
            </tr>
        </thead>
        <tbody>
            @foreach($developers as $developer)
                <tr>
                    <td>
                        <div class="developer-name">{{ $developer['developer'] }}</div>
                    </td>
                    <td>{{ $developer['total_sessions'] }}</td>
                    <td>{{ $developer['avg_session_duration_minutes'] }}</td>
                    <td>{{ $developer['task_activities'] }}</td>
                    <td>{{ $developer['preferred_work_time'] }}</td>
                    <td>{{ $developer['activity_by_period']['morning'] ?? 0 }}</td>
                    <td>{{ $developer['activity_by_period']['afternoon'] ?? 0 }}</td>
                    <td>{{ $developer['activity_by_period']['night'] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($developers) > 0)
        <div class="page-break"></div>
        
        <div class="section-title">Developer Details</div>
        <table>
            <thead>
                <tr>
                    <th>Developer</th>
                    <th>Period</th>
                    <th>Total Sessions</th>
                    <th>Avg Session (min)</th>
                    <th>Task Activities</th>
                    <th>Preferred Time</th>
                    <th>Most Active Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach($developers as $developer)
                    <tr>
                        <td>{{ $developer['developer'] }}</td>
                        <td>{{ $developer['period']['start'] }} to {{ $developer['period']['end'] }}</td>
                        <td>{{ $developer['total_sessions'] }}</td>
                        <td>{{ $developer['avg_session_duration_minutes'] }}</td>
                        <td>{{ $developer['task_activities'] }}</td>
                        <td>{{ $developer['preferred_work_time'] }}</td>
                        <td>
                            @foreach($developer['most_active_hours'] as $hour => $count)
                                {{ $hour }}:00 ({{ $count }})
                                @if(!$loop->last), @endif
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            No developer activity data found in the specified period
        </div>
    @endif

    <div class="notes">
        <h3>Important Notes:</h3>
        <ul>
            <li>This report includes activity data for all developers in the specified period</li>
            <li>Session duration is calculated from login to logout activities</li>
            <li>Task activities include start, pause, resume, and finish actions</li>
            <li>Time periods are based on Colombia timezone (morning: 6-12, afternoon: 12-18, night: 18-6)</li>
            <li>Most active hours show the top 5 hours with highest activity count</li>
            <li>All times are converted to Colombia timezone for consistency</li>
            <li>This report is automatically generated by the system</li>
        </ul>
    </div>
</body>
</html> 