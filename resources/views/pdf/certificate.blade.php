<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Participation</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .certificate-container {
            background: white;
            border: 20px solid #d4af37;
            border-radius: 15px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .certificate-header {
            margin-bottom: 40px;
        }
        
        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .certificate-subtitle {
            font-size: 24px;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        
        .certificate-content {
            margin: 40px 0;
        }
        
        .certificate-text {
            font-size: 20px;
            line-height: 1.6;
            color: #34495e;
            margin-bottom: 30px;
        }
        
        .participant-name {
            font-size: 36px;
            font-weight: bold;
            color: #e74c3c;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .event-details {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .event-title {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .event-date {
            font-size: 18px;
            color: #7f8c8d;
        }
        
        .serial-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .issued-date {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .border-pattern {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="border-pattern"></div>
        
        <div class="certificate-header">
            <div class="certificate-title">Certificate of Participation</div>
            <div class="certificate-subtitle">This is to certify that</div>
        </div>
        
        <div class="certificate-content">
            <div class="participant-name">{{ $name }}</div>
            
            <div class="certificate-text">
                has successfully participated in the event
            </div>
            
            <div class="event-details">
                <div class="event-title">{{ $event->title }}</div>
                <div class="event-date">
                    {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}
                </div>
            </div>
            
            <div class="certificate-text">
                This certificate is awarded in recognition of active participation and contribution to the success of this event.
            </div>
        </div>
        
        <div class="serial-number">
            Serial: {{ $serial }}
        </div>
        
        <div class="issued-date">
            Issued: {{ $date }}
        </div>
    </div>
</body>
</html>
