<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Achievement - {{ $event->title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Crimson Text', serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .certificate-container {
            background: #ffffff;
            width: 100%;
            max-width: 800px;
            position: relative;
            border: 8px solid #d4af37;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .certificate-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .certificate-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.1) 10px,
                rgba(255,255,255,0.1) 20px
            );
        }
        
        .certificate-title {
            font-family: 'Great Vibes', cursive;
            font-size: 4rem;
            font-weight: 400;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .certificate-subtitle {
            font-size: 1.3rem;
            font-weight: 400;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            font-style: italic;
        }
        
        .certificate-body {
            padding: 50px 40px;
            text-align: center;
            position: relative;
            background: #fafafa;
        }
        
        .certificate-body::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #d4af37;
            border-radius: 5px;
            opacity: 0.3;
        }
        
        .award-text {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 25px;
            font-weight: 400;
            line-height: 1.6;
        }
        
        .participant-name {
            font-family: 'Great Vibes', cursive;
            font-size: 3.5rem;
            font-weight: 400;
            color: #1e3c72;
            margin: 30px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .event-details {
            background: white;
            border: 2px solid #d4af37;
            border-radius: 10px;
            padding: 25px;
            margin: 35px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .event-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .event-date {
            font-size: 1.1rem;
            color: #666;
            font-weight: 400;
        }
        
        .event-location {
            font-size: 1rem;
            color: #888;
            margin-top: 8px;
            font-style: italic;
        }
        
        .achievement-text {
            font-size: 1.2rem;
            color: #444;
            margin: 30px 0;
            font-style: italic;
            line-height: 1.6;
        }
        
        .certificate-footer {
            background: #1e3c72;
            color: white;
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .serial-info {
            text-align: left;
        }
        
        .serial-label {
            font-size: 0.9rem;
            color: #b8c5d1;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .serial-number {
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            color: #d4af37;
            font-weight: 700;
            margin-top: 5px;
        }
        
        .issued-info {
            text-align: right;
        }
        
        .issued-label {
            font-size: 0.9rem;
            color: #b8c5d1;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .issued-date {
            font-size: 1rem;
            color: #d4af37;
            font-weight: 700;
            margin-top: 5px;
        }
        
        .decorative-border {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 1px solid #d4af37;
            border-radius: 5px;
            opacity: 0.5;
        }
        
        .corner-ornament {
            position: absolute;
            width: 40px;
            height: 40px;
            border: 3px solid #d4af37;
            border-radius: 50%;
            opacity: 0.3;
        }
        
        .corner-ornament.top-left {
            top: 30px;
            left: 30px;
        }
        
        .corner-ornament.top-right {
            top: 30px;
            right: 30px;
        }
        
        .corner-ornament.bottom-left {
            bottom: 30px;
            left: 30px;
        }
        
        .corner-ornament.bottom-right {
            bottom: 30px;
            right: 30px;
        }
        
        .seal {
            position: absolute;
            top: 50%;
            right: 30px;
            transform: translateY(-50%);
            width: 80px;
            height: 80px;
            border: 3px solid #d4af37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(212, 175, 55, 0.1);
        }
        
        .seal-text {
            font-size: 0.7rem;
            font-weight: 700;
            color: #1e3c72;
            text-align: center;
            line-height: 1.2;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: end;
        }
        
        .signature {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            width: 150px;
            height: 2px;
            background: #1e3c72;
            margin: 0 auto 8px;
        }
        
        .signature-text {
            font-size: 0.8rem;
            color: #666;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="decorative-border"></div>
        <div class="corner-ornament top-left"></div>
        <div class="corner-ornament top-right"></div>
        <div class="corner-ornament bottom-left"></div>
        <div class="corner-ornament bottom-right"></div>
        
        <div class="certificate-header">
            <h1 class="certificate-title">Certificate of Achievement</h1>
            <p class="certificate-subtitle">This is to certify that</p>
        </div>
        
        <div class="certificate-body">
            <p class="award-text">has successfully completed and participated in</p>
            
            <div class="participant-name">{{ $name }}</div>
            
            <div class="event-details">
                <h2 class="event-title">{{ $event->title }}</h2>
                <p class="event-date">
                    {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}
                    @if($event->start_time)
                        at {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                    @endif
                </p>
                @if($event->location)
                    <p class="event-location">📍 {{ $event->location }}</p>
                @endif
            </div>
            
            <p class="achievement-text">
                This certificate is presented in recognition of outstanding participation, dedication, and valuable contribution to the success of this educational program.
            </p>
            
            <div class="seal">
                <div class="seal-text">
                    OFFICIAL<br>
                    SEAL
                </div>
            </div>
            
            <div class="signature-section">
                <div class="signature">
                    <div class="signature-line"></div>
                    <p class="signature-text">Event Director</p>
                </div>
                <div class="signature">
                    <div class="signature-line"></div>
                    <p class="signature-text">School Principal</p>
                </div>
            </div>
        </div>
        
        <div class="certificate-footer">
            <div class="serial-info">
                <p class="serial-label">Certificate Number</p>
                <p class="serial-number">{{ $serial }}</p>
            </div>
            <div class="issued-info">
                <p class="issued-label">Date of Issue</p>
                <p class="issued-date">{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>







