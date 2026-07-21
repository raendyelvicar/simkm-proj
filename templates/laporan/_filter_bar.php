<?php
/**
 * Shared filter bar for every Laporan report: date range + (optionally) student name
 * search + report-specific extra fields + Cari/Reset/Export PDF/Print buttons.
 *
 * Expects, from the including template's scope:
 * @var array $filters        ['date_from' => 'Y-m-d', 'date_to' => 'Y-m-d', 'search' => string]
 * @var string $pdfUrl        URL for the "Export PDF" button (query string appended automatically)
 * @var bool $showSearch      whether to show the "Nama Mahasiswa" field (default true)
 * @var array $extraFields    optional list of ['name','label','type'=>'text'|'select','value','options'=>[val=>label]]
 */
$showSearch = $showSearch ?? true;
$extraFields = $extraFields ?? [];
$currentQuery = $_GET;
unset($currentQuery['page']);
$pdfHref = $pdfUrl . '?' . http_build_query($currentQuery);
?>
<div class="lap-card">
    <form method="get" class="lap-filter-bar">
        <div class="lap-filter-field">
            <label for="lap-date-from">Tanggal Awal</label>
            <input type="date" id="lap-date-from" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>">
        </div>
        <div class="lap-filter-field">
            <label for="lap-date-to">Tanggal Akhir</label>
            <input type="date" id="lap-date-to" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>">
        </div>
        <?php if ($showSearch): ?>
            <div class="lap-filter-field">
                <label for="lap-search">Nama Mahasiswa</label>
                <input type="text" id="lap-search" name="q" placeholder="Cari nama/NPM" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
        <?php endif; ?>
        <?php foreach ($extraFields as $field): ?>
            <div class="lap-filter-field">
                <label for="lap-<?= htmlspecialchars($field['name']) ?>"><?= htmlspecialchars($field['label']) ?></label>
                <?php if (($field['type'] ?? 'text') === 'select'): ?>
                    <select id="lap-<?= htmlspecialchars($field['name']) ?>" name="<?= htmlspecialchars($field['name']) ?>">
                        <option value="">Semua</option>
                        <?php foreach ($field['options'] as $val => $label): ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= ($field['value'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="text" id="lap-<?= htmlspecialchars($field['name']) ?>" name="<?= htmlspecialchars($field['name']) ?>"
                        placeholder="<?= htmlspecialchars($field['label']) ?>" value="<?= htmlspecialchars($field['value'] ?? '') ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="lap-filter-actions">
            <button type="submit" class="lap-btn lap-btn-primary">🔍 Cari</button>
            <a href="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')) ?>" class="lap-btn lap-btn-ghost">↺ Reset</a>
            <a href="<?= htmlspecialchars($pdfHref) ?>" class="lap-btn lap-btn-ghost" target="_blank">📄 Export PDF</a>
            <button type="button" class="lap-btn lap-btn-ghost" onclick="window.print()">🖨️ Print</button>
        </div>
    </form>
</div>
