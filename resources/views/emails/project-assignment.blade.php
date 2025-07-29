<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Assignment - Tracker</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9fafb;
        }
        .container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 40px 30px;
        }
        .message {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 30px;
        }
        .project-card {
            background-color: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            text-align: center;
        }
        .project-name {
            font-size: 24px;
            font-weight: 700;
            color: #166534;
            margin-bottom: 8px;
        }
        .project-status {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            margin: 24px 0;
            text-align: center;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-1px);
        }
        .info-box {
            background-color: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .info-box h3 {
            margin: 0 0 12px 0;
            color: #0c4a6e;
            font-size: 16px;
        }
        .info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #0c4a6e;
        }
        .info-box li {
            margin-bottom: 6px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .assignment-details {
            background-color: #f3f4f6;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .detail-item:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #374151;
        }
        .detail-value {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 New Project Assignment</h1>
            <p>You have been assigned to a new project</p>
        </div>
        
        <div class="content">
            <div class="message">
                <p>Hello <strong>{{ $userName }}</strong>,</p>
                <p>Great news! You have been assigned to work on a new project in Tracker.</p>
            </div>
            
            <div class="project-card">
                <div class="project-name">{{ $projectName }}</div>
                <div class="project-status">Active</div>
            </div>
            
            <div class="assignment-details">
                <div class="detail-item">
                    <span class="detail-label">Assigned by:</span>
                    <span class="detail-value">{{ $assignedBy }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Assignment date:</span>
                    <span class="detail-value">{{ date('M d, Y') }}</span>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $projectUrl }}" class="cta-button">
                    📋 View Project Details
                </a>
            </div>
            
            <div class="info-box">
                <h3>🚀 What you can do now:</h3>
                <ul>
                    <li>Review the project requirements and objectives</li>
                    <li>Check the project timeline and milestones</li>
                    <li>Familiarize yourself with the team members</li>
                    <li>Start working on your assigned tasks</li>
                    <li>Update your progress regularly</li>
                </ul>
            </div>
            
            <p style="color: #6b7280; font-size: 14px;">
                If you have any questions about this project assignment, please contact your project manager or team lead.
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Tracker. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 