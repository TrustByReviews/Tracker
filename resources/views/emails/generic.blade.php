<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
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
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
        }
        .message {
            font-size: 16px;
            color: #1f2937;
            line-height: 1.7;
        }
        .message p {
            margin-bottom: 16px;
        }
        .message p:last-child {
            margin-bottom: 0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 24px;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Tracker</h1>
        </div>
        
        <div class="content">
            @if($userName)
                <div class="greeting">
                    <p>Hello <strong>{{ $userName }}</strong>,</p>
                </div>
            @endif
            
            <div class="message">
                {!! nl2br(e($message)) !!}
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated message from Tracker. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Tracker. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 