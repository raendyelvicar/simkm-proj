"""
Pengelompokan tabel basis data per modul aplikasi — dipakai bersama oleh
generate_db_doc.py (dokumentasi .docx) dan generate_erd.py (diagram ERD per
modul), supaya kedua deliverable selalu konsisten satu sama lain.
"""

MODULES = [
    ('1. Akun & Autentikasi', ['users', 'password_reset_tokens']),
    ('2. Data Referensi Akademik', ['faculties', 'majors']),
    ('3. Konselor & Konsultasi', [
        'counselors', 'counselor_schedules', 'counseling_bookings',
        'booking_cancellation_requests', 'monitoring_periods',
        'counseling_sessions', 'chat_messages',
    ]),
    ('4. Diary Terstruktur', ['diary_entries']),
    ('5. Self-Assessment (BDI-II & PWB)', [
        'assessment_questions', 'assessment_choices', 'assessment_sessions',
        'assessment_session_answers', 'assessment_submissions',
        'assessment_answers', 'assessment_retake_grants',
    ]),
    ('6. Self Help', ['self_help_activities']),
    ('7. Konten & Informasi', ['articles', 'daily_tips']),
    ('8. Pengaturan Aplikasi', ['app_settings']),
    ('9. Tabel Warisan / Belum Digunakan Aktif', [
        'students', 'system_settings', 'questions', 'answers',
        'assessment_results', 'counselor_ratings', 'counselor_certifications',
        'diary_responses', 'notifications', 'chat_logs', 'login_logs',
    ]),
    ('10. Tabel Infrastruktur', ['schema_migrations']),
]
