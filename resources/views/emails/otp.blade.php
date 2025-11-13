<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'verification' ? 'Verifikasi Email' : 'Reset Password' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            padding: 40px 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .email-header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .email-header p {
            color: #e0e7ff;
            font-size: 14px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .message {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .otp-container {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px dashed #3b82f6;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #1e40af;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .otp-validity {
            font-size: 13px;
            color: #64748b;
            margin-top: 15px;
        }
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            border-radius: 6px;
            margin: 25px 0;
        }
        .warning-box p {
            font-size: 14px;
            color: #92400e;
            margin: 0;
        }
        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            border-radius: 6px;
            margin: 25px 0;
        }
        .info-box p {
            font-size: 14px;
            color: #1e40af;
            margin: 0;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            font-size: 12px;
            color: #6b7280;
            margin: 5px 0;
        }
        .email-footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            .email-header {
                padding: 30px 20px;
            }
            .otp-code {
                font-size: 36px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>🎓 EduFest</h1>
            <p>{{ $type === 'verification' ? 'Verifikasi Email Anda' : 'Reset Password' }}</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Halo, {{ $user->name }}! 👋
            </div>

            <div class="message">
                @if($type === 'verification')
                    Terima kasih telah mendaftar di <strong>EduFest</strong>! Untuk menyelesaikan proses pendaftaran, 
                    silakan verifikasi alamat email Anda dengan menggunakan kode OTP di bawah ini.
                @else
                    Kami menerima permintaan untuk mereset password akun EduFest Anda. 
                    Gunakan kode OTP di bawah ini untuk melanjutkan proses reset password.
                @endif
            </div>

            <!-- OTP Code -->
            <div class="otp-container">
                <div class="otp-label">Kode OTP Anda</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-validity">
                    ⏰ Berlaku selama <strong>10 menit</strong>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <p>
                    <strong>⚠️ Peringatan Keamanan:</strong><br>
                    Jangan bagikan kode OTP ini kepada siapa pun, termasuk tim EduFest. 
                    Kami tidak akan pernah meminta kode OTP Anda melalui telepon atau email.
                </p>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <p>
                    <strong>💡 Tips:</strong><br>
                    @if($type === 'verification')
                        Masukkan kode OTP di halaman verifikasi email untuk mengaktifkan akun Anda. 
                        Jika Anda tidak melakukan pendaftaran, abaikan email ini.
                    @else
                        Masukkan kode OTP di halaman reset password untuk membuat password baru. 
                        Jika Anda tidak meminta reset password, abaikan email ini.
                    @endif
                </p>
            </div>

            <div class="divider"></div>

            <div style="font-size: 14px; color: #6b7280; text-align: center;">
                <p>Jika kode OTP tidak berfungsi atau sudah kedaluwarsa, Anda dapat meminta kode baru melalui aplikasi.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>EduFest</strong> - Platform Event Management</p>
            <p>Email ini dikirim secara otomatis, mohon jangan membalas email ini.</p>
            <p style="margin-top: 15px;">
                <a href="#">Kunjungi Website</a> | 
                <a href="#">Bantuan</a> | 
                <a href="#">Kebijakan Privasi</a>
            </p>
            <p style="margin-top: 10px; font-size: 11px; color: #9ca3af;">
                © {{ date('Y') }} EduFest. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

