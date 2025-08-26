# 🧪 **POSTMAN TESTING GUIDE - Laravel Event App API**

## 📥 **File yang Perlu Di-Import:**

1. **`Laravel_Event_App_API.postman_collection.json`** - Collection utama
2. **`Laravel_Event_App_Environment.postman_environment.json`** - Environment variables

## 🚀 **Cara Import ke Postman:**

1. **Buka Postman**
2. **Import Collection:**
   - Klik "Import" button
   - Drag & drop file `Laravel_Event_App_API.postman_collection.json`
   - Atau klik "Upload Files" dan pilih file

3. **Import Environment:**
   - Klik "Import" button
   - Drag & drop file `Laravel_Event_App_Environment.postman_environment.json`
   - Atau klik "Upload Files" dan pilih file

4. **Set Environment:**
   - Pilih environment "Laravel Event App Environment" dari dropdown

## 🔧 **Setup Sebelum Testing:**

### **1. Start Laravel Server:**
```bash
php artisan serve
```

### **2. Setup Database & Data:**
```bash
php artisan migrate:fresh
php artisan db:seed --class=TestDataSeeder
```

### **3. Start Queue Worker (untuk Certificate Generation):**
```bash
php artisan queue:work
```

## 📋 **Testing Flow Step by Step:**

### **Step 1: Authentication**
1. **Register User** → Dapatkan user ID
2. **Verify Email OTP** → Verifikasi email
3. **Login User** → Dapatkan TOKEN (akan auto-save ke environment)

### **Step 2: Event Operations**
1. **Get All Events** → Lihat events yang tersedia
2. **Get Event by ID** → Lihat detail event tertentu
3. **Register for Event** → Daftar ke event (perlu TOKEN)

### **Step 3: Attendance & Certificate**
1. **Submit Attendance** → Submit kehadiran (perlu TOKEN)
2. **Generate Certificate** → Generate PDF certificate (perlu TOKEN)
3. **Check Certificate Status** → Cek status generation
4. **Download Certificate** → Download PDF yang sudah jadi

### **Step 4: Admin Operations (Admin Role)**
1. **Create Event** → Buat event baru
2. **Update Event** → Update event yang ada
3. **Publish Event** → Publish event
4. **Delete Event** → Hapus event

### **Step 5: Reports & Export**
1. **Monthly Events Report** → Laporan bulanan events
2. **Monthly Attendees Report** → Laporan bulanan attendees
3. **Top 10 Events Report** → Top 10 events
4. **Export Event Participants** → Export CSV peserta event
5. **Export All Participants** → Export CSV semua peserta

## 🔑 **Environment Variables:**

| Variable | Value | Description |
|----------|-------|-------------|
| `BASE_URL` | `http://localhost:8000/api` | Base URL API |
| `TOKEN` | Auto-filled | JWT token dari login |
| `USER_ID` | Manual set | ID user yang sedang test |
| `EVENT_ID` | Manual set | ID event yang sedang test |
| `REGISTRATION_ID` | Manual set | ID registration |
| `CERTIFICATE_ID` | Manual set | ID certificate |

## 📝 **Test Data yang Dibuat:**

- **User:** test@example.com / password123
- **Event:** Test Event 2025
- **Registration:** Status confirmed
- **Attendance:** Status present

## ⚠️ **Penting untuk Diperhatikan:**

### **1. Token Management:**
- Login request akan auto-save TOKEN ke environment
- Semua request yang memerlukan auth akan menggunakan `{{TOKEN}}`

### **2. Queue Processing:**
- Certificate generation menggunakan Job Queue
- Pastikan `php artisan queue:work` berjalan
- Check status certificate untuk memastikan generation selesai

### **3. File Downloads:**
- Export CSV akan download file
- Certificate PDF akan download file
- Pastikan browser mengizinkan download

### **4. Error Handling:**
- 401: Unauthorized (token invalid/expired)
- 403: Forbidden (tidak punya permission)
- 404: Not Found (resource tidak ada)
- 422: Validation Error (data tidak valid)

## 🧪 **Testing Scenarios:**

### **Scenario 1: User Journey**
1. Register → Login → Browse Events → Register Event → Attend → Generate Certificate

### **Scenario 2: Admin Journey**
1. Login as Admin → Create Event → Manage Events → Generate Reports → Export Data

### **Scenario 3: Error Testing**
1. Invalid Token → Unauthorized Response
2. Invalid Data → Validation Error
3. Non-existent Resource → 404 Error

## 📊 **Expected Responses:**

### **Success Login:**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com"
    }
}
```

### **Certificate Generation Started:**
```json
{
    "message": "Certificate generation started",
    "status": "processing"
}
```

### **Export Success:**
- File download (CSV/PDF)
- Content-Type: text/csv atau application/pdf

## 🎯 **Tips Testing:**

1. **Test secara berurutan** sesuai flow yang sudah dibuat
2. **Gunakan environment variables** untuk ID yang dinamis
3. **Monitor console** untuk melihat auto-saved variables
4. **Check response status** dan body untuk setiap request
5. **Test error cases** untuk memastikan error handling berfungsi

## 🚨 **Troubleshooting:**

### **Token Expired:**
- Re-run login request
- Token akan auto-update

### **Queue Not Working:**
- Pastikan `php artisan queue:work` berjalan
- Check Laravel logs untuk error

### **File Not Found:**
- Pastikan storage link sudah dibuat: `php artisan storage:link`
- Check file permissions di storage directory

---

**Happy Testing! 🎉**
