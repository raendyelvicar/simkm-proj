<?php ob_start(); ?>

<div class="diary-page">
    <div class="page-head">
        <div>
            <h1>Edit Diary</h1>
            <p>Perbarui catatan diary kamu.</p>
        </div>
        <a href="/diary/<?= urlencode($entry['id']) ?>" class="btn-diary btn-diary-ghost">&larr; Kembali</a>
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

            <form method="post" action="/diary/<?= urlencode($entry['id']) ?>" class="diary-form">

                <div class="field">
                    <label for="entry_date">Tanggal</label>
                    <input type="date" id="entry_date" name="entry_date"
                           value="<?= htmlspecialchars($entry['entry_date'] ?? date('Y-m-d')) ?>" required>
                </div>

                <div class="field">
                    <label for="situasi">1. Situasi</label>
                    <p class="field-hint">Apa yang terjadi hari ini yang membuatmu merasa tidak nyaman atau terganggu?</p>
                    <textarea id="situasi" name="situasi" required><?= htmlspecialchars($entry['situasi'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label for="pikiran_awal">2. Pikiran Pertama (Automatic Thought)</label>
                    <p class="field-hint">Saat kejadian itu terjadi, apa pikiran pertama yang muncul?</p>
                    <textarea id="pikiran_awal" name="pikiran_awal" required><?= htmlspecialchars($entry['pikiran_awal'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label>3. Emosi yang Dirasakan</label>
                    <div class="diary-checkbox-group">
                        <?php $curEmosi = $entry['emosi'] ?? $entry['emosi_list'] ?? []; ?>
                        <?php foreach ($emotionOptions as $option): ?>
                            <label class="diary-checkbox-pill">
                                <input type="checkbox" name="emosi[]" value="<?= htmlspecialchars($option) ?>"
                                       <?= in_array($option, $curEmosi, true) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($option) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="emosi_lainnya" class="diary-lainnya-input"
                           placeholder="Sebutkan emosi lainnya (jika memilih 'Lainnya')"
                           value="<?= htmlspecialchars($entry['emosi_lainnya'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Intensitas Emosi</label>
                    <p class="field-hint">1 = Sangat Ringan, 5 = Sangat Berat</p>
                    <div class="diary-intensity-scale">
                        <?php $curIntensity = (int) ($entry['intensitas_emosi'] ?? 0); ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="diary-intensity-option">
                                <input type="radio" name="intensitas_emosi" value="<?= $i ?>"
                                       <?= $curIntensity === $i ? 'checked' : '' ?> required>
                                <span><?= $i ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="field">
                    <label>4. Reaksi Fisik</label>
                    <div class="diary-checkbox-group">
                        <?php $curReaksi = $entry['reaksi_fisik'] ?? $entry['reaksi_fisik_list'] ?? []; ?>
                        <?php foreach ($physicalOptions as $option): ?>
                            <label class="diary-checkbox-pill">
                                <input type="checkbox" name="reaksi_fisik[]" value="<?= htmlspecialchars($option) ?>"
                                       <?= in_array($option, $curReaksi, true) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($option) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="reaksi_fisik_lainnya" class="diary-lainnya-input"
                           placeholder="Sebutkan reaksi fisik lainnya (jika memilih 'Lainnya')"
                           value="<?= htmlspecialchars($entry['reaksi_fisik_lainnya'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="perilaku">5. Perilaku</label>
                    <textarea id="perilaku" name="perilaku" required><?= htmlspecialchars($entry['perilaku'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label for="self_reflection">🖊 Self Reflection</label>
                    <textarea id="self_reflection" name="self_reflection" placeholder="(opsional)"><?= htmlspecialchars($entry['self_reflection'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label>🙏 Gratitude Journal</label>
                    <div class="diary-gratitude-list">
                        <?php $curGratitude = $entry['gratitude'] ?? $entry['gratitude_list'] ?? []; ?>
                        <?php for ($i = 0; $i < $gratitudeSlots; $i++): ?>
                            <input type="text" name="gratitude[]" placeholder="<?= $i + 1 ?>. ..."
                                   value="<?= htmlspecialchars($curGratitude[$i] ?? '') ?>">
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="rencana_besok">🎯 Rencana Besok</label>
                    <textarea id="rencana_besok" name="rencana_besok" placeholder="(opsional)"><?= htmlspecialchars($entry['rencana_besok'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label>Privasi</label>
                    <?php $curPrivate = $entry['is_private'] ?? true; ?>
                    <div class="diary-visibility">
                        <label class="diary-check">
                            <input type="radio" name="visibility" value="private" data-visibility-toggle
                                   <?= $curPrivate ? 'checked' : '' ?>>
                            <span>Private &mdash; hanya saya yang bisa lihat</span>
                        </label>
                        <label class="diary-check">
                            <input type="radio" name="visibility" value="konselor" data-visibility-toggle
                                   <?= !$curPrivate ? 'checked' : '' ?>>
                            <span>Publish to Konselor</span>
                        </label>
                        <select name="shared_konselor_id" id="shared_konselor_id" <?= $curPrivate ? 'disabled' : '' ?>>
                            <option value="">Pilih konselor...</option>
                            <?php foreach ($konselors as $k): ?>
                                <option value="<?= (int) $k['konselor_id'] ?>"
                                    <?= (int) ($entry['shared_konselor_id'] ?? 0) === (int) $k['konselor_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama'] ?: $k['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="diary-form-actions">
                    <button type="submit" class="btn-diary btn-diary-primary">Update Diary</button>
                    <a href="/diary/<?= urlencode($entry['id']) ?>" class="btn-diary btn-diary-ghost">Batal</a>
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
$pageTitle = $title ?? 'Edit Diary';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
