<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset OTP - Tracker</title>
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
        .otp-container {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            text-align: center;
        }
        .otp-code {
            font-size: 48px;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: 8px;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
            margin: 16px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
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
        .warning-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .warning-box h3 {
            margin: 0 0 8px 0;
            color: #991b1b;
            font-size: 14px;
        }
        .warning-box p {
            margin: 0;
            color: #7f1d1d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset OTP</h1>
            <p>Enter this code to reset your password</p>
        </div>
        
        <div class="content">
            <div class="message">
                <p>Hello <strong>{{ $userName }}</strong>,</p>
                <p>We received a request to reset your password for your Tracker account. Use the OTP code below to complete the process.</p>
            </div>
            
            <div class="otp-container">
                <div class="otp-label">Your OTP Code</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-label">Enter this code in the app</div>
            </div>
            
            <div class="expiry-notice">
                <strong>⏰ This code will expire at {{ $expiresAt }}</strong>
            </div>
            
            <div class="info-box">
                <h3>📋 How to use this OTP:</h3>
                <ul>
                    <li>Go to the password reset page in your app</li>
                    <li>Enter the 6-digit code above</li>
                    <li>Create your new password</li>
                    <li>You'll be automatically logged in</li>
                </ul>
            </div>
            
            <div class="warning-box">
                <h3>🔒 Security Notice</h3>
                <p>If you didn't request this password reset, please ignore this email and contact support immediately. This code will expire in 10 minutes for your security.</p>
            </div>
            
            <p style="color: #6b7280; font-size: 14px;">
                For security reasons, this OTP can only be used once and will expire automatically.
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Tracker. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 