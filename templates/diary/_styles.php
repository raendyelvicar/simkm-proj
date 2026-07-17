<?php
// Shared, scoped styles for the diary feature. Reuses the color variables
// (--primary, --border, --text, --muted, ...) already defined in the app
// shell (templates/layouts/index.php) so the accent stays consistent
// app-wide instead of introducing a second color system just for diary.
return <<<'CSS'
.diary-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.diary-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.diary-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.diary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.diary-card-body {
    padding: 24px;
}

.btn-diary {
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

.btn-diary-primary {
    background: var(--primary);
    color: #fff;
}

.btn-diary-primary:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-diary-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}

.btn-diary-ghost:hover {
    background: #f3f4f6;
    color: var(--text);
}

.btn-diary-danger {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-diary-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.btn-diary-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.diary-table {
    width: 100%;
    border-collapse: collapse;
}

.diary-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.diary-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
}

.diary-table thead th:first-child,
.diary-table tbody td:first-child {
    padding-left: 24px;
}

.diary-table thead th:last-child,
.diary-table tbody td:last-child {
    padding-right: 24px;
}

.diary-table tbody tr:last-child td {
    border-bottom: none;
}

.diary-table tbody tr:hover {
    background: #f9fafb;
}

.diary-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.diary-snippet {
    color: var(--muted);
}

.diary-date {
    white-space: nowrap;
    font-weight: 500;
}

.mood-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.mood-pill.mood-sangat-senang { background: #ecfdf5; color: #047857; }
.mood-pill.mood-senang        { background: #f0fdf4; color: #15803d; }
.mood-pill.mood-netral        { background: #f3f4f6; color: #4b5563; }
.mood-pill.mood-sedih         { background: #eff6ff; color: #1d4ed8; }
.mood-pill.mood-sangat-buruk  { background: #fef2f2; color: #b91c1c; }

.diary-empty {
    text-align: center;
    padding: 56px 20px;
}

.diary-empty .diary-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.diary-empty p {
    margin: 4px 0 18px;
    color: var(--muted);
}

.diary-alert {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.85rem;
    margin-bottom: 18px;
}

.diary-alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.diary-form .field {
    margin-bottom: 20px;
}

.diary-form label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}

.diary-form input[type="text"],
.diary-form input[type="date"],
.diary-form textarea {
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

.diary-form input[type="text"]:focus,
.diary-form input[type="date"]:focus,
.diary-form textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.diary-form textarea {
    resize: vertical;
    min-height: 140px;
    line-height: 1.55;
}

.mood-picker {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.mood-picker input[type="radio"] {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.mood-picker label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid var(--border);
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    transition: all .15s ease;
    margin: 0;
}

.mood-picker input[type="radio"]:checked + label {
    border-color: var(--primary);
    background: rgba(37, 99, 235, 0.08);
    color: var(--primary-dark);
    font-weight: 600;
}

.diary-check {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.diary-check input {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
}

.diary-check span {
    font-size: 0.85rem;
    color: var(--text);
}

.diary-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
}

.diary-content-text {
    margin-top: 16px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 12px;
    line-height: 1.7;
    color: var(--text);
    font-size: 0.92rem;
    white-space: pre-line;
}

.diary-form .field-hint {
    margin: 0 0 8px;
    font-size: 0.8rem;
    color: var(--muted);
}

.diary-form select {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.9rem;
    font-family: inherit;
    color: var(--text);
    background: #fff;
}

.diary-form select:disabled {
    background: #f3f4f6;
    color: var(--muted);
}

.diary-checkbox-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.diary-checkbox-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid var(--border);
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    transition: all .15s ease;
    margin: 0;
}

.diary-checkbox-pill input {
    accent-color: var(--primary);
}

.diary-checkbox-pill:has(input:checked) {
    border-color: var(--primary);
    background: rgba(37, 99, 235, 0.08);
    color: var(--primary-dark);
    font-weight: 600;
}

.diary-checkbox-group-readonly .diary-checkbox-pill-active {
    border: 1px solid var(--primary);
    background: rgba(37, 99, 235, 0.08);
    color: var(--primary-dark);
    font-weight: 600;
    cursor: default;
}

.diary-lainnya-input {
    margin-top: 10px;
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 9px 12px;
    font-size: 0.85rem;
    font-family: inherit;
    color: var(--text);
    background: #fff;
}

.diary-intensity-scale {
    display: flex;
    gap: 8px;
}

.diary-intensity-option {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    border: 1px solid var(--border);
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    transition: all .15s ease;
    margin: 0;
}

.diary-intensity-option input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.diary-intensity-option:has(input:checked) {
    border-color: var(--primary);
    background: rgba(37, 99, 235, 0.08);
    color: var(--primary-dark);
}

.diary-gratitude-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.diary-gratitude-list input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.9rem;
    font-family: inherit;
    color: var(--text);
    background: #fff;
}

.diary-gratitude-view {
    margin: 8px 0 0;
    padding-left: 20px;
    color: var(--text);
    font-size: 0.92rem;
    line-height: 1.7;
}

.diary-visibility {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
}

.diary-visibility select {
    max-width: 320px;
}

.diary-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.diary-section:first-of-type {
    margin-top: 16px;
}

.diary-section h5 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 8px;
}

.diary-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.8rem;
}

.diary-badge::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.diary-badge-green  { background: #dcfce7; color: #15803d; }
.diary-badge-yellow { background: #fef9c3; color: #a16207; }
.diary-badge-orange { background: #ffedd5; color: #c2410c; }
.diary-badge-red    { background: #fee2e2; color: #b91c1c; }
.diary-badge-gray   { background: #f1f5f9; color: #475569; }
CSS;
