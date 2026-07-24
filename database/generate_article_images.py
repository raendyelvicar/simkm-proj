#!/usr/bin/env python3
"""
Generates 15 cover images (1200x675, 16:9 to match .article-card-thumb) for the
psychology-article dummy data — a gradient card with a category badge, a large
emoji icon in a white circle, and the article title. No external downloads, so
no licensing/network concerns for seed data.

Usage: python3 database/generate_article_images.py
Writes into public/uploads/articles/psych_XX.jpg
"""
import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT_DIR = os.path.join(ROOT, 'public', 'uploads', 'articles')
os.makedirs(OUT_DIR, exist_ok=True)

W, H = 1200, 675

FONT_BOLD = '/System/Library/Fonts/Supplemental/Arial Bold.ttf'
FONT_REG = '/System/Library/Fonts/Supplemental/Arial.ttf'
EMOJI_FONT = '/System/Library/Fonts/Apple Color Emoji.ttc'
EMOJI_SIZE = 160

ARTICLES = [
    {'slug': 'psych_01', 'emoji': '😟', 'category': 'Kecemasan', 'title': 'Mengenal Gejala Kecemasan pada Mahasiswa', 'colors': ((37, 99, 235), (14, 165, 164))},
    {'slug': 'psych_02', 'emoji': '😫', 'category': 'Stres Akademik', 'title': 'Cara Mengatasi Stres Akademik Secara Efektif', 'colors': ((234, 88, 12), (250, 204, 21))},
    {'slug': 'psych_03', 'emoji': '📝', 'category': 'Self-Assessment', 'title': 'Pentingnya Self-Assessment bagi Kesehatan Mental', 'colors': ((13, 148, 136), (45, 212, 191))},
    {'slug': 'psych_04', 'emoji': '🔥', 'category': 'Burnout', 'title': 'Burnout Akademik: Kenali Sebelum Terlambat', 'colors': ((190, 24, 93), (251, 113, 133))},
    {'slug': 'psych_05', 'emoji': '🧘', 'category': 'Relaksasi', 'title': 'Teknik Relaksasi Sederhana untuk Menenangkan Pikiran', 'colors': ((5, 150, 105), (110, 231, 183))},
    {'slug': 'psych_06', 'emoji': '🌧️', 'category': 'Depresi', 'title': 'Memahami Depresi: Gejala, Penyebab, dan Cara Mengatasinya', 'colors': ((51, 65, 85), (100, 116, 139))},
    {'slug': 'psych_07', 'emoji': '⏰', 'category': 'Manajemen Waktu', 'title': 'Manajemen Waktu untuk Mengurangi Tekanan Kuliah', 'colors': ((202, 138, 4), (250, 204, 21))},
    {'slug': 'psych_08', 'emoji': '😴', 'category': 'Tidur & Kesehatan', 'title': 'Pentingnya Tidur Berkualitas bagi Kesehatan Mental', 'colors': ((67, 56, 202), (129, 140, 248))},
    {'slug': 'psych_09', 'emoji': '🌱', 'category': 'Resiliensi', 'title': 'Membangun Resiliensi Mental di Masa Perkuliahan', 'colors': ((21, 128, 61), (132, 204, 22))},
    {'slug': 'psych_10', 'emoji': '📱', 'category': 'Media Sosial', 'title': 'Dampak Media Sosial terhadap Kesehatan Mental Mahasiswa', 'colors': ((124, 58, 237), (232, 121, 249))},
    {'slug': 'psych_11', 'emoji': '🤝', 'category': 'Dukungan Sosial', 'title': 'Cara Mendukung Teman yang Sedang Berjuang Secara Mental', 'colors': ((8, 145, 178), (34, 211, 238))},
    {'slug': 'psych_12', 'emoji': '🧠', 'category': 'Mindfulness', 'title': 'Mindfulness: Latihan Sederhana untuk Hidup di Saat Ini', 'colors': ((15, 118, 110), (94, 234, 212))},
    {'slug': 'psych_13', 'emoji': '💭', 'category': 'Terapi Kognitif', 'title': 'Mengelola Pikiran Negatif dengan Pendekatan CBT', 'colors': ((79, 70, 229), (165, 180, 252))},
    {'slug': 'psych_14', 'emoji': '🫂', 'category': 'Support System', 'title': 'Pentingnya Support System dalam Menjaga Kesehatan Mental', 'colors': ((219, 39, 119), (244, 114, 182))},
    {'slug': 'psych_15', 'emoji': '🧑‍⚕️', 'category': 'Bantuan Profesional', 'title': 'Kapan Harus Mencari Bantuan Profesional?', 'colors': ((2, 132, 199), (56, 189, 248))},
]


