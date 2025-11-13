# 🔐 MIDTRANS PAYMENT GATEWAY SETUP GUIDE

## 📋 Prerequisites

✅ Akun Midtrans sudah terdaftar  
✅ Server Key sudah didapat  
✅ Client Key sudah didapat  
✅ Merchant ID sudah didapat  

---

## 🚀 STEP-BY-STEP SETUP

### STEP 1: Tambahkan Credentials ke `.env`

Buka file `.env` (bukan `.env.example`) dan tambahkan/update baris berikut:

```env
# Midtrans Configuration
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

**⚠️ PENTING:**
- Ganti `SB-Mid-server-xxxxxxxxxxxxxxxx` dengan **Server Key** Anda
- Ganti `SB-Mid-client-xxxxxxxxxxxxxxxx` dengan **Client Key** Anda
- `MIDTRANS_IS_PRODUCTION=false` → Untuk **Sandbox/Testing**
- `MIDTRANS_IS_PRODUCTION=true` → Untuk **Production/Live**

**Contoh (Sandbox):**
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-abc123def456ghi789
MIDTRANS_CLIENT_KEY=SB-Mid-client-xyz987uvw654rst321
MIDTRANS_IS_PRODUCTION=false
```

---

### STEP 2: Clear Config Cache

Setelah update `.env`, jalankan command ini:

```bash
php artisan config:clear
php artisan cache:clear
```

---

### STEP 3: Verify Configuration

Test apakah credentials sudah terbaca dengan benar:

```bash
php artisan tinker
```

Kemudian di tinker, ketik:
```php
config('midtrans.server_key')
config('midtrans.client_key')
config('midtrans.is_production')
```

Output seharusnya menampilkan credentials Anda (bukan `null`).

Ketik `exit` untuk keluar dari tinker.

---

### STEP 4: Update Frontend Environment

Buka file `frontend-react.js/.env` dan tambahkan:

```env
REACT_APP_MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxx
```

**⚠️ PENTING:** Ganti dengan **Client Key** yang sama seperti di Laravel `.env`

---

### STEP 5: Restart Development Servers

**Backend (Laravel):**
```bash
# Jika menggunakan php artisan serve:
Ctrl + C (stop)
php artisan serve
```

**Frontend (React):**
```bash
# Di folder frontend-react.js:
Ctrl + C (stop)
npm start
```

---

## 🧪 TESTING PAYMENT

### Test dengan Sandbox

Midtrans Sandbox menyediakan test cards untuk testing:

#### Credit Card (Success)
```
Card Number: 4811 1111 1111 1114
CVV: 123
Exp Date: 01/25
OTP: 112233
```

#### Credit Card (Failure)
```
Card Number: 4911 1111 1111 1113
CVV: 123
Exp Date: 01/25
```

#### GoPay (Success)
```
Pilih GoPay → Akan muncul simulasi pembayaran
Klik "Success" untuk simulasi pembayaran berhasil
```

#### Virtual Account BCA (Success)
```
Pilih BCA Virtual Account
VA Number akan di-generate otomatis
Gunakan Midtrans Simulator untuk bayar
```

---

## 📊 FLOW PEMBAYARAN

### 1. User Flow
```
1. User klik "Beli Sekarang" di Event Detail
2. Redirect ke halaman Midtrans Snap
3. User pilih metode pembayaran (Credit Card/GoPay/VA/dll)
4. User selesaikan pembayaran
5. Midtrans kirim notifikasi ke backend
6. Backend update status payment
7. User redirect kembali ke aplikasi
```

### 2. Backend Flow
```
1. PaymentController::createPayment()
   - Create payment record
   - Generate Snap Token dari Midtrans
   - Return Snap Token ke frontend

2. PaymentController::handleNotification()
   - Terima webhook dari Midtrans
   - Verify signature
   - Update payment status
   - Trigger actions (kirim email, generate certificate, dll)
```

---

## 🔧 CONFIGURATION FILES

### 1. Laravel Config: `config/midtrans.php`
```php
<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];
```

### 2. Environment Variables
```env
# .env (Laravel)
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

# .env (React)
REACT_APP_MIDTRANS_CLIENT_KEY=your_client_key
```

---

## 🔐 SECURITY BEST PRACTICES

### ✅ DO's
- ✅ Simpan Server Key di `.env` (backend only)
- ✅ Gunakan Client Key di frontend
- ✅ Verify signature di webhook handler
- ✅ Gunakan HTTPS di production
- ✅ Set `MIDTRANS_IS_3DS=true` untuk keamanan ekstra

### ❌ DON'Ts
- ❌ JANGAN commit `.env` ke Git
- ❌ JANGAN expose Server Key di frontend
- ❌ JANGAN skip signature verification
- ❌ JANGAN gunakan production key untuk testing

---

## 🌐 WEBHOOK CONFIGURATION

