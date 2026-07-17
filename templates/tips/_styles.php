<?php
// Shared, scoped styles for the daily-tips feature. Mirrors the diary/article
// patterns (templates/diary/_styles.php, templates/article/_styles.php) so
// the look stays consistent app-wide.
return <<<'CSS'
.tips-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.tips-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.tips-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.tips-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.tips-table-scroll {
    overflow-x: auto;
}

.tips-table {
    width: 100%;
    border-collapse: collapse;
}

.tips-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.tips-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
}

.tips-table thead th:first-child,
.tips-table tbody td:first-child {
    padding-left: 24px;
}

.tips-table thead th:last-child,
.tips-table tbody td:last-child {
    padding-right: 24px;
}

.tips-table tbody tr:last-child td {
    border-bottom: none;
}

.tips-table tbody tr:hover {
    background: #f9fafb;
}

.tips-content-snippet {
    color: var(--muted);
    max-width: 380px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-pill-active { background: #ecfdf5; color: #047857; }
.status-pill-inactive { background: #f3f4f6; color: #6b7280; }

.btn-tips {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    line-height: 1.2;
}

.btn-tips-primary {
    background: var(--primary);
    color: #fff;
}

.btn-tips-primary:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-tips-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}

.btn-tips-ghost:hover {
    background: #f3f4f6;
    color: var(--text);
}

.btn-tips-danger {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-tips-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.btn-tips-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.tips-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.tips-empty {
    text-align: center;
    padding: 56px 20px;
}

.tips-empty .tips-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.tips-empty p {
    margin: 4px 0 18px;
    color: var(--muted);
}

.tips-detail {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    padding: 32px;
    max-width: 640px;
}

.tips-alert {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.85rem;
    margin-bottom: 18px;
}

.tips-alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.tips-form .field {
    margin-bottom: 20px;
}

.tips-form label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}

.tips-form input[type="text"],
.tips-form textarea {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.9rem;
    font-family: inherit;
    color: var(--text);
    background: #fff;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.tips-form input[type="text"]:focus,
.tips-form textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.tips-form textarea {
    resize: vertical;
    min-height: 120px;
    line-height: 1.6;
}

.tips-check {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.tips-check input {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
}

.tips-check span {
    font-size: 0.85rem;
    color: var(--text);
}

.tips-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
}
CSS;
