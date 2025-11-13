# 🧪 TESTING MODE - VALIDASI DINONAKTIFKAN

## Status: TESTING MODE AKTIF ⚠️

Untuk memudahkan testing, beberapa validasi production telah dinonaktifkan sementara:

### 1. ✅ Validasi H-3 Event Creation (NONAKTIF)
**File:** `app/Http/Controllers/Api/EventController.php` (line 109-117)

**Yang Dinonaktifkan:**
- Admin bisa membuat event kapan saja (tidak perlu H-3)
- Bisa buat event untuk hari ini, besok, atau kemarin

**Untuk Testing:**
- Admin bisa langsung buat event dengan tanggal hari ini
- Tidak perlu tunggu 3 hari

---

### 2. ✅ Auto-Close Registration (NONAKTIF)
**File:** `app/Http/Controllers/Api/RegistrationController.php` (line 30-59)

**Yang Dinonaktifkan:**
- Pendaftaran tidak otomatis ditutup saat event dimulai
- User bisa daftar event yang sudah lewat/sedang berlangsung

**Untuk Testing:**
- User bisa daftar event kapan saja
- Bisa test attendance meskipun event sudah dimulai

---

### 3. ✅ Attendance Time Validation (NONAKTIF)
**File:** `app/Http/Controllers/Api/AttendanceController.php` (line 74-97)

**Yang Dinonaktifkan:**
- Validasi waktu absensi (harus hari event & setelah jam mulai)
- User bisa absen kapan saja

**Untuk Testing:**
- User bisa isi daftar hadir kapan saja
- Tidak perlu tunggu event dimulai
- Bisa test generate sertifikat langsung

---

## 🔄 CARA AKTIFKAN KEMBALI (SETELAH TESTING SELESAI)

### Step 1: Aktifkan Validasi H-3
Buka file: `app/Http/Controllers/Api/EventController.php`

Cari baris:
```php
// ===== TESTING MODE: H-3 VALIDATION DISABLED =====
```

**Uncomment** baris validasi:
```php
public function store(Request $r)
{
    // Aktifkan validasi H-3
    if (!$r->event_date || now()->diffInDays(Carbon::parse($r->event_date), false) < 3) {
        return response()->json(['message' => 'Event must be created at least H-3.'], 422);
    }
    
    // ... rest of code
}
```

### Step 2: Aktifkan Auto-Close Registration
Buka file: `app/Http/Controllers/Api/RegistrationController.php`

Cari baris:
```php
// ===== TESTING MODE: AUTO-CLOSE REGISTRATION DISABLED =====
```

**Uncomment** semua baris validasi di dalam blok tersebut.

### Step 3: Aktifkan Attendance Time Validation
Buka file: `app/Http/Controllers/Api/AttendanceController.php`

Cari baris:
```php
// ===== TESTING MODE: ATTENDANCE TIME VALIDATION DISABLED =====
```

**Uncomment** semua baris validasi waktu absensi di dalam blok tersebut.

### Step 4: Clear Cache
```bash
cd laravel-event-app
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Step 5: Restart Services
```bash
# Restart queue worker (jika running)
php artisan queue:restart

# Restart Laravel server (jika perlu)
# Ctrl+C lalu php artisan serve
```

---

## 📋 CHECKLIST TESTING

Sebelum aktifkan kembali validasi, pastikan sudah test:

- [ ] Admin bisa upload template sertifikat custom
- [ ] Admin bisa buat event dengan tanggal hari ini
- [ ] User bisa daftar event
- [ ] User bisa hadir (attendance)
- [ ] Sertifikat generate dengan template custom
- [ ] Nama user muncul di sertifikat
- [ ] Deskripsi event muncul di sertifikat
- [ ] Download sertifikat PDF berhasil
- [ ] Template custom overlay text dengan benar

---

## ⚠️ PENTING!

**JANGAN LUPA AKTIFKAN KEMBALI SETELAH TESTING!**

Validasi ini penting untuk production:
- H-3 mencegah event mendadak tanpa persiapan
- Auto-close registration mencegah user daftar event yang sudah lewat

---

## 📝 Catatan Testing

Tanggal Testing Dimulai: [ISI TANGGAL]
Tanggal Testing Selesai: [ISI TANGGAL]
Tested By: [ISI NAMA]

Status Validasi:
- [ ] H-3 Validation: SUDAH DIAKTIFKAN KEMBALI
- [ ] Auto-Close Registration: SUDAH DIAKTIFKAN KEMBALI

---

**File ini akan dihapus setelah testing selesai dan validasi diaktifkan kembali.**
