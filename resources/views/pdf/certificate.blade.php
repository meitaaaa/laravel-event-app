<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Penghargaan - {{ $event->title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            width: 297mm;
            height: 210mm;
            position: relative;
        }
        
        .certificate {
            width: 100%;
            height: 100%;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }
        
        /* Ribbon Emas Kiri */
        .ribbon-left {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
            height: 180px;
            background: linear-gradient(135deg, #d4af37 0%, #f4d03f 50%, #d4af37 100%);
            clip-path: polygon(0 0, 100% 0, 100% 70%, 50% 85%, 0 70%);
            box-shadow: 3px 3px 10px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .ribbon-triangle {
            position: absolute;
            left: 25px;
            top: 120px;
            width: 0;
            height: 0;
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
            border-top: 25px solid #c9a227;
        }
        
        /* Ornamen Bawah */
        .wave-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(to top, #1e3a5f 0%, #2d5a8c 30%, transparent 100%);
            clip-path: ellipse(100% 100% at 50% 100%);
        }
        
        .wave-accent {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 120px;
            background: linear-gradient(to top, #d4af37 0%, #f4d03f 30%, transparent 100%);
            clip-path: ellipse(95% 90% at 50% 100%);
        }
        
        /* Content Container */
        .content {
            position: relative;
            z-index: 5;
            padding: 60px 100px;
            text-align: center;
        }
        
        /* Header */
        .header {
            margin-bottom: 30px;
        }
        
        .title {
            font-size: 56px;
            font-weight: bold;
            color: #1a1a1a;
            letter-spacing: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .subtitle {
            font-size: 24px;
            color: #333;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 10px;
        }
        
        /* Medali Emas */
        .medal {
            width: 80px;
            height: 80px;
            margin: 20px auto;
            background: radial-gradient(circle, #f4d03f 0%, #d4af37 50%, #c9a227 100%);
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3), inset 0 2px 5px rgba(255,255,255,0.5);
            position: relative;
        }
        
        .medal::before {
            content: '★';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 40px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .medal-ribbon {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 20px;
            background: linear-gradient(to bottom, #c9302c 0%, #e74c3c 100%);
        }
        
        /* Diberikan Kepada */
        .given-to {
            font-size: 18px;
            color: #555;
            margin: 25px 0 15px 0;
            font-style: italic;
        }
        
        /* Nama Peserta */
        .participant-name {
            font-size: 42px;
            font-weight: bold;
            color: #1a1a1a;
            margin: 20px 0;
            padding-bottom: 10px;
            border-bottom: 3px solid #d4af37;
            display: inline-block;
            min-width: 400px;
            letter-spacing: 2px;
        }
        
        /* Deskripsi */
        .description {
            font-size: 15px;
            color: #444;
            line-height: 1.8;
            max-width: 700px;
            margin: 25px auto;
            text-align: center;
        }
        
        /* Signature Section */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 60px;
            position: relative;
            z-index: 10;
        }
        
        .signature {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 40px;
        }
        
        .signature-line {
            width: 200px;
            height: 2px;
            background: #333;
            margin: 50px auto 10px auto;
        }
        
        .signature-name {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 3px;
        }
        
        .signature-title {
            font-size: 13px;
            color: #666;
        }
        
        /* Serial Number */
        .serial {
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 11px;
            color: #999;
            z-index: 10;
        }
        
        .issued-date {
            position: absolute;
            bottom: 20px;
            left: 30px;
            font-size: 11px;
            color: #999;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <!-- Ribbon Kiri -->
        <div class="ribbon-left">
            <div class="ribbon-triangle"></div>
        </div>
        
        <!-- Ornamen Bawah -->
        <div class="wave-bottom"></div>
        <div class="wave-accent"></div>
        
        <!-- Content -->
        <div class="content">
            <!-- Header -->
            <div class="header">
                <div class="title">SERTIFIKAT</div>
                <div class="subtitle">PENGHARGAAN</div>
            </div>
            
            <!-- Medali -->
            <div class="medal">
                <div class="medal-ribbon"></div>
            </div>
            
            <!-- Diberikan Kepada -->
            <div class="given-to">Diberikan Kepada :</div>
            
            <!-- Nama Peserta -->
            <div class="participant-name">{{ strtoupper($name) }}</div>
            
            <!-- Deskripsi -->
            <div class="description">
                Yang telah berprestasi dan berhasil menyelesaikan pendidikan<br>
                sekolah menengah atas di <strong>{{ $event->title }}</strong> dengan nilai terbaik.<br>
                <em>Tanggal: {{ \Carbon\Carbon::parse($event->event_date)->isoFormat('D MMMM Y') }}</em>
            </div>
            
            <!-- Tanda Tangan -->
            <div class="signatures">
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">OLIVIA WILSON</div>
                    <div class="signature-title">Kepala Sekolah</div>
                </div>
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">KETUT SUSILO</div>
                    <div class="signature-title">Wakil Kepala Sekolah</div>
                </div>
            </div>
        </div>
        
        <!-- Serial & Date -->
        <div class="serial">No. Sertifikat: {{ $serial }}</div>
        <div class="issued-date">Diterbitkan: {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}</div>
    </div>
</body>
</html>
