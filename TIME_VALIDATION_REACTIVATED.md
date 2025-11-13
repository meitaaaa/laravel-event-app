# ✅ VALIDASI WAKTU DIAKTIFKAN KEMBALI

## 📋 Overview

Semua validasi waktu yang sebelumnya dinonaktifkan untuk testing sudah **DIAKTIFKAN KEMBALI**.

**Tanggal:** 6 November 2025  
**Status:** ✅ ACTIVE

---

## 🔒 VALIDASI YANG SUDAH DIAKTIFKAN

### 1. **H-3 Validation untuk Admin** ✅

**File:** `app/Http/Controllers/Api/EventController.php`

**Aturan:**
- Admin **HARUS** membuat event minimal **3 hari sebelum** tanggal event
- Validasi dilakukan saat create event

**Code:**
```php
// Validasi H-3: Event harus dibuat minimal 3 hari sebelum tanggal event
if (!$r->event_date || now()->diffInDays(Carbon::parse($r->event_date), false) < 3) {
    return response()->json([
        'message' => 'Event harus dibuat minimal H-3 (3 hari sebelum tanggal event).'
    ], 422);
}
```

**Contoh:**
- Hari ini: 6 November 2025
- Event date minimal: 9 November 2025 (H-3)
- Jika admin coba buat event tanggal 7 atau 8 November → ❌ DITOLAK

---

### 2. **Auto-Close Registration** ✅

**File:** `app/Http/Controllers/Api/RegistrationController.php`

**Aturan:**
- Pendaftaran **OTOMATIS DITUTUP** saat event dimulai
- User tidak bisa daftar jika event sudah mulai atau lewat

**Code:**
```php
// Validasi: Pendaftaran otomatis ditutup saat event dimulai
try {
  if (strpos($event->event_date, ' ') !== false) {
    $eventDateTime = Carbon::parse($event->event_date);
  } else {
    $eventDateTime = Carbon::parse($event->event_date . ' ' . ($event->start_time ?? '00:00:00'));
  }
} catch (\Exception $dtErr) {
  \Log::warning('Failed to parse event start time for registration');
  $eventDateTime = now()->addDay();
}

if(now()->greaterThanOrEqualTo($eventDateTime)){
  return response()->json([
    'message' => 'Pendaftaran sudah ditutup. Event telah dimulai atau sudah berlalu.',
    'registration_closed' => true
  ], 403);
}
```

**Contoh:**
- Event: 6 November 2025, 10:00 WIB
- Waktu sekarang: 6 November 2025, 10:01 WIB
- User coba daftar → ❌ DITOLAK (event sudah dimulai)

---

### 3. **Attendance Time Validation** ✅

**File:** `app/Http/Controllers/Api/AttendanceController.php`

**Aturan:**
- Absensi **HANYA BISA** dilakukan pada **hari event**
- Absensi **HANYA BISA** dilakukan **setelah jam mulai event**

**Code:**
```php
// Validasi: Absensi hanya dapat dilakukan pada hari event dan setelah jam mulai
$now = Carbon::now();
$eventDate = Carbon::parse($event->event_date);
$eventStartTime = Carbon::parse($event->event_date . ' ' . $event->start_time);

$isEventDay = $now->toDateString() === $eventDate->toDateString();
$isAfterStartTime = $now->greaterThanOrEqualTo($eventStartTime);

if (!$isEventDay) {
    return response()->json([
        'message' => 'Absensi hanya dapat dilakukan pada hari kegiatan (' . $eventDate->format('d/m/Y') . ')'
    ], 422);
}

if (!$isAfterStartTime) {
    return response()->json([
        'message' => 'Absensi belum dapat dilakukan. Silakan tunggu hingga jam ' . Carbon::parse($event->start_time)->format('H:i')
    ], 422);
}
```

**Contoh 1 - Absen di hari yang salah:**
- Event: 10 November 2025, 09:00 WIB
- Waktu sekarang: 9 November 2025, 10:00 WIB
- User coba absen → ❌ DITOLAK (bukan hari event)

**Contoh 2 - Absen terlalu cepat:**
- Event: 10 November 2025, 09:00 WIB
- Waktu sekarang: 10 November 2025, 08:30 WIB
- User coba absen → ❌ DITOLAK (event belum mulai)

**Contoh 3 - Absen di waktu yang benar:**
- Event: 10 November 2025, 09:00 WIB
- Waktu sekarang: 10 November 2025, 09:15 WIB
- User coba absen → ✅ BERHASIL

---

## 📊 SUMMARY ATURAN

