<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Invited to Join as a Trainer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1a1a1a;
            font-size: 24px;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin: 0 0 16px 0;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #000000;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            background-color: #333333;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .expiry-notice {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }
        .link-fallback {
            font-size: 12px;
            color: #999;
            word-break: break-all;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>You're Invited!</h1>
        </div>

        <div class="content">
            <p>Hi {{ $firstName }},</p>

            <p>You've been invited to join our platform as a trainer. Click the button below to complete your account setup and get started.</p>

            <div class="button-container">
                <a href="{{ $setupUrl }}" class="button">Complete Setup</a>
            </div>

            <p class="link-fallback">
                If the button doesn't work, copy and paste this link into your browser:<br>
                {{ $setupUrl }}
            </p>

            <div class="expiry-notice">
                <strong>Note:</strong> This invitation expires on {{ $expiresAt }}. Please complete your setup before then.
            </div>
        </div>

        <div class="footer">
            <p>If you didn't expect this invitation, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>
