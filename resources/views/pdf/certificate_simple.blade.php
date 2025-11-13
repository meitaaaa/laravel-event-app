<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            padding: 0;
        }
        
        .certificate {
            border: 8px solid #1e40af;
            padding: 40px;
            text-align: center;
        }
        
        .inner {
            border: 2px solid #f59e0b;
            padding: 30px;
        }
        
        .school {
            background: #1e40af;
            color: white;
            padding: 8px 25px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .title {
            font-size: 42px;
            font-weight: bold;
            color: #1e40af;
            letter-spacing: 8px;
            margin: 15px 0;
        }
        
        .subtitle {
            font-size: 14px;
            color: #f59e0b;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .line {
            width: 80px;
            height: 3px;
            background: #f59e0b;
            margin: 15px auto;
        }
        
        .text {
            font-size: 13px;
            color: #666;
            margin: 15px 0;
        }
        
        .name {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            margin: 20px 0;
            padding: 12px 30px;
            background: #f0f0f0;
        }
        
        .event {
            font-size: 13px;
            color: #333;
            margin: 15px 0;
            line-height: 1.8;
        }
        
        .event-name {
            font-weight: bold;
            color: #1e40af;
        }
        
        .sig {
            margin-top: 40px;
        }
        
        .sig table {
            width: 100%;
        }
        
        .sig td {
            width: 50%;
            text-align: center;
            padding: 10px;
        }
        
        .sig-line {
            width: 150px;
            height: 1px;
            background: #333;
            margin: 40px auto 8px auto;
        }
        
        .sig-name {
            font-size: 12px;
            font-weight: bold;
            color: #000;
        }
        
        .sig-title {
            font-size: 11px;
            color: #666;
        }
        
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #999;
        }
        
        .footer table {
            width: 100%;
        }
        
        .footer td {
            width: 50%;
        }
        
        .footer .left {
            text-align: left;
        }
        
        .footer .right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="inner">
            <div class="school">SMKN 4 BOGOR</div>
            
            <div class="title">SERTIFIKAT</div>
            <div class="subtitle">CERTIFICATE OF ACHIEVEMENT</div>
            
            <div class="line"></div>
            
            <div class="text">Diberikan Kepada:</div>
            
            <div class="name">{{ strtoupper($name) }}</div>
            
            <div class="event">
                Telah mengikuti dan menyelesaikan kegiatan<br>
                <span class="event-name">{{ $event->title }}</span><br>
                pada tanggal {{ \Carbon\Carbon::parse($event->event_date)->isoFormat('D MMMM Y') }}<br>
                di {{ $event->location }}
            </div>
            
            <div class="sig">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div class="sig-line"></div>
                            <div class="sig-name">Drs. H. Ahmad Fauzi, M.Pd</div>
                            <div class="sig-title">Kepala SMKN 4 Bogor</div>
                        </td>
                        <td>
                            <div class="sig-line"></div>
                            <div class="sig-name">Tim Panitia EduFest</div>
                            <div class="sig-title">Koordinator Acara</div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="footer">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="left">Diterbitkan: {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}</td>
                        <td class="right">No: {{ $serial }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
