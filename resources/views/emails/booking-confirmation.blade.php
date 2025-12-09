<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: #d4ff00;
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            color: #94a3b8;
            margin: 8px 0 0;
            font-size: 14px;
        }
        .content {
            padding: 32px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .message {
            color: #64748b;
            margin-bottom: 24px;
        }
        .details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 16px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            width: 120px;
            flex-shrink: 0;
        }
        .detail-value {
            color: #1e293b;
            font-weight: 500;
        }
        .meeting-link {
            background: #0f172a;
            color: #d4ff00;
            text-decoration: none;
            padding: 16px 24px;
            border-radius: 8px;
            display: inline-block;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
        }
        .meeting-link:hover {
            background: #1e293b;
        }
        .footer {
            text-align: center;
            padding: 24px 32px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 12px;
        }
        .trainer-info {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .trainer-name {
            font-weight: 600;
            color: #1e293b;
        }
        .trainer-email {
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Booking Confirmed</h1>
                <p>Your session has been scheduled</p>
            </div>

            <div class="content">
                <p class="greeting">Hi {{ $booking->attendee_name }},</p>

                <p class="message">
                    Great news! Your session with {{ $trainer->first_name }} {{ $trainer->last_name }} has been confirmed.
                    Here are your booking details:
                </p>

                <div class="details">
                    <div class="detail-row">
                        <span class="detail-label">Session</span>
                        <span class="detail-value">{{ $eventType->name }} ({{ $eventType->duration }} min)</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date</span>
                        <span class="detail-value">{{ $booking->start_time->format('l, F j, Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Time</span>
                        <span class="detail-value">{{ $booking->start_time->format('g:i A') }} - {{ $booking->end_time->format('g:i A') }} ({{ $booking->timezone }})</span>
                    </div>
                    @if($booking->location)
                    <div class="detail-row">
                        <span class="detail-label">Location</span>
                        <span class="detail-value">{{ $booking->location }}</span>
                    </div>
                    @endif
                </div>

                @if($booking->meeting_url)
                <p style="text-align: center; margin-bottom: 24px;">
                    <a href="{{ $booking->meeting_url }}" class="meeting-link">
                        Join Meeting
                    </a>
                </p>
                @endif

                <div class="trainer-info">
                    <p style="margin: 0 0 4px;"><span class="trainer-name">{{ $trainer->first_name }} {{ $trainer->last_name }}</span></p>
                    <p style="margin: 0;"><span class="trainer-email">{{ $trainer->email }}</span></p>
                </div>

                @if($booking->notes)
                <p style="color: #64748b; font-size: 14px;">
                    <strong>Your notes:</strong> {{ $booking->notes }}
                </p>
                @endif
            </div>

            <div class="footer">
                <p>Need to make changes? Contact your trainer directly.</p>
                <p style="margin-top: 8px;">This email was sent to {{ $booking->attendee_email }}</p>
            </div>
        </div>
    </div>
</body>
</html>
