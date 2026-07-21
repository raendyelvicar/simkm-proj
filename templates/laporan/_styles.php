<?php
// Shared, scoped styles for all 8 Laporan report pages + the hub. One file (unlike
// most features) since the spec requires a consistent look across every report.
return <<<'CSS'
.lap-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.lap-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.lap-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.lap-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--muted);
    text-decoration: none;
    font-size: 0.85rem;
    margin-bottom: 10px;
}

.lap-back-link:hover { color: var(--primary); }

.lap-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    padding: 20px;
    margin-bottom: 20px;
}

.lap-filter-bar {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.lap-filter-field { display: flex; flex-direction: column; gap: 4px; }

.lap-filter-field label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.lap-filter-field input,
.lap-filter-field select {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 0.85rem;
    min-width: 160px;
}

.lap-filter-actions {
    display: flex;
    gap: 8px;
    margin-left: auto;
    flex-wrap: wrap;
}

.lap-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    line-height: 1.2;
}

.lap-btn-primary { background: var(--primary); color: #fff; }
.lap-btn-primary:hover { background: var(--primary-dark); color: #fff; }
.lap-btn-ghost { background: transparent; border-color: var(--border); color: var(--text); }
.lap-btn-ghost:hover { background: #f3f4f6; }

.lap-table-scroll { overflow-x: auto; }

.lap-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.lap-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 12px 10px;
    border-bottom: 1px solid var(--border);
}

.lap-table tbody td {
    padding: 12px 10px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.86rem;
    color: var(--text);
    white-space: normal;
}

.lap-table tbody tr:hover { background: #f9fafb; }

.lap-empty {
    text-align: center;
    padding: 48px 20px;
    color: var(--muted);
}

.lap-empty .icon { font-size: 2rem; margin-bottom: 8px; }

.lap-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.lap-badge-green  { background: #dcfce7; color: #15803d; }
.lap-badge-yellow { background: #fef9c3; color: #a16207; }
.lap-badge-orange { background: #ffedd5; color: #c2410c; }
.lap-badge-red    { background: #fee2e2; color: #b91c1c; }
.lap-badge-gray   { background: #f1f5f9; color: #475569; }

.lap-stat-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.lap-stat-tile {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px 16px;
    text-align: center;
    background: var(--surface);
}

.lap-stat-tile .value { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
.lap-stat-tile .label { color: var(--muted); font-size: 0.78rem; }

.lap-hub-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}

.lap-hub-card {
    display: block;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    text-decoration: none;
    color: var(--text);
    transition: box-shadow .15s ease, border-color .15s ease;
}

.lap-hub-card:hover {
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    border-color: var(--primary);
}

.lap-hub-card .icon { font-size: 1.6rem; margin-bottom: 8px; }
.lap-hub-card h3 { font-size: 1rem; font-weight: 700; margin: 0 0 6px; color: var(--text); }
.lap-hub-card p { margin: 0; font-size: 0.82rem; color: var(--muted); }

.lap-diary-entry {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 14px;
}

.lap-diary-entry h4 { font-size: 0.95rem; font-weight: 700; margin: 0 0 10px; }
.lap-diary-entry dl { display: grid; grid-template-columns: 160px 1fr; gap: 6px 12px; margin: 0; }
.lap-diary-entry dt { font-weight: 600; color: var(--muted); font-size: 0.8rem; }
.lap-diary-entry dd { margin: 0; font-size: 0.86rem; }

canvas.lap-chart { max-height: 320px; }

@media print {
    .topbar, .sidebar, .main-footer, .lap-filter-bar, .lap-back-link, .toast-container { display: none !important; }
    .app-shell, .content-wrapper, .main-content { display: block !important; padding: 0 !important; margin: 0 !important; }
    .lap-card { box-shadow: none !important; border: none !important; }
}
CSS;
