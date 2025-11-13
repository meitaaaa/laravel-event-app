-- SQL script to create test events for OTP testing
INSERT INTO events (
    title, 
    description, 
    event_date, 
    start_time, 
    end_time, 
    location, 
    price, 
    is_free, 
    is_published, 
    created_by, 
    registration_closes_at, 
    created_at, 
    updated_at
) VALUES 
(
    'Programming Competition 2025 - Testing OTP',
    'Event khusus untuk testing sistem OTP dan sertifikat. Kompetisi pemrograman dengan sistem absensi digital menggunakan token email. Daftar sekarang untuk mendapatkan token OTP via email!',
    CURDATE(),
    '08:00:00',
    '16:00:00',
    'Lab Computer SMKN 4 Bogor',
    0,
    1,
    1,
    1,
    DATE_ADD(NOW(), INTERVAL 1 DAY),
    NOW(),
    NOW()
),
(
    'Workshop Digital Marketing - OTP Test',
    'Pelatihan digital marketing dengan sistem sertifikat otomatis. Event untuk testing flow lengkap dari pendaftaran hingga download sertifikat. Sistem OTP email terintegrasi.',
    DATE_ADD(CURDATE(), INTERVAL 1 DAY),
    '09:00:00',
    '15:00:00',
    'Ruang Multimedia SMKN 4 Bogor',
    25000,
    0,
    1,
    1,
    DATE_ADD(NOW(), INTERVAL 2 DAY),
    NOW(),
    NOW()
),
(
    'Seminar Teknologi AI - Certificate Test',
    'Seminar tentang perkembangan AI dan machine learning. Cocok untuk testing sistem email OTP dan verifikasi kehadiran. Dapatkan sertifikat digital setelah absensi.',
    DATE_ADD(CURDATE(), INTERVAL 3 DAY),
    '13:00:00',
    '17:00:00',
    'Auditorium SMKN 4 Bogor',
    0,
    1,
    1,
    1,
    DATE_ADD(NOW(), INTERVAL 5 DAY),
    NOW(),
    NOW()
);

-- Create admin user if not exists
INSERT IGNORE INTO users (
    name, 
    email, 
    password, 
    role, 
    email_verified_at, 
    created_at, 
    updated_at
) VALUES (
    'Admin SMKN 4',
    'admin@smkn4bogor.sch.id',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'admin',
    NOW(),
    NOW(),
    NOW()
);