def lerp(a, b, t):
    return tuple(int(a[i] + (b[i] - a[i]) * t) for i in range(3))


def gradient(w, h, c1, c2):
    img = Image.new('RGB', (w, h), c1)
    px = img.load()
    for x in range(w):
        t = x / (w - 1)
        col = lerp(c1, c2, t)
        for y in range(h):
            px[x, y] = col
    return img


def wrap_text(draw, text, font, max_width):
    words = text.split()
    lines, cur = [], ''
    for w in words:
        trial = (cur + ' ' + w).strip()
        if draw.textlength(trial, font=font) <= max_width:
            cur = trial
        else:
            if cur:
                lines.append(cur)
            cur = w
    if cur:
        lines.append(cur)
    return lines


def make_image(spec):
    c1, c2 = spec['colors']
    img = gradient(W, H, c1, c2).convert('RGBA')

    # Soft decorative circles for texture.
    overlay = Image.new('RGBA', (W, H), (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    od.ellipse([W - 260, -120, W + 260, 380], fill=(255, 255, 255, 28))
    od.ellipse([-180, H - 260, 260, H + 180], fill=(255, 255, 255, 22))
    overlay = overlay.filter(ImageFilter.GaussianBlur(2))
    img = Image.alpha_composite(img, overlay)

    draw = ImageDraw.Draw(img)

    # White circle badge with the emoji icon.
    badge_r = 100
    cx, cy = 150, H // 2
    draw.ellipse([cx - badge_r, cy - badge_r, cx + badge_r, cy + badge_r], fill=(255, 255, 255, 235))
    try:
        efont = ImageFont.truetype(EMOJI_FONT, EMOJI_SIZE)
        bbox = draw.textbbox((0, 0), spec['emoji'], font=efont, embedded_color=True)
        ew, eh = bbox[2] - bbox[0], bbox[3] - bbox[1]
        draw.text((cx - ew / 2 - bbox[0], cy - eh / 2 - bbox[1]), spec['emoji'], font=efont, embedded_color=True)
    except Exception as e:
        print(f"  (emoji render skipped for {spec['slug']}: {e})")

    # Category badge (pill).
    cat_font = ImageFont.truetype(FONT_BOLD, 30)
    cat_text = spec['category'].upper()
    text_x = 300
    tw = draw.textlength(cat_text, font=cat_font)
    pad_x, pad_y = 24, 12
    pill_top = 150
    draw.rounded_rectangle(
        [text_x, pill_top, text_x + tw + pad_x * 2, pill_top + 30 + pad_y * 2],
        radius=26, fill=(255, 255, 255, 235)
    )
    draw.text((text_x + pad_x, pill_top + pad_y - 2), cat_text, font=cat_font, fill=c1 if sum(c1) < sum(c2) else c2)

    # Title, word-wrapped.
    title_font = ImageFont.truetype(FONT_BOLD, 52)
    max_w = W - text_x - 60
    lines = wrap_text(draw, spec['title'], title_font, max_w)[:3]
    ty = pill_top + 30 + pad_y * 2 + 36
    for line in lines:
        draw.text((text_x, ty), line, font=title_font, fill=(255, 255, 255, 255))
        ty += 64

    # Brand footer.
    brand_font = ImageFont.truetype(FONT_REG, 26)
    draw.text((text_x, H - 60), 'SIMKM • Artikel Kesehatan Mental', font=brand_font, fill=(255, 255, 255, 200))

    out_path = os.path.join(OUT_DIR, f"{spec['slug']}.jpg")
    img.convert('RGB').save(out_path, quality=88)
    print(f'  wrote {out_path}')


def main():
    for spec in ARTICLES:
        make_image(spec)
    print(f'Done. {len(ARTICLES)} images in {OUT_DIR}')


if __name__ == '__main__':
    main()
