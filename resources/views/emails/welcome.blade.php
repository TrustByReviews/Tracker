<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Tracker</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .welcome-message {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 30px;
        }
        .credentials-card {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .credential-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .credential-label {
            font-weight: 600;
            color: #374151;
            min-width: 100px;
        }
        .credential-value {
            background-color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
            font-size: 14px;
            color: #1f2937;
            border: 1px solid #d1d5db;
            flex: 1;
            margin-left: 16px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .warning-box {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .warning-box h3 {
            margin: 0 0 12px 0;
            color: #92400e;
            font-size: 16px;
        }
        .warning-box ul {
            margin: 0;
            padding-left: 20px;
            color: #92400e;
        }
        .warning-box li {
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
        .social-links {
            margin-top: 16px;
        }
        .social-links a {
            color: #6b7280;
            text-decoration: none;
            margin: 0 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Welcome to Tracker</h1>
            <p>Your account has been successfully created</p>
        </div>
        
        <div class="content">
            <div class="welcome-message">
                <p>Hello <strong>{{ $userName }}</strong>,</p>
                <p>Welcome to Tracker! Your account has been created and you're now ready to start managing your projects efficiently.</p>
            </div>
            
            <div class="credentials-card">
                <h3 style="margin: 0 0 20px 0; color: #374151;">Your Login Credentials</h3>
                
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $email }}</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="cta-button">
                    🚀 Get Started - Login Now
                </a>
            </div>
            
            <div class="warning-box">
                <h3>🔒 Security Reminders</h3>
                <ul>
                    <li>Keep your credentials in a secure location</li>
                    <li>We recommend changing your password after your first login</li>
                    <li>Never share your login credentials with anyone</li>
                    <li>Enable two-factor authentication for additional security</li>
                </ul>
            </div>
            
            <p style="color: #6b7280; font-size: 14px;">
                If you have any questions or need assistance, please don't hesitate to contact our support team.
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Tracker. All rights reserved.</p>
            <div class="social-links">
                <a href="#">Support</a> • 
                <a href="#">Documentation</a> • 
                <a href="#">Privacy Policy</a>
            </div>
        </div>
    </div>
</body>
</html> 