### Setup di Midtrans Dashboard

1. Login ke [Midtrans Dashboard](https://dashboard.midtrans.com)
2. Pilih environment (Sandbox/Production)
3. Settings → Configuration
4. **Payment Notification URL:**
   ```
   https://yourdomain.com/api/midtrans/notification
   ```
   
   Untuk local testing:
   ```
   http://127.0.0.1:8000/api/midtrans/notification
   ```

5. **Finish Redirect URL:**
   ```
   https://yourdomain.com/payment/success
   ```

6. **Unfinish Redirect URL:**
   ```
   https://yourdomain.com/payment/pending
   ```

7. **Error Redirect URL:**
   ```
   https://yourdomain.com/payment/failed
   ```

---

## 🧪 LOCAL TESTING WITH NGROK

Untuk test webhook di local development:

### 1. Install ngrok
Download dari: https://ngrok.com/download

### 2. Run ngrok
```bash
ngrok http 8000
```

### 3. Copy HTTPS URL
```
Forwarding: https://abc123.ngrok.io -> http://localhost:8000
```

### 4. Update Midtrans Webhook URL
```
https://abc123.ngrok.io/api/midtrans/notification
```

---

## 📝 PAYMENT STATUS

### Midtrans Transaction Status
```
pending     → Menunggu pembayaran
settlement  → Pembayaran berhasil (sukses)
capture     → Pembayaran berhasil (credit card)
deny        → Pembayaran ditolak
cancel      → Pembayaran dibatalkan
expire      → Pembayaran kadaluarsa
failure     → Pembayaran gagal
```

### Mapping ke Database
```php
'pending'    → status: 'pending'
'settlement' → status: 'paid'
'capture'    → status: 'paid'
'deny'       → status: 'failed'
'cancel'     → status: 'cancelled'
'expire'     → status: 'expired'
'failure'    → status: 'failed'
```

---

## 🐛 TROUBLESHOOTING

### Problem 1: "Server Key is not set"
**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
```
Pastikan `.env` sudah benar.

### Problem 2: "Snap Token null"
**Solution:**
- Check Server Key di `.env`
- Check internet connection
- Check Midtrans API status
- Check log: `storage/logs/laravel.log`

### Problem 3: Webhook tidak terima notifikasi
**Solution:**
- Pastikan webhook URL sudah di-set di Midtrans Dashboard
- Untuk local: gunakan ngrok
- Check firewall/security settings
- Check log: `storage/logs/laravel.log`

### Problem 4: Payment status tidak update
**Solution:**
- Check webhook handler: `PaymentController::handleNotification()`
- Verify signature verification
- Check database connection
- Check log untuk error

---

## 📊 MONITORING & LOGS

### Check Payment Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Filter payment logs
grep "Payment" storage/logs/laravel.log
grep "Midtrans" storage/logs/laravel.log
```

### Check Database
```sql
-- Check payments
SELECT * FROM payments ORDER BY created_at DESC LIMIT 10;

-- Check payment by status
SELECT status, COUNT(*) as total FROM payments GROUP BY status;

-- Check recent transactions
SELECT p.*, r.name, e.title 
FROM payments p
JOIN registrations r ON p.registration_id = r.id
JOIN events e ON r.event_id = e.id
ORDER BY p.created_at DESC
LIMIT 10;
```

---

## 🎯 QUICK START CHECKLIST

- [ ] Copy Server Key dari Midtrans Dashboard
- [ ] Copy Client Key dari Midtrans Dashboard
- [ ] Update `laravel-event-app/.env` dengan credentials
- [ ] Update `frontend-react.js/.env` dengan Client Key
- [ ] Run `php artisan config:clear`
- [ ] Run `php artisan cache:clear`
- [ ] Restart Laravel server
- [ ] Restart React server
- [ ] Test payment dengan Sandbox test card
- [ ] Verify payment status di database
- [ ] Check webhook notification di logs

---

## 📚 RESOURCES

- **Midtrans Documentation:** https://docs.midtrans.com
- **Midtrans Dashboard (Sandbox):** https://dashboard.sandbox.midtrans.com
- **Midtrans Dashboard (Production):** https://dashboard.midtrans.com
- **Test Cards:** https://docs.midtrans.com/en/technical-reference/sandbox-test
- **API Reference:** https://api-docs.midtrans.com

---

## ✅ PRODUCTION CHECKLIST

Sebelum go live:

- [ ] Ganti ke Production credentials
- [ ] Set `MIDTRANS_IS_PRODUCTION=true`
- [ ] Update webhook URL ke domain production
- [ ] Test semua payment methods
- [ ] Setup monitoring & alerts
- [ ] Backup database
- [ ] Test error scenarios
- [ ] Document payment flow
- [ ] Train customer support team

---

**Last Updated:** 6 November 2025  
**Status:** Ready for Integration  
**Environment:** Sandbox (Testing)
