<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset - Tracker</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            margin: 24px 0;
            text-align: center;
            transition: transform 0.2s;
            font-size: 16px;
        }
        .reset-button:hover {
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
        .expiry-notice {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
            text-align: center;
            color: #92400e;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .manual-link {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 12px;
            margin: 16px 0;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
            font-size: 14px;
            word-break: break-all;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
            <p>We received a request to reset your password</p>
        </div>
        
        <div class="content">
            <div class="message">
                <p>Hello <strong>{{ $userName }}</strong>,</p>
                <p>We received a request to reset your password for your Tracker account. If you didn't make this request, you can safely ignore this email.</p>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-button">
                    🔑 Reset My Password
                </a>
            </div>
            
            <div class="expiry-notice">
                <strong>⏰ This link will expire on {{ $expiresAt }}</strong>
            </div>
            
            <div class="info-box">
                <h3>📋 What happens next?</h3>
                <ul>
                    <li>Click the button above to go to the password reset page</li>
                    <li>Enter your new password (minimum 8 characters)</li>
                    <li>Confirm your new password</li>
                    <li>You'll be automatically logged in with your new password</li>
                </ul>
            </div>
            
            <p style="color: #6b7280; font-size: 14px;">
                If the button doesn't work, you can copy and paste this link into your browser:
            </p>
            
            <div class="manual-link">
                {{ $resetUrl }}
            </div>
            
            <div class="info-box">
                <h3>🔒 Security Tips</h3>
                <ul>
                    <li>Use a strong, unique password</li>
                    <li>Don't share your password with anyone</li>
                    <li>Consider enabling two-factor authentication</li>
                    <li>If you didn't request this reset, contact support immediately</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Tracker. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 