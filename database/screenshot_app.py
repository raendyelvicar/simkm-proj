#!/usr/bin/env python3
"""
Drives the running app (php -S localhost:8000 -t public) with headless
Chromium and screenshots every major feature, for all three roles (admin,
counselor, student) plus the public pages. Also performs one real end-to-end
booking -> confirm -> chat -> cancel -> approve walkthrough so the screenshots
show actual data, not just empty states.

Requires: pip3 install playwright && python3 -m playwright install chromium
Requires: the app running at BASE_URL with working DB credentials.

Usage: python3 database/screenshot_app.py
"""
import os
import re
import sys
import time

from playwright.sync_api import sync_playwright

BASE_URL = 'http://localhost:8000'
OUT_ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'screenshots')
PASSWORD = 'Screenshot123!'

os.makedirs(OUT_ROOT, exist_ok=True)


def shot(page, category, name):
    d = os.path.join(OUT_ROOT, category)
    os.makedirs(d, exist_ok=True)
    path = os.path.join(d, f'{name}.png')
    page.screenshot(path=path, full_page=True)
    print(f'  [{category}] {name}.png')


def goto(page, path):
    page.goto(BASE_URL + path, wait_until='networkidle')
    time.sleep(0.3)


def login(page, username, password):
    goto(page, '/login')
    page.fill('#username', username)
    page.fill('#password', password)
    page.click('#btnLogin')
    page.wait_for_load_state('networkidle')
    time.sleep(0.3)


def public_pages(browser):
    print('== Public ==')
    ctx = browser.new_context(viewport={'width': 1440, 'height': 900})
    page = ctx.new_page()

    goto(page, '/')
    shot(page, 'public', '01_beranda')

    goto(page, '/login')
    shot(page, 'public', '02_login')

    goto(page, '/register')
    shot(page, 'public', '03_register')

    goto(page, '/forgot-password')
    shot(page, 'public', '04_lupa_password')

    ctx.close()


def student_flow(browser, counselor_user_id, article_id):
    print('== Mahasiswa (student001) ==')
    ctx = browser.new_context(viewport={'width': 1440, 'height': 900})
    page = ctx.new_page()
    login(page, 'student001', PASSWORD)

    shot(page, 'student', '01_dashboard')

    goto(page, '/diary')
    shot(page, 'student', '02_diary_list')

    goto(page, '/diary/create')
    shot(page, 'student', '03_diary_create_kosong')

    # Isi & kirim diary supaya ada data nyata untuk halaman detail.
    page.fill('#situation', 'Presentasi tugas akhir ditunda mendadak, membuat saya kecewa dan cemas.')
    page.fill('#initial_thoughts', 'Saya merasa semua usaha saya sia-sia.')
    # Kotak centang & radio custom di-skin lewat <label>, bukan input mentah —
    # klik label-nya (perilaku label-for-input standar), bukan force-click ke
    # input yang divisualkan tersembunyi (state-nya tidak akan berubah). Ada
    # dua grup checkbox di form ini (emosi & reaksi fisik) — keduanya wajib
    # diisi minimal satu, jadi klik pilihan pertama di tiap grup.
    checkbox_groups = page.locator('.diary-checkbox-group')
    for i in range(checkbox_groups.count()):
        first_label = checkbox_groups.nth(i).locator('label.diary-checkbox-pill').first
        if first_label.count() > 0:
            first_label.click()
    page.locator('.diary-intensity-scale label:has(input[value="3"])').click()
    page.fill('#behavior', 'Menghindari teman-teman dan memilih diam di kamar seharian.')
    page.fill('#self_reflection', 'Mungkin lain kali saya perlu bicara dulu dengan dosen pembimbing.')
    page.click('button:has-text("Simpan Diary")')
    page.wait_for_load_state('networkidle')
    shot(page, 'student', '04_diary_list_terisi')

    # Buka detail diary yang baru dibuat (baris pertama di tabel).
    first_link = page.locator('a:has-text("Lihat")').first
    if first_link.count() > 0:
        first_link.click()
        page.wait_for_load_state('networkidle')
        shot(page, 'student', '05_diary_detail')

    goto(page, '/assessment')
    shot(page, 'student', '06_self_assessment')

    goto(page, '/assessment/history')
    shot(page, 'student', '07_assessment_riwayat')

    goto(page, '/assessment/start')
    shot(page, 'student', '08_assessment_mulai')

    goto(page, '/self-help')
    shot(page, 'student', '09_self_help')

    goto(page, '/self-help/breathing')
    shot(page, 'student', '10_self_help_pernapasan')

    goto(page, '/self-help/gratitude')
    shot(page, 'student', '11_self_help_gratitude')

    goto(page, '/self-help/pfa')
    shot(page, 'student', '12_self_help_pfa')

    goto(page, '/self-help/activities')
    shot(page, 'student', '13_self_help_activities')

    goto(page, '/counselor')
    shot(page, 'student', '14_daftar_konselor')

    goto(page, f'/counselor/{counselor_user_id}')
    shot(page, 'student', '15_profil_konselor')

    goto(page, '/bookings')
    shot(page, 'student', '16_booking_list_kosong')

    goto(page, f'/bookings/create/{counselor_user_id}')
    shot(page, 'student', '17_booking_ajukan')

    select = page.locator('select[name="schedule_id"]')
    options = select.locator('option')
    picked = False
    for i in range(options.count()):
        val = options.nth(i).get_attribute('value')
        if val:
            select.select_option(val)
            picked = True
            break
    if picked:
        page.fill('#complaint', 'Merasa cemas berlebihan menjelang sidang skripsi.')
        page.click('button:has-text("Ajukan Booking")')
        page.wait_for_load_state('networkidle')

    shot(page, 'student', '18_booking_list_pending')

    goto(page, '/article')
    shot(page, 'student', '19_artikel_list')

    goto(page, f'/article/{article_id}')
    shot(page, 'student', '20_artikel_detail')

    goto(page, '/profile')
    shot(page, 'student', '21_profil_saya')

    goto(page, '/laporan')
    shot(page, 'student', '22_laporan')

    ctx.close()


