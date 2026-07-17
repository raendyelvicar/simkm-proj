<?php ob_start(); ?>

<div class="diary-page">
    <div class="page-head">
        <div>
            <h1>Diary Harian</h1>
            <p>Luangkan waktu sekitar 3&ndash;5 menit untuk merefleksikan hari ini. Semua catatan bersifat pribadi.</p>
        </div>
        <a href="/diary" class="btn-diary btn-diary-ghost">&larr; Kembali</a>
    </div>

    <div class="diary-card">
        <div class="diary-card-body">

            <?php if (!empty($errors)): ?>
                <div class="diary-alert diary-alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/diary" class="diary-form">

                <div class="field">
                    <label for="entry_date">Tanggal</label>
                    <input type="date" id="entry_date" name="entry_date"
                           value="<?= htmlspecialchars($old['entry_date'] ?? date('Y-m-d')) ?>" required>
                </div>

                <div class="field">
                    <label for="situasi">1. Situasi</label>
                    <p class="field-hint">Apa yang terjadi hari ini yang membuatmu merasa tidak nyaman atau terganggu? Contoh: "Presentasi skripsi ditunda sehingga saya merasa kecewa."</p>
                    <textarea id="situasi" name="situasi" placeholder="Jawaban kamu..." required><?= htmlspecialchars($old['situasi'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label for="pikiran_awal">2. Pikiran Pertama (Automatic Thought)</label>
                    <p class="field-hint">Saat kejadian itu terjadi, apa pikiran pertama yang muncul? Contoh: "Saya pasti gagal."</p>
                    <textarea id="pikiran_awal" name="pikiran_awal" placeholder="Jawaban kamu..." required><?= htmlspecialchars($old['pikiran_awal'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label>3. Emosi yang Dirasakan</label>
                    <p class="field-hint">Apa yang paling menggambarkan perasaanmu saat itu?</p>
                    <div class="diary-checkbox-group">
                        <?php $oldEmosi = $old['emosi'] ?? []; ?>
                        <?php foreach ($emotionOptions as $option): ?>
                            <label class="diary-checkbox-pill">
                                <input type="checkbox" name="emosi[]" value="<?= htmlspecialchars($option) ?>"
                                       <?= in_array($option, $oldEmosi, true) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($option) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="emosi_lainnya" class="diary-lainnya-input"
                           placeholder="Sebutkan emosi lainnya (jika memilih 'Lainnya')"
                           value="<?= htmlspecialchars($old['emosi_lainnya'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Intensitas Emosi</label>
                    <p class="field-hint">1 = Sangat Ringan, 5 = Sangat Berat</p>
                    <div class="diary-intensity-scale">
                        <?php $oldIntensity = (int) ($old['intensitas_emosi'] ?? 0); ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="diary-intensity-option">
                                <input type="radio" name="intensitas_emosi" value="<?= $i ?>"
                                       <?= $oldIntensity === $i ? 'checked' : '' ?> required>
                                <span><?= $i ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="field">
                    <label>4. Reaksi Fisik</label>
                    <p class="field-hint">Apa yang tubuhmu rasakan saat itu?</p>
                    <div class="diary-checkbox-group">
                        <?php $oldReaksi = $old['reaksi_fisik'] ?? []; ?>
                        <?php foreach ($physicalOptions as $option): ?>
                            <label class="diary-checkbox-pill">
                                <input type="checkbox" name="reaksi_fisik[]" value="<?= htmlspecialchars($option) ?>"
                                       <?= in_array($option, $oldReaksi, true) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($option) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="reaksi_fisik_lainnya" class="diary-lainnya-input"
                           placeholder="Sebutkan reaksi fisik lainnya (jika memilih 'Lainnya')"
                           value="<?= htmlspecialchars($old['reaksi_fisik_lainnya'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="perilaku">5. Perilaku</label>
                    <p class="field-hint">Apa yang kamu lakukan setelah kejadian tersebut? Contoh: menghindari orang lain, tidur, menangis, tetap bekerja, berbicara dengan teman.</p>
                    <textarea id="perilaku" name="perilaku" placeholder="Jawaban kamu..." required><?= htmlspecialchars($old['perilaku'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label for="self_reflection">🖊 Self Reflection</label>
                    <p class="field-hint">Kalau melihat kejadian tadi dengan lebih tenang, apakah ada cara lain yang mungkin lebih membantu untuk menghadapinya?</p>
                    <textarea id="self_reflection" name="self_reflection" placeholder="Jawaban kamu... (opsional)"><?= htmlspecialchars($old['self_reflection'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label>🙏 Gratitude Journal</label>
                    <p class="field-hint">Sebutkan 3 hal yang kamu syukuri hari ini.</p>
                    <div class="diary-gratitude-list">
                        <?php $oldGratitude = $old['gratitude'] ?? []; ?>
                        <?php for ($i = 0; $i < $gratitudeSlots; $i++): ?>
                            <input type="text" name="gratitude[]" placeholder="<?= $i + 1 ?>. ..."
                                   value="<?= htmlspecialchars($oldGratitude[$i] ?? '') ?>">
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="rencana_besok">🎯 Rencana Besok</label>
                    <p class="field-hint">Satu hal kecil apa yang ingin kamu lakukan besok agar merasa lebih baik? Contoh: jalan pagi 15 menit, tidur sebelum jam 11, menghubungi teman.</p>
                    <textarea id="rencana_besok" name="rencana_besok" placeholder="Jawaban kamu... (opsional)"><?= htmlspecialchars($old['rencana_besok'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label>Privasi</label>
                    <?php $oldPrivate = $old['is_private'] ?? true; ?>
                    <div class="diary-visibility">
                        <label class="diary-check">
                            <input type="radio" name="visibility" value="private" data-visibility-toggle
                                   <?= $oldPrivate ? 'checked' : '' ?>>
                            <span>Private &mdash; hanya saya yang bisa lihat</span>
                        </label>
                        <label class="diary-check">
                            <input type="radio" name="visibility" value="konselor" data-visibility-toggle
                                   <?= !$oldPrivate ? 'checked' : '' ?>>
                            <span>Publish to Konselor</span>
                        </label>
                        <select name="shared_konselor_id" id="shared_konselor_id" <?= $oldPrivate ? 'disabled' : '' ?>>
                            <option value="">Pilih konselor...</option>
                            <?php foreach ($konselors as $k): ?>
                                <option value="<?= (int) $k['konselor_id'] ?>"
                                    <?= (int) ($old['shared_konselor_id'] ?? 0) === (int) $k['konselor_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama'] ?: $k['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="diary-form-actions">
                    <button type="submit" class="btn-diary btn-diary-primary">Simpan Diary</button>
                    <a href="/diary" class="btn-diary btn-diary-ghost">Batal</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var radios = document.querySelectorAll('[data-visibility-toggle]');
    var select = document.getElementById('shared_konselor_id');
    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            select.disabled = document.querySelector('[data-visibility-toggle]:checked').value !== 'konselor';
        });
    });
})();
</script>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Tulis Diary';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