### Admin (Create Event)
| Kondisi | Minimal | Maksimal | Status |
|---------|---------|----------|--------|
| Event date | H-3 (3 hari sebelum) | - | ✅ ACTIVE |

### User (Registration)
| Kondisi | Kapan Bisa Daftar | Status |
|---------|-------------------|--------|
| Registration | Sebelum event dimulai | ✅ ACTIVE |
| Registration | Setelah event dimulai | ❌ DITOLAK |

### User (Attendance)
| Kondisi | Kapan Bisa Absen | Status |
|---------|------------------|--------|
| Hari event | Hanya pada hari event | ✅ ACTIVE |
| Jam event | Setelah jam mulai event | ✅ ACTIVE |
| Sebelum event | Tidak bisa | ❌ DITOLAK |
| Setelah event | Bisa (tidak ada batas akhir) | ✅ ALLOWED |

---

## 🧪 TESTING SCENARIOS

### Scenario 1: Admin Create Event (H-3)
```
✅ PASS: Event date = today + 3 days
✅ PASS: Event date = today + 7 days
❌ FAIL: Event date = today + 2 days (kurang dari H-3)
❌ FAIL: Event date = today + 1 day
❌ FAIL: Event date = today
```

### Scenario 2: User Registration
```
Event: 10 Nov 2025, 09:00 WIB

✅ PASS: Register on 9 Nov 2025, 23:59 WIB (sebelum event)
❌ FAIL: Register on 10 Nov 2025, 09:00 WIB (event sudah mulai)
❌ FAIL: Register on 10 Nov 2025, 10:00 WIB (event sudah mulai)
```

### Scenario 3: User Attendance
```
Event: 10 Nov 2025, 09:00 WIB

❌ FAIL: Absen on 9 Nov 2025, 10:00 WIB (bukan hari event)
❌ FAIL: Absen on 10 Nov 2025, 08:30 WIB (belum jam mulai)
✅ PASS: Absen on 10 Nov 2025, 09:00 WIB (tepat jam mulai)
✅ PASS: Absen on 10 Nov 2025, 10:00 WIB (setelah jam mulai)
✅ PASS: Absen on 10 Nov 2025, 15:00 WIB (masih hari event)
```

---

## 🔄 COMPARISON: BEFORE vs AFTER

### BEFORE (Testing Mode)
```
❌ Admin bisa buat event H-1, H-2 (untuk testing)
❌ User bisa daftar setelah event dimulai (untuk testing)
❌ User bisa absen kapan saja (untuk testing sertifikat)
```

### AFTER (Production Mode)
```
✅ Admin HARUS buat event minimal H-3
✅ User TIDAK BISA daftar setelah event dimulai
✅ User HANYA BISA absen pada hari event dan setelah jam mulai
```

---

## 📝 FILES MODIFIED

1. **app/Http/Controllers/Api/EventController.php**
   - Line 109-112: H-3 validation activated

2. **app/Http/Controllers/Api/RegistrationController.php**
   - Line 30-54: Auto-close registration activated

3. **app/Http/Controllers/Api/AttendanceController.php**
   - Line 94-112: Attendance time validation activated

---

## ⚠️ IMPORTANT NOTES

### Untuk Testing Selanjutnya

Jika perlu testing lagi dengan event yang dekat, ada 2 opsi:

**Opsi 1: Ubah tanggal event di database**
```sql
UPDATE events SET event_date = '2025-11-10' WHERE id = 1;
```

**Opsi 2: Temporary disable validation**
Comment kembali validasi yang diperlukan (tidak recommended untuk production)

### Error Messages

User akan menerima pesan error yang jelas:

1. **Admin create event H-2:**
   ```
   "Event harus dibuat minimal H-3 (3 hari sebelum tanggal event)."
   ```

2. **User daftar setelah event mulai:**
   ```
   "Pendaftaran sudah ditutup. Event telah dimulai atau sudah berlalu."
   ```

3. **User absen di hari yang salah:**
   ```
   "Absensi hanya dapat dilakukan pada hari kegiatan (10/11/2025)"
   ```

4. **User absen sebelum jam mulai:**
   ```
   "Absensi belum dapat dilakukan. Silakan tunggu hingga jam 09:00"
   ```

---

## ✅ STATUS FINAL

**SEMUA VALIDASI WAKTU SUDAH AKTIF!**

- ✅ H-3 validation untuk admin
- ✅ Auto-close registration saat event dimulai
- ✅ Attendance time validation (hari event + setelah jam mulai)

**Production Ready:** YES  
**Testing Mode:** OFF  
**Last Updated:** 6 November 2025, 10:58 WIB