def counselor_flow_confirm(browser):
    print('== Konselor (konselor01) — konfirmasi booking ==')
    ctx = browser.new_context(viewport={'width': 1440, 'height': 900})
    page = ctx.new_page()
    login(page, 'konselor01', PASSWORD)

    shot(page, 'counselor', '01_dashboard')

    goto(page, '/consultations')
    shot(page, 'counselor', '02_konsultasi_masuk_kosong')

    goto(page, '/booking-requests')
    shot(page, 'counselor', '03_permintaan_booking_pending')

    confirm_btn = page.locator('button:has-text("Konfirmasi")').first
    if confirm_btn.count() > 0:
        confirm_btn.click()
        page.wait_for_load_state('networkidle')
    shot(page, 'counselor', '04_permintaan_booking_terkonfirmasi')

    goto(page, '/schedule')
    shot(page, 'counselor', '05_jadwal_konsultasi')

    goto(page, '/shared-diaries')
    shot(page, 'counselor', '06_diary_dibagikan')

    goto(page, '/tips')
    shot(page, 'counselor', '07_tips_harian')

    goto(page, '/tips/create')
    shot(page, 'counselor', '08_tips_tambah')

    goto(page, '/article/create')
    shot(page, 'counselor', '09_artikel_tulis')

    goto(page, '/profile')
    shot(page, 'counselor', '10_profil_saya')

    goto(page, '/laporan')
    shot(page, 'counselor', '11_laporan')

    ctx.close()


def student_chat_flow(browser, counselor_user_id):
    print('== Mahasiswa — chat & pembatalan ==')
    ctx = browser.new_context(viewport={'width': 1440, 'height': 900})
    page = ctx.new_page()
    login(page, 'student001', PASSWORD)

    goto(page, '/bookings')
    shot(page, 'student', '23_booking_list_terkonfirmasi')

    chat_link = page.locator('a:has-text("Chat")').first
    if chat_link.count() > 0:
        chat_link.click()
        page.wait_for_load_state('networkidle')
        page.fill('input[name="message"]', 'Selamat siang, Dok. Saya ingin konsultasi terkait kecemasan menjelang sidang.')
        page.click('button:has-text("Kirim")')
        page.wait_for_load_state('networkidle')
        shot(page, 'student', '24_chat_konselor')

    # Ajukan pembatalan booking (butuh persetujuan admin).
    goto(page, '/bookings')
    cancel_btn = page.locator('button:has-text("Batal")').first
    if cancel_btn.count() > 0:
        cancel_btn.click()
        page.wait_for_timeout(400)
        shot(page, 'student', '25_modal_batalkan_booking')
        reason_box = page.locator('textarea[name="reason"]')
        if reason_box.count() > 0:
            reason_box.fill('Jadwal bentrok dengan ujian susulan.')
        page.click('button:has-text("Kirim Permintaan Pembatalan")')
        page.wait_for_load_state('networkidle')
    shot(page, 'student', '26_booking_menunggu_persetujuan_pembatalan')

    ctx.close()


def counselor_inbox_after_chat(browser):
    print('== Konselor — inbox setelah chat ==')
    ctx = browser.new_context(viewport={'width': 1440, 'height': 900})
    page = ctx.new_page()
    login(page, 'konselor01', PASSWORD)

    goto(page, '/consultations')
    shot(page, 'counselor', '12_konsultasi_masuk_terisi')

    thread_link = page.locator('.thread-row').first
    if thread_link.count() > 0:
        thread_link.click()
        page.wait_for_load_state('networkidle')
        shot(page, 'counselor', '13_konsultasi_thread')

    ctx.close()


def admin_flow(browser):
    print('== Admin (admin01) ==')
    ctx = browser.new_context(viewport={'width': 1440, 'height': 900})
    page = ctx.new_page()
    login(page, 'admin01', PASSWORD)

    shot(page, 'admin', '01_dashboard')

    goto(page, '/students')
    shot(page, 'admin', '02_data_mahasiswa')

    goto(page, '/admin/counselors')
    shot(page, 'admin', '03_kelola_konselor')

    goto(page, '/admin/counselors/create')
    shot(page, 'admin', '04_tambah_konselor')

    goto(page, '/admin/approvals')
    shot(page, 'admin', '05_persetujuan_akun')

    goto(page, '/admin/booking-cancellations')
    shot(page, 'admin', '06_persetujuan_pembatalan_booking_pending')

    approve_btn = page.locator('button:has-text("Setujui")').first
    if approve_btn.count() > 0:
        page.once('dialog', lambda d: d.accept())
        approve_btn.click()
        page.wait_for_load_state('networkidle')
    shot(page, 'admin', '07_persetujuan_pembatalan_booking_disetujui')

    goto(page, '/admin/settings')
    shot(page, 'admin', '08_pengaturan_sistem')

    goto(page, '/laporan')
    shot(page, 'admin', '09_laporan_hub')

    goto(page, '/laporan/self-assessment')
    shot(page, 'admin', '10_laporan_self_assessment')

    goto(page, '/laporan/konseling')
    shot(page, 'admin', '11_laporan_konseling')

    goto(page, '/laporan/mood-analysis')
    shot(page, 'admin', '12_laporan_mood_analysis')

    goto(page, '/laporan/risk-mapping')
    shot(page, 'admin', '13_laporan_risk_mapping')

    goto(page, '/profile')
    shot(page, 'admin', '14_profil_saya')

    ctx.close()


def student_final_status(browser):
    print('== Mahasiswa — status booking akhir ==')
    ctx = browser.new_context(viewport={'width': 1440, 'height': 900})
    page = ctx.new_page()
    login(page, 'student001', PASSWORD)
    goto(page, '/bookings')
    shot(page, 'student', '27_booking_list_dibatalkan')
    ctx.close()


def main():
    with sync_playwright() as p:
        browser = p.chromium.launch()

        public_pages(browser)

        # counselor_id 4 = user_id 41 (Dr. Andi Prakoso) — has an active future
        # schedule slot inserted for this walkthrough.
        counselor_user_id = 41
        article_id = 13

        student_flow(browser, counselor_user_id, article_id)
        counselor_flow_confirm(browser)
        student_chat_flow(browser, counselor_user_id)
        counselor_inbox_after_chat(browser)
        admin_flow(browser)
        student_final_status(browser)

        browser.close()

    print(f'\nDone. Screenshots in {OUT_ROOT}')


if __name__ == '__main__':
    main()
